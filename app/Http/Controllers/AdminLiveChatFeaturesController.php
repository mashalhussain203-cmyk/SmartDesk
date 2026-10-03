<?php

namespace App\Http\Controllers;

use App\Services\LiveChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class AdminLiveChatFeaturesController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        abort_unless((bool) $request->user()?->is_admin, 403);
    }

    public function stats(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $base = DB::table('live_chat_conversations');
        $today = now()->startOfDay();

        $responseRows = DB::table('live_chat_conversations')
            ->whereNotNull('first_response_at')
            ->get(['created_at', 'first_response_at']);
        $avgResponse = $responseRows->isEmpty()
            ? 0
            : (int) round($responseRows->avg(function ($row): int {
                return max(0, strtotime((string) $row->first_response_at) - strtotime((string) $row->created_at));
            }));

        return response()->json([
            'active' => (clone $base)->where('status', '!=', 'closed')->count(),
            'waiting' => (clone $base)->where('status', 'waiting')->count(),
            'closed_today' => (clone $base)->where('status', 'closed')->where('closed_at', '>=', $today)->count(),
            'messages_today' => DB::table('live_chat_messages')->where('created_at', '>=', $today)->count(),
            'unread' => (int) DB::table('live_chat_conversations as c')
                ->selectSub(function ($query): void {
                    $query->from('live_chat_messages as m')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('m.conversation_id', 'c.id')
                        ->where('m.sender', 'visitor')
                        ->whereColumn('m.id', '>', 'c.admin_last_read_id');
                }, 'unread_count')
                ->get()
                ->sum('unread_count'),
            'avg_first_response_seconds' => $avgResponse,
        ]);
    }

    public function updateMeta(Request $request, int $conversation): JsonResponse
    {
        $this->authorizeAdmin($request);
        abort_unless(DB::table('live_chat_conversations')->where('id', $conversation)->exists(), 404);

        $data = $request->validate([
            'priority' => ['sometimes', 'in:low,normal,high,urgent'],
            'labels' => ['sometimes', 'array', 'max:8'],
            'labels.*' => ['string', 'max:32'],
            'assigned_admin_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ]);

        $update = ['updated_at' => now()];
        if (array_key_exists('priority', $data)) {
            $update['priority'] = $data['priority'];
        }
        if (array_key_exists('labels', $data)) {
            $update['labels'] = json_encode(array_values(array_unique(array_map('trim', $data['labels']))));
        }
        if (array_key_exists('assigned_admin_id', $data)) {
            $update['assigned_admin_id'] = $data['assigned_admin_id'];
        }

        DB::table('live_chat_conversations')->where('id', $conversation)->update($update);
        $this->event($conversation, (int) $request->user()->id, 'metadata_updated', $data);

        return response()->json(['ok' => true]);
    }

    public function notes(Request $request, int $conversation): JsonResponse
    {
        $this->authorizeAdmin($request);
        abort_unless(DB::table('live_chat_conversations')->where('id', $conversation)->exists(), 404);

        $notes = DB::table('live_chat_internal_notes as n')
            ->leftJoin('users as u', 'u.id', '=', 'n.admin_id')
            ->where('n.conversation_id', $conversation)
            ->orderByDesc('n.id')
            ->get(['n.id', 'n.body', 'n.created_at', 'n.updated_at', 'u.name as admin_name']);

        return response()->json(['items' => $notes]);
    }

    public function storeNote(Request $request, int $conversation): JsonResponse
    {
        $this->authorizeAdmin($request);
        abort_unless(DB::table('live_chat_conversations')->where('id', $conversation)->exists(), 404);
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $id = DB::table('live_chat_internal_notes')->insertGetId([
            'conversation_id' => $conversation,
            'admin_id' => $request->user()->id,
            'body' => trim($data['body']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->event($conversation, (int) $request->user()->id, 'internal_note_added', ['note_id' => $id]);

        return response()->json(['ok' => true, 'id' => $id]);
    }

    public function deleteNote(Request $request, int $conversation, int $note): JsonResponse
    {
        $this->authorizeAdmin($request);
        $deleted = DB::table('live_chat_internal_notes')->where('id', $note)->where('conversation_id', $conversation)->delete();
        abort_unless($deleted, 404);
        return response()->json(['ok' => true]);
    }

    public function react(Request $request, int $conversation, int $message): JsonResponse
    {
        $this->authorizeAdmin($request);
        abort_unless(DB::table('live_chat_messages')->where('id', $message)->where('conversation_id', $conversation)->exists(), 404);
        $data = $request->validate(['emoji' => ['required', 'string', 'max:16', 'in:👍,❤️,😂,😮,😢,🙏']]);
        $actorKey = 'admin:'.(int) $request->user()->id;
        $existing = DB::table('live_chat_message_reactions')
            ->where('message_id', $message)->where('actor_key', $actorKey)->where('emoji', $data['emoji'])->first();
        if ($existing) {
            DB::table('live_chat_message_reactions')->where('id', $existing->id)->delete();
            $active = false;
        } else {
            DB::table('live_chat_message_reactions')->insert([
                'message_id' => $message,
                'actor_key' => $actorKey,
                'emoji' => $data['emoji'],
                'created_at' => now(),
            ]);
            $active = true;
        }
        return response()->json(['ok' => true, 'active' => $active]);
    }

    public function editMessage(Request $request, int $conversation, int $message): JsonResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['body' => ['required', 'string', 'max:4000']]);
        $record = DB::table('live_chat_messages')->where('id', $message)->where('conversation_id', $conversation)->where('sender', 'admin')->first();
        abort_unless($record, 404);
        abort_if(now()->diffInMinutes($record->created_at) > (int) config('live_chat.edit_window_minutes', 5), 409, 'Dit bericht kan niet meer worden bewerkt.');
        DB::table('live_chat_messages')->where('id', $message)->update(['body' => trim($data['body']), 'edited_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function block(Request $request, int $conversation): JsonResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'hours' => ['nullable', 'integer', 'min:1', 'max:8760'],
        ]);
        $record = DB::table('live_chat_conversations')->where('id', $conversation)->first();
        abort_unless($record, 404);
        DB::table('live_chat_blocks')->insert([
            'owner_key' => $record->owner_key,
            'ip_hash' => $record->visitor_ip_hash,
            'reason' => $data['reason'] ?? null,
            'created_by' => $request->user()->id,
            'expires_at' => ! empty($data['hours']) ? now()->addHours((int) $data['hours']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('live_chat_conversations')->where('id', $conversation)->update([
            'blocked_at' => now(),
            'blocked_reason' => $data['reason'] ?? null,
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by_user_id' => $request->user()->id,
            'updated_at' => now(),
        ]);
        $this->event($conversation, (int) $request->user()->id, 'visitor_blocked', $data);
        return response()->json(['ok' => true]);
    }

    public function unblock(Request $request, int $conversation): JsonResponse
    {
        $this->authorizeAdmin($request);
        $record = DB::table('live_chat_conversations')->where('id', $conversation)->first();
        abort_unless($record, 404);
        DB::table('live_chat_blocks')->where(function ($query) use ($record): void {
            $query->where('owner_key', $record->owner_key);
            if ($record->visitor_ip_hash) {
                $query->orWhere('ip_hash', $record->visitor_ip_hash);
            }
        })->delete();
        DB::table('live_chat_conversations')->where('id', $conversation)->update([
            'blocked_at' => null,
            'blocked_reason' => null,
            'updated_at' => now(),
        ]);
        $this->event($conversation, (int) $request->user()->id, 'visitor_unblocked');
        return response()->json(['ok' => true]);
    }

    public function history(Request $request, int $conversation): JsonResponse
    {
        $this->authorizeAdmin($request);
        $items = DB::table('live_chat_conversation_events as e')
            ->leftJoin('users as u', 'u.id', '=', 'e.actor_user_id')
            ->where('e.conversation_id', $conversation)
            ->orderByDesc('e.id')
            ->limit(200)
            ->get(['e.id', 'e.event_type', 'e.metadata', 'e.created_at', 'u.name as actor_name']);
        return response()->json(['items' => $items]);
    }

    public function media(Request $request, int $conversation): JsonResponse
    {
        $this->authorizeAdmin($request);
        $items = DB::table('live_chat_messages')
            ->where('conversation_id', $conversation)
            ->whereNotNull('attachment_path')
            ->orderByDesc('id')
            ->get(['id', 'sender', 'type', 'attachment_name', 'attachment_mime', 'attachment_size', 'created_at'])
            ->map(function ($row) use ($conversation) {
                $row->url = route('admin.live-chat.attachment', ['conversation' => $conversation, 'message' => $row->id]);
                $row->download_url = route('admin.live-chat.attachment', ['conversation' => $conversation, 'message' => $row->id, 'download' => 1]);
                return $row;
            });
        return response()->json(['items' => $items]);
    }

    public function profile(Request $request, int $conversation): JsonResponse
    {
        $this->authorizeAdmin($request);
        $record = DB::table('live_chat_conversations as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.user_id')
            ->where('c.id', $conversation)
            ->first(['c.*', 'u.name', 'u.email']);
        abort_unless($record, 404);
        return response()->json([
            'conversation' => $record,
            'message_count' => DB::table('live_chat_messages')->where('conversation_id', $conversation)->count(),
            'attachment_count' => DB::table('live_chat_messages')->where('conversation_id', $conversation)->whereNotNull('attachment_path')->count(),
            'first_message_at' => DB::table('live_chat_messages')->where('conversation_id', $conversation)->min('created_at'),
            'last_message_at' => DB::table('live_chat_messages')->where('conversation_id', $conversation)->max('created_at'),
        ]);
    }

    public function transcript(Request $request, int $conversation): Response
    {
        $this->authorizeAdmin($request);
        $record = DB::table('live_chat_conversations as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.user_id')
            ->where('c.id', $conversation)
            ->first(['c.*', 'u.name', 'u.email']);
        abort_unless($record, 404);
        $messages = DB::table('live_chat_messages')->where('conversation_id', $conversation)->orderBy('id')->get();

        $html = view('admin.live-chat-transcript', [
            'conversation' => $record,
            'messages' => $messages,
        ])->render();

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function exportZip(Request $request, int $conversation): Response
    {
        $this->authorizeAdmin($request);
        abort_unless(class_exists(ZipArchive::class), 501, 'PHP ZIP-extensie ontbreekt op deze server.');
        $record = DB::table('live_chat_conversations')->where('id', $conversation)->first();
        abort_unless($record, 404);
        $messages = DB::table('live_chat_messages')->where('conversation_id', $conversation)->orderBy('id')->get();

        $tmp = tempnam(sys_get_temp_dir(), 'chat-export-');
        $zip = new ZipArchive();
        abort_unless($tmp && $zip->open($tmp, ZipArchive::OVERWRITE) === true, 500, 'Export kon niet worden gemaakt.');

        $lines = ["SmartDesk chat transcript #{$conversation}", ''];
        foreach ($messages as $message) {
            $lines[] = '['.$message->created_at.'] '.strtoupper($message->sender).': '.($message->body ?: '[bijlage]');
            if ($message->attachment_name) {
                $lines[] = '  Bijlage: '.$message->attachment_name;
            }
            if ($message->attachment_path && Storage::disk('public')->exists($message->attachment_path)) {
                $safeName = $message->id.'-'.Str::slug(pathinfo($message->attachment_name ?: 'bestand', PATHINFO_FILENAME)).'.'.pathinfo($message->attachment_name ?: $message->attachment_path, PATHINFO_EXTENSION);
                $zip->addFile(Storage::disk('public')->path($message->attachment_path), 'attachments/'.$safeName);
            }
        }
        $zip->addFromString('transcript.txt', implode("\r\n", $lines));
        $zip->close();
        $contents = file_get_contents($tmp);
        @unlink($tmp);

        return response($contents ?: '', 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="chat-'.$conversation.'-export.zip"',
        ]);
    }

    private function event(int $conversation, ?int $actor, string $type, array $metadata = []): void
    {
        DB::table('live_chat_conversation_events')->insert([
            'conversation_id' => $conversation,
            'actor_user_id' => $actor,
            'event_type' => $type,
            'metadata' => $metadata === [] ? null : json_encode($metadata),
            'created_at' => now(),
        ]);
    }
}
