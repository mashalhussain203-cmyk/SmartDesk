<?php

namespace App\Http\Controllers;

use App\Services\LiveChatEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class MailgunLiveChatWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        LiveChatEmailService $email,
        string $secret
    ): JsonResponse {
        try {
            return response()
                ->json(
                    $email->receiveMailgun(
                        $request,
                        $secret
                    )
                )
                ->header(
                    'Cache-Control',
                    'no-store'
                );
        } catch (Throwable $exception) {
            /*
             * HTTP exceptions (403/404/409/503) moeten hun eigen status
             * behouden. Andere fouten laten we door Laravel afhandelen.
             */
            throw $exception;
        }
    }
}
