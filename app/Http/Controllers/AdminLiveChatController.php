<?php

namespace App\Http\Controllers;

use App\Services\LiveChatService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use stdClass;

class AdminLiveChatController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        abort_unless((bool) $request->user()?->is_admin, 403);
    }

    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.live-chat');
    }

    public function conversations(Request $request, LiveChatService $chat): JsonResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['page' => ['sometimes', 'integer', 'min:1'], 'filter' => ['sometimes', 'in:active,closed']]);
        $query = DB::table('live_chat_conversations as c')->leftJoin('users as u', 'u.id', '=', 'c.user_id')
            ->select('c.id', 'c.user_id', 'c.status', 'c.last_message_at', 'u.name', 'u.email')
            ->selectSub(function ($query): void {
                $query->from('live_chat_messages as m')->selectRaw('COUNT(*)')->whereColumn('m.conversation_id', 'c.id')
                    ->where('m.sender', 'visitor')->whereColumn('m.id', '>', 'c.admin_last_read_id');
            }, 'unread');

        if (($data['filter'] ?? 'active') === 'closed') {
            $query->where('c.status', 'closed');
        } else {
            $query->where('c.status', '!=', 'closed');
        }

        $page = $query->orderByDesc('c.last_message_at')->orderByDesc('c.id')->paginate(30);
        $items = $page->getCollection()->map(function (stdClass $conversation) use ($chat): array {
            $identity = $chat->userIdentity($conversation->user_id ? (int) $conversation->user_id : null);

            return [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'name' => $conversation->user_id ? $conversation->name : 'Gast #'.$conversation->id,
                'email' => $conversation->user_id ? $conversation->email : null,
                'avatar' => $identity['avatar'],
                'kind' => $conversation->user_id ? 'account' : 'guest',
                'unread' => (int) $conversation->unread,
                'last_message_at' => $conversation->last_message_at,
            ];
        })->all();

        return response()->json(['items' => $items, 'page' => $page->currentPage(), 'last_page' => $page->lastPage()])
            ->header('Cache-Control', 'no-store');
    }

    public function show(Request $request, LiveChatService $chat, int $conversation): JsonResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['after' => ['sometimes', 'integer', 'min:0']]);
        $record = DB::table('live_chat_conversations')->where('id', $conversation)->first();
        abort_unless($record, 404);
        $payload = $chat->payload($record, (int) ($data['after'] ?? 0));
        $lastId = collect($payload['messages'])->max('id');
        if ($lastId) {
            DB::table('live_chat_conversations')->where('id', $conversation)->where('admin_last_read_id', '<', $lastId)
                ->update(['admin_last_read_id' => $lastId]);
        }

        return response()->json($payload)->header('Cache-Control', 'no-store');
    }

    public function store(Request $request, LiveChatService $chat, int $conversation): JsonResponse
    {
        $this->authorizeAdmin($request);
        $type = (string) $request->input('type', 'text');
        $request->merge(['type' => $type]);

        $rules = [
            'type' => ['required', Rule::in(['text', 'file', 'voice'])],
            'client_id' => ['required', 'uuid'],
            'body' => [$type === 'text' ? 'required' : 'nullable', 'string', 'max:4000'],
        ];
        if ($type === 'file') {
            $rules['attachment'] = ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,gif,pdf,txt,doc,docx,xls,xlsx'];
        } elseif ($type === 'voice') {
            $rules['attachment'] = ['required', 'file', 'max:15360', 'mimetypes:audio/webm,audio/ogg,audio/mpeg,audio/mp4,audio/wav,video/webm,application/octet-stream'];
        }

        $data = $request->validate($rules);
        $chat->sendAdmin($conversation, (int) $request->user()->id, $data, $request->file('attachment'));

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, LiveChatService $chat, int $conversation, int $message): JsonResponse
    {
        $this->authorizeAdmin($request);
        $chat->deleteAdminMessage($conversation, $message);

        return response()->json(['ok' => true]);
    }

    public function update(Request $request, int $conversation): JsonResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['status' => ['required', 'in:open,closed']]);
        DB::transaction(function () use ($conversation, $data): void {
            $record = DB::table('live_chat_conversations')->where('id', $conversation)->lockForUpdate()->first();
            abort_unless($record, 404);
            DB::table('live_chat_conversations')->where('id', $conversation)->update([
                'status' => $data['status'], 'updated_at' => now(),
            ]);
        });

        return response()->json(['ok' => true]);
    }

    public function presence(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['online' => ['required', 'boolean']]);
        if ($data['online']) {
            DB::table('live_chat_agents')->updateOrInsert(['user_id' => $request->user()->id], ['last_seen_at' => now()]);
        } else {
            DB::table('live_chat_agents')->where('user_id', $request->user()->id)->delete();
        }

        return response()->json(['ok' => true]);
    }
}
