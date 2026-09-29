<?php

namespace App\Http\Controllers;

use App\Services\GroqChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class GuestChatController extends Controller
{
    public function store(Request $request, GroqChatService $groqChat): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'history' => ['sometimes', 'array', 'max:10'],
            'history.*' => ['required', 'array:role,content'],
            'history.*.role' => ['required', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:4000'],
        ]);

        if (! $groqChat->isConfigured()) {
            return response()->json([
                'message' => 'De AI-chat is nog niet beschikbaar. Probeer het later opnieuw.',
            ], 503);
        }

        $messages = [[
            'role' => 'system',
            'content' => 'Je bent Mashal AI, een vriendelijke algemene AI-assistent op Mashal Studio. '
                .'Beantwoord ook vragen die niet over de website gaan. Antwoord standaard in het Nederlands, '
                .'of in de taal van de bezoeker. Houd antwoorden helder en beknopt. '
                .'Je hebt geen toegang tot accounts, privégegevens, bestanden of live internet. '
                .'Verzin geen websitefuncties, prijzen of uitgevoerde handelingen. '
                .'Mashal Studio biedt afbeeldingen uploaden, bewerken en opslaan en een AI-chat. '
                .'Voor de persoonlijke werkruimte moet de bezoeker inloggen. Vraag nooit om wachtwoorden of geheime sleutels.',
        ]];

        foreach ($validated['history'] ?? [] as $message) {
            $messages[] = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $validated['message']];

        try {
            $result = $groqChat->chat($messages, ['mode' => 'plain']);

            return response()->json(['message' => $result['message']]);
        } catch (Throwable $exception) {
            Log::warning('Guest AI chat request failed.', [
                'exception_type' => $exception::class,
                'provider_status' => $groqChat->lastStatus(),
            ]);

            if ($groqChat->lastStatus() === 429) {
                return response()->json([
                    'message' => 'De AI is momenteel druk. Wacht even en probeer het opnieuw.',
                ], 429)->header('Retry-After', (string) ($groqChat->lastRetryAfterSeconds() ?? 30));
            }

            return response()->json([
                'message' => 'De AI kon nu geen antwoord geven. Probeer het straks opnieuw.',
            ], 503);
        }
    }
}
