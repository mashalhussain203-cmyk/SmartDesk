<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiDocument;
use App\Models\User;
use App\Services\AiWorkspaceService;
use App\Services\ChatFileReaderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiWorkspaceController extends Controller
{
    public function __construct(
        private readonly AiWorkspaceService $workspace
    ) {
    }

    public function bootstrap(Request $request): JsonResponse
    {
        return response()->json([
            'ok' => true,
            ...$this->workspace->bootstrap($this->user($request)),
        ]);
    }

    public function syncConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'uuid'],
            'project_id' => ['nullable', 'uuid'],
            'title' => ['nullable', 'string', 'max:180'],
            'mode' => [
                'nullable',
                'string',
                Rule::in(['auto', 'web', 'research', 'code', 'plain']),
            ],
            'pinned' => ['nullable', 'boolean'],
            'archived' => ['nullable', 'boolean'],
            'messages' => ['nullable', 'array', 'max:200'],
            'messages.*.role' => [
                'required_with:messages',
                Rule::in(['user', 'assistant']),
            ],
            'messages.*.content' => [
                'required_with:messages',
                'string',
                'max:50000',
            ],
        ]);

        $conversation = $this->workspace->syncConversation(
            $this->user($request),
            $validated
        );

        return response()->json([
            'ok' => true,
            'conversation' => [
                'id' => $conversation->id,
                'project_id' => $conversation->project_id,
                'title' => $conversation->title,
                'mode' => $conversation->mode,
                'pinned' => (bool) $conversation->pinned,
                'archived' => (bool) $conversation->archived,
                'updated_at' => optional($conversation->updated_at)?->toIso8601String(),
            ],
        ]);
    }

    public function deleteConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'uuid'],
        ]);

        $this->workspace->deleteConversation(
            $this->user($request),
            (string) $validated['id']
        );

        return response()->json(['ok' => true]);
    }

    public function saveProject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['nullable', 'uuid'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:3000'],
            'instructions' => ['nullable', 'string', 'max:6000'],
            'color' => ['nullable', 'string', 'max:32'],
        ]);

        $project = $this->workspace->createOrUpdateProject(
            $this->user($request),
            $validated
        );

        return response()->json([
            'ok' => true,
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'instructions' => $project->instructions,
                'color' => $project->color,
                'updated_at' => optional($project->updated_at)?->toIso8601String(),
            ],
        ]);
    }

    public function deleteProject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'uuid'],
        ]);

        $this->workspace->deleteProject(
            $this->user($request),
            (string) $validated['id']
        );

        return response()->json(['ok' => true]);
    }

    public function listDocuments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'uuid'],
        ]);

        return response()->json([
            'ok' => true,
            'documents' => $this->workspace->projectDocuments(
                $this->user($request),
                (string) $validated['project_id']
            ),
        ]);
    }

    public function uploadDocument(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'uuid'],
            'file' => [
                'required',
                'file',
                'max:' . (int) (ChatFileReaderService::MAX_FILE_BYTES / 1024),
            ],
        ]);

        $file = $request->file('file');

        if (! $file) {
            throw ValidationException::withMessages([
                'file' => 'Het bestand kon niet worden gelezen.',
            ]);
        }

        $document = $this->workspace->storeProjectDocument(
            $this->user($request),
            (string) $validated['project_id'],
            $file
        );

        return response()->json([
            'ok' => true,
            'document' => [
                'id' => $document->id,
                'name' => $document->original_name,
                'extension' => $document->extension,
                'analysis' => $document->analysis,
                'project_id' => $document->project_id,
                'size_bytes' => (int) $document->size_bytes,
            ],
        ]);
    }

    public function deleteDocument(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'uuid'],
        ]);

        $this->workspace->deleteDocument(
            $this->user($request),
            (string) $validated['id']
        );

        return response()->json(['ok' => true]);
    }

    public function downloadDocument(
        Request $request,
        string $document
    ): StreamedResponse {
        $item = AiDocument::query()
            ->where('user_id', $this->user($request)->id)
            ->whereKey($document)
            ->firstOrFail();

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(
            (string) $item->disk
        );

        abort_unless(
            $disk->exists(
                (string) $item->path
            ),
            404
        );

        return $disk->download(
            (string) $item->path,
            (string) $item->original_name
        );
    }

    public function saveMemory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['nullable', 'uuid'],
            'label' => ['nullable', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:3000'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        $memory = $this->workspace->saveMemory(
            $this->user($request),
            $validated
        );

        return response()->json([
            'ok' => true,
            'memory' => [
                'id' => $memory->id,
                'label' => $memory->label,
                'content' => $memory->content,
                'enabled' => (bool) $memory->enabled,
            ],
        ]);
    }

    public function deleteMemory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'uuid'],
        ]);

        $this->workspace->deleteMemory(
            $this->user($request),
            (string) $validated['id']
        );

        return response()->json(['ok' => true]);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:200'],
        ]);

        return response()->json([
            'ok' => true,
            ...$this->workspace->search(
                $this->user($request),
                (string) $validated['q']
            ),
        ]);
    }

    public function share(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => ['required', 'uuid'],
        ]);

        $share = $this->workspace->createShare(
            $this->user($request),
            (string) $validated['conversation_id']
        );

        return response()->json([
            'ok' => true,
            'url' => route('ai.workspace.shared', ['token' => $share->token]),
            'expires_at' => optional($share->expires_at)?->toIso8601String(),
        ]);
    }

    public function export(Request $request, string $conversation): Response
    {
        $markdown = $this->workspace->exportMarkdown(
            $this->user($request),
            $conversation
        );

        $chat = AiConversation::query()
            ->where('user_id', $this->user($request)->id)
            ->whereKey($conversation)
            ->firstOrFail();

        $filename = preg_replace(
            '/[^A-Za-z0-9._-]+/',
            '-',
            $chat->title
        ) ?: 'mashal-ai-chat';

        return response(
            $markdown,
            200,
            [
                'Content-Type' => 'text/markdown; charset=UTF-8',
                'Content-Disposition' =>
                    'attachment; filename="' . trim($filename, '-') . '.md"',
            ]
        );
    }

    public function shared(string $token): View
    {
        $share = $this->workspace->sharedConversation($token);

        abort_if(! $share, 404);

        return view('ai.shared', [
            'conversation' => $share->conversation,
            'expiresAt' => $share->expires_at,
        ]);
    }

    private function user(Request $request): User
    {
        $user = $request->user();

        abort_if(! $user instanceof User, 401);

        return $user;
    }
}
