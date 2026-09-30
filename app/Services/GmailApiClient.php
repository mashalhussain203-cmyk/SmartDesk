<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GmailApiClient
{
    public function configured(): bool
    {
        return $this->username() !== ''
            && $this->clientId() !== ''
            && $this->clientSecret() !== ''
            && $this->refreshToken() !== '';
    }

    public function oauthClientConfigured(): bool
    {
        return $this->clientId() !== ''
            && $this->clientSecret() !== '';
    }

    public function username(): string
    {
        return strtolower(trim((string) config('live-chat-email.gmail_username')));
    }

    public function redirectUri(): string
    {
        $configured = trim((string) config('live-chat-email.gmail_redirect_uri'));

        if ($configured !== '') {
            return $configured;
        }

        return rtrim((string) config('app.url'), '/')
            .'/admin/live-chat/gmail/callback';
    }

    public function authorizationUrl(string $state): string
    {
        if (! $this->oauthClientConfigured()) {
            throw new RuntimeException(
                'LIVE_CHAT_GMAIL_CLIENT_ID en LIVE_CHAT_GMAIL_CLIENT_SECRET ontbreken.'
            );
        }

        $query = http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => (string) config(
                'live-chat-email.gmail_scope',
                'https://www.googleapis.com/auth/gmail.modify'
            ),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);

        return rtrim(
            (string) config(
                'live-chat-email.google_authorize_endpoint',
                'https://accounts.google.com/o/oauth2/v2/auth'
            ),
            '?'
        ).'?'.$query;
    }

    /**
     * @return array<string,mixed>
     */
    public function exchangeAuthorizationCode(string $code): array
    {
        $response = Http::asForm()
            ->acceptJson()
            ->timeout($this->timeout())
            ->post(
                (string) config(
                    'live-chat-email.google_token_endpoint',
                    'https://oauth2.googleapis.com/token'
                ),
                [
                    'client_id' => $this->clientId(),
                    'client_secret' => $this->clientSecret(),
                    'code' => $code,
                    'redirect_uri' => $this->redirectUri(),
                    'grant_type' => 'authorization_code',
                ]
            );

        if (! $response->successful()) {
            throw new RuntimeException(
                'Google OAuth gaf HTTP '.$response->status().': '
                .$this->responseError($response)
            );
        }

        return (array) $response->json();
    }

    public function storeRefreshToken(string $refreshToken): void
    {
        $refreshToken = trim($refreshToken);

        if ($refreshToken === '') {
            throw new RuntimeException('Google gaf geen refresh token terug.');
        }

        if (! Schema::hasTable('live_chat_settings')) {
            throw new RuntimeException(
                'De Gmail-migratie is nog niet uitgevoerd. Voer php artisan migrate uit.'
            );
        }

        DB::table('live_chat_settings')->updateOrInsert(
            ['key' => 'gmail_refresh_token'],
            [
                'value' => Crypt::encryptString($refreshToken),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        Cache::forget($this->tokenCacheKey());
    }

    public function clearStoredRefreshToken(): void
    {
        if (Schema::hasTable('live_chat_settings')) {
            DB::table('live_chat_settings')
                ->where('key', 'gmail_refresh_token')
                ->delete();
        }

        Cache::forget($this->tokenCacheKey());
    }

    /**
     * @return array<string,mixed>
     */
    public function sendRaw(string $rawMessage, ?string $threadId = null): array
    {
        $payload = [
            'raw' => $this->base64UrlEncode($rawMessage),
        ];

        $threadId = trim((string) $threadId);

        if ($threadId !== '') {
            $payload['threadId'] = $threadId;
        }

        return $this->request(
            'POST',
            '/users/me/messages/send',
            [],
            $payload
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listUnread(int $limit = 25): array
    {
        $result = $this->request(
            'GET',
            '/users/me/messages',
            [
                'q' => 'is:unread in:inbox',
                'maxResults' => max(1, min(100, $limit)),
            ]
        );

        return array_values(
            array_filter(
                (array) ($result['messages'] ?? []),
                'is_array'
            )
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function getMessage(string $messageId, string $format = 'full'): array
    {
        return $this->request(
            'GET',
            '/users/me/messages/'.rawurlencode($messageId),
            ['format' => $format]
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function getThread(string $threadId, string $format = 'metadata'): array
    {
        return $this->request(
            'GET',
            '/users/me/threads/'.rawurlencode($threadId),
            ['format' => $format]
        );
    }

    public function getAttachment(string $messageId, string $attachmentId): string
    {
        $result = $this->request(
            'GET',
            '/users/me/messages/'.rawurlencode($messageId)
                .'/attachments/'.rawurlencode($attachmentId)
        );

        return $this->base64UrlDecode(
            (string) ($result['data'] ?? '')
        );
    }

    public function markRead(string $messageId): void
    {
        $this->markProcessed($messageId, false);
    }

    /**
     * Markeert een succesvol door SmartDesk verwerkt klantbericht als gelezen.
     * Als $archive true is, wordt ook het INBOX-label verwijderd zodat de
     * technische Gmail-mailbox schoon blijft. Het bericht blijft in Gmail
     * onder Alle e-mail beschikbaar en de threadId blijft intact.
     */
    public function markProcessed(
        string $messageId,
        bool $archive = true
    ): void {
        $removeLabelIds = ['UNREAD'];

        if ($archive) {
            $removeLabelIds[] = 'INBOX';
        }

        $this->request(
            'POST',
            '/users/me/messages/'.rawurlencode($messageId).'/modify',
            [],
            ['removeLabelIds' => $removeLabelIds]
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function profile(): array
    {
        return $this->request('GET', '/users/me/profile');
    }

    /**
     * @return array<string,mixed>
     */
    private function request(
        string $method,
        string $path,
        array $query = [],
        ?array $json = null,
        bool $retry = true
    ): array {
        $url = rtrim(
            (string) config(
                'live-chat-email.gmail_api_base',
                'https://gmail.googleapis.com/gmail/v1'
            ),
            '/'
        ).$path;

        $request = Http::acceptJson()
            ->withToken($this->accessToken())
            ->timeout($this->timeout());

        if ($query !== []) {
            $request = $request->withQueryParameters($query);
        }

        $response = $json === null
            ? $request->send($method, $url)
            : $request->asJson()->send($method, $url, ['json' => $json]);

        if ($response->status() === 401 && $retry) {
            Cache::forget($this->tokenCacheKey());

            return $this->request(
                $method,
                $path,
                $query,
                $json,
                false
            );
        }

        if (! $response->successful()) {
            throw new RuntimeException(
                'Gmail API gaf HTTP '.$response->status().': '
                .$this->responseError($response)
            );
        }

        return (array) ($response->json() ?? []);
    }

    private function accessToken(): string
    {
        if (! $this->configured()) {
            throw new RuntimeException(
                'Gmail API is nog niet gekoppeld. Open /admin/live-chat/gmail/connect.'
            );
        }

        $cacheKey = $this->tokenCacheKey();
        $cached = trim((string) Cache::get($cacheKey, ''));

        if ($cached !== '') {
            return $cached;
        }

        $response = Http::asForm()
            ->acceptJson()
            ->timeout($this->timeout())
            ->post(
                (string) config(
                    'live-chat-email.google_token_endpoint',
                    'https://oauth2.googleapis.com/token'
                ),
                [
                    'client_id' => $this->clientId(),
                    'client_secret' => $this->clientSecret(),
                    'refresh_token' => $this->refreshToken(),
                    'grant_type' => 'refresh_token',
                ]
            );

        if (! $response->successful()) {
            throw new RuntimeException(
                'Google kon het Gmail access token niet vernieuwen: '
                .$this->responseError($response)
            );
        }

        $token = trim((string) $response->json('access_token'));
        $expires = max(300, (int) $response->json('expires_in', 3600));

        if ($token === '') {
            throw new RuntimeException(
                'Google gaf geen Gmail access token terug.'
            );
        }

        Cache::put(
            $cacheKey,
            $token,
            now()->addSeconds(max(60, $expires - 120))
        );

        return $token;
    }

    private function refreshToken(): string
    {
        $environment = trim(
            (string) config('live-chat-email.gmail_refresh_token')
        );

        if ($environment !== '') {
            return $environment;
        }

        try {
            if (! Schema::hasTable('live_chat_settings')) {
                return '';
            }

            $encrypted = (string) DB::table('live_chat_settings')
                ->where('key', 'gmail_refresh_token')
                ->value('value');

            if ($encrypted === '') {
                return '';
            }

            return trim(Crypt::decryptString($encrypted));
        } catch (Throwable) {
            return '';
        }
    }

    private function clientId(): string
    {
        return trim((string) config('live-chat-email.gmail_client_id'));
    }

    private function clientSecret(): string
    {
        return trim((string) config('live-chat-email.gmail_client_secret'));
    }

    private function timeout(): int
    {
        return max(
            5,
            (int) config('live-chat-email.gmail_api_timeout_seconds', 20)
        );
    }

    private function tokenCacheKey(): string
    {
        return 'live-chat:gmail-token:'.hash(
            'sha256',
            $this->username().'|'.$this->clientId()
        );
    }

    private function responseError(Response $response): string
    {
        $message = trim((string) (
            $response->json('error.message')
            ?: $response->json('error_description')
            ?: $response->json('error')
            ?: $response->body()
        ));

        return Str::limit(
            $message !== '' ? $message : 'Onbekende Google-fout.',
            800,
            ''
        );
    }

    public function base64UrlDecode(string $value): string
    {
        $value = strtr($value, '-_', '+/');
        $padding = strlen($value) % 4;

        if ($padding !== 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        return base64_decode($value, true) ?: '';
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(
            strtr(base64_encode($value), '+/', '-_'),
            '='
        );
    }
}
