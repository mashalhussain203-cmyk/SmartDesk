<?php

namespace App\Http\Controllers;

use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GmailController extends Controller
{
    /**
     * ============================================
     * GOOGLE CLIENT
     * ============================================
     */
    private function makeGoogleClient(): GoogleClient
    {
        $client = new GoogleClient();

        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        // Nodig zodat we later zonder opnieuw inloggen
        // een verlopen access token kunnen vernieuwen.
        $client->setAccessType('offline');

        // Vraag bestaande toestemmingen mee.
        $client->setIncludeGrantedScopes(true);

        // We willen de gebruiker zijn account laten kiezen
        // en toestemming laten geven.
        $client->setPrompt('select_account consent');

        // Alleen lezen + verzenden.
        // We geven de website bewust geen verwijder/rechten.
        $client->addScope([
            Gmail::GMAIL_READONLY,
            Gmail::GMAIL_SEND,
        ]);

        return $client;
    }

    /**
     * ============================================
     * CONTROLEER WEBSITE-GEBRUIKER
     * ============================================
     */
    private function requireGmailWebsiteUser(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Je moet eerst ingelogd zijn.');
        }

        $email = strtolower(trim((string) $user->email));

        if (!Str::endsWith($email, '@gmail.com')) {
            abort(403, 'De Gmail functie is alleen beschikbaar voor @gmail.com accounts.');
        }

        return $user;
    }

    /**
     * ============================================
     * GOOGLE OAUTH STARTEN
     *
     * GET /gmail/connect
     * ============================================
     */
    public function redirectToGoogle(Request $request)
    {
        $user = $this->requireGmailWebsiteUser($request);

        $client = $this->makeGoogleClient();

        // Extra beveiliging tegen OAuth/CSRF-aanvallen.
        $state = Str::random(64);

        session([
            'google_gmail_oauth_state' => $state,
        ]);

        $client->setState($state);

        // Laat Google alvast weten welk account
        // waarschijnlijk gebruikt moet worden.
        $client->setLoginHint($user->email);

        return redirect()->away($client->createAuthUrl());
    }

    /**
     * ============================================
     * GOOGLE CALLBACK
     *
     * GET /auth/google/callback
     * ============================================
     */
    public function handleGoogleCallback(Request $request)
    {
        $user = $this->requireGmailWebsiteUser($request);

        /*
         * Gebruiker heeft geweigerd.
         */
        if ($request->filled('error')) {
            return redirect('/mail')->with(
                'error',
                'Je hebt geen toestemming gegeven voor Gmail.'
            );
        }

        /*
         * OAuth state controleren.
         */
        $expectedState = session()->pull('google_gmail_oauth_state');
        $receivedState = $request->query('state');

        if (
            !$expectedState ||
            !$receivedState ||
            !hash_equals($expectedState, $receivedState)
        ) {
            abort(419, 'Ongeldige Google OAuth sessie. Probeer opnieuw.');
        }

        /*
         * Google moet een authorization code terugsturen.
         */
        $code = $request->query('code');

        if (!$code) {
            return redirect('/mail')->with(
                'error',
                'Google heeft geen geldige authorization code teruggestuurd.'
            );
        }

        try {
            $client = $this->makeGoogleClient();

            /*
             * Authorization code omwisselen
             * voor access/refresh token.
             */
            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                $description = $token['error_description']
                    ?? $token['error'];

                throw new RuntimeException($description);
            }

            /*
             * Google Client heeft "created" nodig
             * om expiry goed te kunnen bepalen.
             */
            $token['created'] = $token['created'] ?? time();

            $client->setAccessToken($token);

            /*
             * Controleren welk Gmail-account
             * werkelijk toestemming heeft gegeven.
             */
            $gmail = new Gmail($client);

            $profile = $gmail->users->getProfile('me');

            $googleEmail = strtolower(
                trim((string) $profile->getEmailAddress())
            );

            $websiteEmail = strtolower(
                trim((string) $user->email)
            );

            /*
             * Alleen echte @gmail.com adressen.
             */
            if (!Str::endsWith($googleEmail, '@gmail.com')) {
                try {
                    $client->revokeToken();
                } catch (Throwable $e) {
                    // Geen probleem als revoke niet lukt.
                }

                return redirect('/mail')->with(
                    'error',
                    'Je moet een @gmail.com account koppelen.'
                );
            }

            /*
             * Zeer belangrijk:
             *
             * Websiteaccount:
             * gebruiker@gmail.com
             *
             * mag niet Gmail koppelen van:
             * iemandanders@gmail.com
             */
            if ($googleEmail !== $websiteEmail) {
                try {
                    $client->revokeToken();
                } catch (Throwable $e) {
                    // Geen probleem als revoke niet lukt.
                }

                return redirect('/mail')->with(
                    'error',
                    'Je moet hetzelfde Gmail-account koppelen waarmee je op de website bent ingelogd.'
                );
            }

            /*
             * Soms stuurt Google bij opnieuw koppelen
             * geen nieuwe refresh token.
             *
             * Daarom bewaren we de bestaande token.
             */
            $refreshToken = $token['refresh_token'] ?? null;

            if (!$refreshToken && $user->google_refresh_token) {
                $refreshToken = $this->decryptValue(
                    $user->google_refresh_token
                );
            }

            /*
             * Refresh token niet dubbel in de access-token JSON bewaren.
             */
            unset($token['refresh_token']);

            /*
             * Tokens versleuteld opslaan.
             */
            $user->google_access_token = Crypt::encryptString(
                json_encode($token)
            );

            if ($refreshToken) {
                $user->google_refresh_token = Crypt::encryptString(
                    $refreshToken
                );
            }

            $user->google_gmail_email = $googleEmail;

            $user->google_token_expires_at = now()->addSeconds(
                (int) ($token['expires_in'] ?? 3600)
            );

            $user->gmail_connected_at = now();

            $user->save();

            return redirect('/mail')->with(
                'success',
                'Je Gmail-account is gekoppeld.'
            );
        } catch (Throwable $e) {
            report($e);

            return redirect('/mail')->with(
                'error',
                'Gmail koppelen is mislukt: ' . $e->getMessage()
            );
        }
    }

    /**
     * ============================================
     * GELDIG GOOGLE CLIENT VOOR DE GEBRUIKER
     * ============================================
     */
    private function authorizedClient($user): GoogleClient
    {
        if (!$user->google_access_token) {
            throw new RuntimeException(
                'Je Gmail-account is nog niet gekoppeld.'
            );
        }

        $encryptedAccessToken = $this->decryptValue(
            $user->google_access_token
        );

        if (!$encryptedAccessToken) {
            throw new RuntimeException(
                'Het opgeslagen Gmail access token is ongeldig.'
            );
        }

        $accessToken = json_decode(
            $encryptedAccessToken,
            true
        );

        if (!is_array($accessToken)) {
            throw new RuntimeException(
                'Het opgeslagen Gmail access token heeft een ongeldig formaat.'
            );
        }

        $client = $this->makeGoogleClient();
        $client->setAccessToken($accessToken);

        /*
         * Token verlopen?
         */
        if ($client->isAccessTokenExpired()) {
            $refreshToken = $this->decryptValue(
                $user->google_refresh_token
            );

            if (!$refreshToken) {
                throw new RuntimeException(
                    'Je Gmail-sessie is verlopen. Koppel Gmail opnieuw.'
                );
            }

            $newToken = $client->fetchAccessTokenWithRefreshToken(
                $refreshToken
            );

            if (isset($newToken['error'])) {
                throw new RuntimeException(
                    $newToken['error_description']
                    ?? $newToken['error']
                );
            }

            $newToken['created'] = $newToken['created'] ?? time();

            /*
             * Sommige responses kunnen eventueel
             * opnieuw een refresh token bevatten.
             */
            if (!empty($newToken['refresh_token'])) {
                $refreshToken = $newToken['refresh_token'];

                $user->google_refresh_token = Crypt::encryptString(
                    $refreshToken
                );
            }

            unset($newToken['refresh_token']);

            $client->setAccessToken($newToken);

            $user->google_access_token = Crypt::encryptString(
                json_encode($newToken)
            );

            $user->google_token_expires_at = now()->addSeconds(
                (int) ($newToken['expires_in'] ?? 3600)
            );

            $user->save();
        }

        return $client;
    }

    /**
     * ============================================
     * INBOX
     *
     * GET /mail
     * ============================================
     */
    public function inbox(Request $request)
    {
        $user = $this->requireGmailWebsiteUser($request);

        /*
         * Nog niet gekoppeld?
         *
         * Dan kan de Blade-view bijvoorbeeld
         * een "Koppel Gmail" knop tonen.
         */
        if (!$user->google_access_token) {
            return view('gmail.inbox', [
                'connected' => false,
                'messages' => [],
                'nextPageToken' => null,
                'query' => '',
            ]);
        }

        try {
            $client = $this->authorizedClient($user);

            $gmail = new Gmail($client);

            $query = trim((string) $request->query('q', ''));
            $pageToken = $request->query('pageToken');

            $options = [
                'maxResults' => 20,
                'labelIds' => ['INBOX'],
            ];

            if ($query !== '') {
                $options['q'] = $query;
            }

            if ($pageToken) {
                $options['pageToken'] = $pageToken;
            }

            $list = $gmail->users_messages->listUsersMessages(
                'me',
                $options
            );

            $messages = [];

            foreach ($list->getMessages() ?? [] as $messageReference) {
                /*
                 * Alleen metadata ophalen voor inbox.
                 * Niet meteen volledige mails downloaden.
                 */
                $message = $gmail->users_messages->get(
                    'me',
                    $messageReference->getId(),
                    [
                        'format' => 'metadata',
                        'metadataHeaders' => [
                            'From',
                            'To',
                            'Subject',
                            'Date',
                        ],
                    ]
                );

                $headers = $this->headersToArray(
                    $message->getPayload()?->getHeaders() ?? []
                );

                $messages[] = [
                    'id' => $message->getId(),
                    'thread_id' => $message->getThreadId(),
                    'from' => $headers['from'] ?? '(Onbekend)',
                    'to' => $headers['to'] ?? '',
                    'subject' => $headers['subject'] ?? '(Geen onderwerp)',
                    'date' => $headers['date'] ?? '',
                    'snippet' => $message->getSnippet() ?? '',
                    'label_ids' => $message->getLabelIds() ?? [],
                ];
            }

            return view('gmail.inbox', [
                'connected' => true,
                'messages' => $messages,
                'nextPageToken' => $list->getNextPageToken(),
                'query' => $query,
            ]);
        } catch (Throwable $e) {
            report($e);

            return view('gmail.inbox', [
                'connected' => false,
                'messages' => [],
                'nextPageToken' => null,
                'query' => '',
                'gmailError' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ============================================
     * ÉÉN MAIL OPENEN
     *
     * GET /mail/{id}
     * ============================================
     */
    public function show(Request $request, string $id)
    {
        $user = $this->requireGmailWebsiteUser($request);

        try {
            $client = $this->authorizedClient($user);

            $gmail = new Gmail($client);

            $message = $gmail->users_messages->get(
                'me',
                $id,
                [
                    'format' => 'full',
                ]
            );

            $payload = $message->getPayload();

            $headers = $this->headersToArray(
                $payload?->getHeaders() ?? []
            );

            /*
             * Voor veiligheid tonen we bij voorkeur
             * text/plain.
             *
             * HTML-e-mails worden omgezet naar gewone tekst.
             * Zo voeren we geen willekeurige HTML/JS uit.
             */
            $body = $this->extractReadableBody($payload);

            return view('gmail.show', [
                'message' => [
                    'id' => $message->getId(),
                    'thread_id' => $message->getThreadId(),

                    'from' => $headers['from'] ?? '(Onbekend)',
                    'to' => $headers['to'] ?? '',
                    'cc' => $headers['cc'] ?? '',

                    'subject' => $headers['subject']
                        ?? '(Geen onderwerp)',

                    'date' => $headers['date'] ?? '',

                    'body' => $body,

                    'snippet' => $message->getSnippet() ?? '',
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return redirect('/mail')->with(
                'error',
                'Deze e-mail kon niet worden geopend.'
            );
        }
    }

    /**
     * ============================================
     * MAIL VERSTUREN
     *
     * POST /mail/send
     * ============================================
     */
    public function send(Request $request)
    {
        $user = $this->requireGmailWebsiteUser($request);

        $validated = $request->validate([
            'to' => [
                'required',
                'email:rfc',
                'max:254',
            ],

            'subject' => [
                'nullable',
                'string',
                'max:998',
            ],

            'body' => [
                'required',
                'string',
                'max:100000',
            ],
        ]);

        try {
            $client = $this->authorizedClient($user);

            $gmail = new Gmail($client);

            /*
             * Header injection voorkomen.
             */
            $subject = str_replace(
                ["\r", "\n"],
                '',
                $validated['subject'] ?? ''
            );

            $to = str_replace(
                ["\r", "\n"],
                '',
                $validated['to']
            );

            $from = strtolower(
                trim((string) $user->email)
            );

            /*
             * RFC/MIME e-mail maken.
             */
            $rawMessage =
                'From: <' . $from . ">\r\n" .
                'To: <' . $to . ">\r\n" .
                'Subject: ' .
                mb_encode_mimeheader(
                    $subject,
                    'UTF-8',
                    'B',
                    "\r\n"
                ) .
                "\r\n" .
                "MIME-Version: 1.0\r\n" .
                "Content-Type: text/plain; charset=UTF-8\r\n" .
                "Content-Transfer-Encoding: quoted-printable\r\n" .
                "\r\n" .
                quoted_printable_encode($validated['body']);

            $encodedMessage = $this->base64UrlEncode(
                $rawMessage
            );

            $message = new Message();
            $message->setRaw($encodedMessage);

            $sent = $gmail->users_messages->send(
                'me',
                $message
            );

            return redirect('/mail')->with(
                'success',
                'E-mail is verzonden.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'E-mail verzenden is mislukt: ' .
                    $e->getMessage()
                );
        }
    }

    /**
     * ============================================
     * GMAIL ONTKOPPELEN
     *
     * POST /gmail/disconnect
     * ============================================
     */
    public function disconnect(Request $request)
    {
        $user = $this->requireGmailWebsiteUser($request);

        try {
            if ($user->google_access_token) {
                $client = $this->authorizedClient($user);

                try {
                    $client->revokeToken();
                } catch (Throwable $e) {
                    /*
                     * Ook als Google revoke mislukt,
                     * verwijderen we lokaal de tokens.
                     */
                }
            }
        } catch (Throwable $e) {
            /*
             * Tokens kunnen al ongeldig zijn.
             * Toch lokaal verwijderen.
             */
        }

        $user->google_access_token = null;
        $user->google_refresh_token = null;
        $user->google_token_expires_at = null;
        $user->google_gmail_email = null;
        $user->gmail_connected_at = null;

        $user->save();

        return redirect('/mail')->with(
            'success',
            'Gmail is ontkoppeld.'
        );
    }

    /**
     * ============================================
     * GMAIL HEADERS → ARRAY
     * ============================================
     */
    private function headersToArray(iterable $headers): array
    {
        $result = [];

        foreach ($headers as $header) {
            $name = strtolower(
                trim((string) $header->getName())
            );

            $result[$name] = $header->getValue();
        }

        return $result;
    }

    /**
     * ============================================
     * MAIL BODY LEZEN
     * ============================================
     */
    private function extractReadableBody($payload): string
    {
        if (!$payload) {
            return '';
        }

        /*
         * Eerst text/plain zoeken.
         */
        $plain = $this->findMimePart(
            $payload,
            'text/plain'
        );

        if ($plain !== null && trim($plain) !== '') {
            return $plain;
        }

        /*
         * Geen plain-text?
         * Dan HTML pakken maar omzetten naar tekst.
         */
        $html = $this->findMimePart(
            $payload,
            'text/html'
        );

        if ($html !== null) {
            $html = preg_replace(
                '/<\s*br\s*\/?>/i',
                "\n",
                $html
            );

            $html = preg_replace(
                '/<\/p\s*>/i',
                "\n\n",
                $html
            );

            $text = strip_tags($html);

            return trim(
                html_entity_decode(
                    $text,
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                )
            );
        }

        /*
         * Sommige simpele mails hebben body direct
         * op het hoofd-payload object.
         */
        $data = $payload->getBody()?->getData();

        if ($data) {
            return $this->base64UrlDecode($data);
        }

        return '';
    }

    /**
     * ============================================
     * MIME PART RECURSIEF ZOEKEN
     * ============================================
     */
    private function findMimePart(
        $part,
        string $wantedMimeType
    ): ?string {
        if (!$part) {
            return null;
        }

        $mimeType = strtolower(
            (string) $part->getMimeType()
        );

        if ($mimeType === strtolower($wantedMimeType)) {
            $data = $part->getBody()?->getData();

            if ($data) {
                return $this->base64UrlDecode($data);
            }
        }

        foreach ($part->getParts() ?? [] as $childPart) {
            $found = $this->findMimePart(
                $childPart,
                $wantedMimeType
            );

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * ============================================
     * BASE64 URL ENCODE
     * ============================================
     */
    private function base64UrlEncode(string $value): string
    {
        return rtrim(
            strtr(
                base64_encode($value),
                '+/',
                '-_'
            ),
            '='
        );
    }

    /**
     * ============================================
     * BASE64 URL DECODE
     * ============================================
     */
    private function base64UrlDecode(string $value): string
    {
        $value = strtr(
            $value,
            '-_',
            '+/'
        );

        $remainder = strlen($value) % 4;

        if ($remainder) {
            $value .= str_repeat(
                '=',
                4 - $remainder
            );
        }

        $decoded = base64_decode(
            $value,
            true
        );

        return $decoded !== false
            ? $decoded
            : '';
    }

    /**
     * ============================================
     * VERSLEUTELDE DATABASEWAARDE LEZEN
     * ============================================
     */
    private function decryptValue(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            return null;
        }
    }
}
               
