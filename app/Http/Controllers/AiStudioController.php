<?php

namespace App\Http\Controllers;

use App\Models\AiAgentRun;
use App\Models\AiConversation;
use App\Models\AiDocument;
use App\Models\AiMemory;
use App\Models\AiProject;
use App\Models\User;
use App\Services\AiWorkspaceService;
use App\Services\AzureSpeechService;
use App\Services\GroqChatService;
use App\Services\GroqVisionService;
use App\Services\GroqVoiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiStudioController extends Controller
{
    public function __construct(
        private readonly AiWorkspaceService $workspace,
        private readonly GroqChatService $chat,
        private readonly GroqVoiceService $voice,
        private readonly GroqVisionService $vision,
        private readonly AzureSpeechService $speech
    ) {
    }

    public function index(Request $request): View
    {
        $user = $this->user($request);

        $query = trim(
            (string) $request->query('q', '')
        );

        $searchResults = [
            'conversations' => [],
            'documents' => [],
        ];

        if (mb_strlen($query) >= 2) {
            $searchResults =
                $this->workspace->search(
                    $user,
                    $query
                );
        }

        $recentConversations =
            AiConversation::query()
                ->where('user_id', $user->id)
                ->where('archived', false)
                ->orderByDesc('pinned')
                ->orderByDesc('last_message_at')
                ->orderByDesc('updated_at')
                ->limit(8)
                ->get();

        $projects =
            AiProject::query()
                ->where('user_id', $user->id)
                ->withCount([
                    'conversations',
                    'documents',
                ])
                ->latest('updated_at')
                ->limit(8)
                ->get();

        $recentDocuments =
            AiDocument::query()
                ->where('user_id', $user->id)
                ->latest('updated_at')
                ->limit(6)
                ->get();

        $periodStart =
            now()->subDays(30);

        $agentRuns =
            AiAgentRun::query()
                ->where('user_id', $user->id)
                ->where('created_at', '>=', $periodStart);

        $runCount =
            (clone $agentRuns)->count();

        $webRuns =
            (clone $agentRuns)
                ->where('used_web', true)
                ->count();

        $codeRuns =
            (clone $agentRuns)
                ->where('used_code', true)
                ->count();

        $averageDuration =
            (int) round(
                (float) (
                    (clone $agentRuns)
                        ->whereNotNull('duration_ms')
                        ->avg('duration_ms')
                    ?? 0
                )
            );

        return view(
            'ai.dashboard',
            [
                'templates' =>
                    array_values(
                        (array) config(
                            'ai-templates.templates',
                            []
                        )
                    ),

                'recentConversations' =>
                    $recentConversations,

                'projects' =>
                    $projects,

                'recentDocuments' =>
                    $recentDocuments,

                'searchQuery' =>
                    $query,

                'searchResults' =>
                    $searchResults,

                'stats' => [
                    'conversations' =>
                        AiConversation::query()
                            ->where('user_id', $user->id)
                            ->count(),

                    'projects' =>
                        AiProject::query()
                            ->where('user_id', $user->id)
                            ->count(),

                    'documents' =>
                        AiDocument::query()
                            ->where('user_id', $user->id)
                            ->count(),

                    'memories' =>
                        AiMemory::query()
                            ->where('user_id', $user->id)
                            ->where('enabled', true)
                            ->count(),

                    'runs_30d' =>
                        $runCount,

                    'web_runs_30d' =>
                        $webRuns,

                    'code_runs_30d' =>
                        $codeRuns,

                    'avg_duration_ms_30d' =>
                        $averageDuration,
                ],

                'providerStatus' =>
                    $this->providerStatus(),
            ]
        );
    }

    public function status(Request $request): JsonResponse
    {
        $this->user($request);

        return response()->json([
            'ok' => true,
            'providers' =>
                $this->providerStatus(),
            'time' =>
                now()->toIso8601String(),
        ]);
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function providerStatus(): array
    {
        return [
            'chat' => [
                'label' => 'AI Chat',
                'configured' =>
                    $this->chat->isConfigured(),
                'model' =>
                    $this->chat->modelName(),
            ],

            'voice_input' => [
                'label' => 'Voice input',
                'configured' =>
                    $this->voice->isConfigured(),
                'model' =>
                    $this->voice->modelName(),
            ],

            'vision' => [
                'label' => 'Vision',
                'configured' =>
                    $this->vision->isConfigured(),
                'model' =>
                    $this->vision->modelName(),
            ],

            'speech' => [
                'label' => 'Voice output',
                'configured' =>
                    $this->speech->isConfigured(),
                'model' => 'Azure Speech',
            ],

            'workspace' => [
                'label' => 'Workspace',
                'configured' =>
                    $this->workspace->enabled(),
                'model' => 'Laravel database',
            ],
        ];
    }

    private function user(Request $request): User
    {
        $user = $request->user();

        abort_if(
            ! $user instanceof User,
            401
        );

        return $user;
    }
}
