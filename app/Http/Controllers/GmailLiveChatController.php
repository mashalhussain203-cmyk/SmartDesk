<?php

namespace App\Http\Controllers;

use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GmailLiveChatController extends Controller
{
    private const TOKEN_CACHE_KEY = 'live_chat_gmail_oauth_token';
    private const STATE_SESSION_KEY = 'live_chat_gmail_oauth_state';

    public function redirectToGoogle(Request $request): RedirectResponse
    {
        try {
            $client = $this->makeGoogleClient();

            $state = bin2hex(random_bytes(32));

            $request->session()->put(
                self::STATE_SESSION_KEY,
                $state
            );

            $client->setState($state);

            return redirect()->away(
                $client->createAuthUrl()
            );
        } catch (Throwable $exception) {
            report($exception);

            Log::error('Live Chat Gmail redirect mislukt.', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('admin.live-chat.index')
                ->with(
                    'error',
                    'Gmail kon niet worden gekoppeld. Controleer de Google OAuth-instellingen.'
                );
        }
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()
                ->route('admin.live-chat.index')
                ->with(
                    'error',
                    'Google Gmail-koppeling is geannuleerd of geweigerd.'
                );
        }

        $expectedState = (string) $request->session()->pull(
            self::STATE_SESSION_KEY,
            ''
        );

        $receivedState = (string) $request->query(
            'state',
            ''
        );

        if (
            $expectedState === ''
            || $receivedState === ''
            || ! hash_equals($expectedState, $receivedState)
        ) {
            return redirect()
                ->route('admin.live-chat.index')
                ->with(
                    'error',
                    'De Gmail-koppeling kon niet worden gecontroleerd. Probeer opnieuw.'
                );
        }

        $authorizationCode = (string) $request->query(
            'code',
            ''
        );

        if ($authorizationCode === '') {
            return redirect()
                ->route('admin.live-chat.index')
                ->with(
                    'error',
                    'Google heeft geen autorisatiecode teruggestuurd.'
                );
        }

        try {
            $client = $this->makeGoogleClient();

            $token = $client->fetchAccessTokenWithAuthCode(
                $authorizationCode
            );

            if (
                isset($token['error'])
                || empty($token['access_token'])
            ) {
                $message = $token['error_description']
                    ?? $token['error']
                    ?? 'Onbekende Google OAuth-fout.';

                throw new RuntimeException($message);
            }

            $existingToken = $this->getStoredToken();

            if (
                empty($token['refresh_token'])
                && ! empty($existingToken['refresh_token'])
            ) {
                $token['refresh_token'] = $existingToken['refresh_token'];
            }

            $client->setAccessToken($token);

            $gmail = new Gmail($client);

            $profile = $gmail->users->getProfile('me');

            $connectedEmail = strtolower(
                trim((string) $profile->getEmailAddress())
            );

            $expectedEmail = strtolower(
                trim((string) env(
                    'LIVE_CHAT_GMAIL_USERNAME',
                    ''
                ))
            );

            if ($expectedEmail === '') {
                throw new RuntimeException(
                    'LIVE_CHAT_GMAIL_USERNAME ontbreekt.'
                );
            }

            if ($connectedEmail !== $expectedEmail) {
                return redirect()
                    ->route('admin.live-chat.index')
                    ->with(
                        'error',
                        'Het gekoppelde Google-account komt niet overeen met LIVE_CHAT_GMAIL_USERNAME.'
                    );
            }

            $this->storeToken($token);

            return redirect()
                ->route('admin.live-chat.index')
                ->with(
                    'success',
                    'Gmail is succesvol gekoppeld aan Live Chat.'
                );
        } catch (Throwable $exception) {
            report($exception);

            Log::error('Live Chat Gmail OAuth callback mislukt.', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('admin.live-chat.index')
                ->with(
                    'error',
                    'Gmail kon niet worden gekoppeld. Controleer de Google OAuth-instellingen en probeer opnieuw.'
                );
        }
    }

    public function status(): array
    {
        $token = $this->getStoredToken();

        return [
            'connected' => ! empty(
                $token['refresh_token']
                ?? $token['access_token']
                ?? null
            ),
            'email' => env('LIVE_CHAT_GMAIL_USERNAME'),
        ];
    }

    public function disconnect(): RedirectResponse
    {
        try {
            $token = $this->getStoredToken();

            if (! empty($token['access_token'])) {
                try {
                    $client = $this->makeGoogleClient();
                    $client->revokeToken($token['access_token']);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }

            $this->forgetStoredToken();

            return redirect()
                ->route('admin.live-chat.index')
                ->with(
                    'success',
                    'Gmail is ontkoppeld van Live Chat.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.live-chat.index')
                ->with(
                    'error',
                    'Gmail kon niet volledig worden ontkoppeld.'
                );
        }
    }

    private function makeGoogleClient(): GoogleClient
    {
        $clientId = trim((string) env(
            'LIVE_CHAT_GMAIL_CLIENT_ID',
            ''
        ));

        $clientSecret = trim((string) env(
            'LIVE_CHAT_GMAIL_CLIENT_SECRET',
            ''
        ));

        if ($clientId === '') {
            throw new RuntimeException(
                'LIVE_CHAT_GMAIL_CLIENT_ID ontbreekt.'
            );
        }

        if ($clientSecret === '') {
            throw new RuntimeException(
                'LIVE_CHAT_GMAIL_CLIENT_SECRET ontbreekt.'
            );
        }

        $client = new GoogleClient();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($this->redirectUri());
        $client->setScopes([
            Gmail::GMAIL_SEND,
            Gmail::GMAIL_READONLY,
        ]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setIncludeGrantedScopes(true);

        return $client;
    }

    private function redirectUri(): string
    {
        $configured = trim((string) env(
            'LIVE_CHAT_GMAIL_REDIRECT_URI',
            ''
        ));

        if ($configured !== '') {
            return $configured;
        }

        return route('admin.live-chat.gmail.callback');
    }

    private function storeToken(array $token): void
    {
        $json = json_encode(
            $token,
            JSON_THROW_ON_ERROR
        );

        $encrypted = Crypt::encryptString($json);

        Cache::forever(
            self::TOKEN_CACHE_KEY,
            $encrypted
        );
    }

    private function getStoredToken(): array
    {
        $encrypted = Cache::get(
            self::TOKEN_CACHE_KEY
        );

        if (
            ! is_string($encrypted)
            || $encrypted === ''
        ) {
            return [];
        }

        try {
            $json = Crypt::decryptString($encrypted);

            $token = json_decode(
                $json,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            return is_array($token)
                ? $token
                : [];
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }
    }

    private function forgetStoredToken(): void
    {
        Cache::forget(
            self::TOKEN_CACHE_KEY
        );
    }
}
