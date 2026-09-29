<?php

namespace App\Http\Controllers;

use App\Services\LiveChatService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function conversations(Request $request): JsonResponse
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
        $items = $page->getCollection()->map(fn (stdClass $conversation): array => [
            'id' => $conversation->id, 'status' => $conversation->status,
            'name' => $conversation->user_id ? $conversation->name : 'Gast #'.$conversation->id,
            'email' => $conversation->user_id ? $conversation->email : null,
            'kind' => $conversation->user_id ? 'account' : 'guest',
            'unread' => (int) $conversation->unread, 'last_message_at' => $conversation->last_message_at,
        ])->all();

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
        $data = $request->validate(['body' => ['required', 'string', 'max:4000'], 'client_id' => ['required', 'uuid']]);
        $chat->sendAdmin($conversation, $data);

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
