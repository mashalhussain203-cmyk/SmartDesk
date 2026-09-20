<?php

namespace App\Http\Controllers;

use App\Services\ModalChatService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class AiChatController extends Controller
{
    public function __construct(
        private readonly ModalChatService $modalChat
    ) {
    }

    public function index(): View
    {
        return view('ai.chat', [
            'chatConfigured' => $this->modalChat->isConfigured(),
            'modelName' => $this->modalChat->modelName(),
        ]);
    }

    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => [
                'required',
                'string',
                'max:12000',
            ],

            'history' => [
                'nullable',
                'array',
                'max:20',
            ],

            'history.*.role' => [
                'required_with:history',
                'string',
                Rule::in([
                    'user',
                    'assistant',
                ]),
            ],

            'history.*.content' => [
                'required_with:history',
                'string',
                'max:12000',
            ],
        ], [
            'message.required' => 'Typ eerst een bericht.',
            'message.max' => 'Je bericht is te lang.',
            'history.max' => 'Deze chat bevat te veel context. Start een nieuwe chat.',
        ]);

        $messages = [];

        $systemPrompt = trim(
            (string) config(
                'modal-chat.system_prompt',
                ''
            )
        );

        if ($systemPrompt !== '') {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }

        foreach (
            (array) ($validated['history'] ?? [])
            as $historyItem
        ) {
            if (
                ! is_array($historyItem) ||
                ! isset(
                    $historyItem['role'],
                    $historyItem['content']
                )
            ) {
                continue;
            }

            $messages[] = [
                'role' => (string) $historyItem['role'],
                'content' => (string) $historyItem['content'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => (string) $validated['message'],
        ];

        try {
            $result = $this->modalChat->chat(
                $messages
            );

            return response()->json([
                'ok' => true,
                'message' => $result['message'],
                'model' => $result['model'],
                'usage' => $result['usage'],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'ok' => false,
                'message' => 'Mashal AI kon nu geen antwoord ophalen. Probeer het opnieuw.',
            ], 502);
        }
    }
}
