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
    /*
    |--------------------------------------------------------------------------
    | Google Gmail Client
    |--------------------------------------------------------------------------
    |
    | Deze client wordt uitsluitend gebruikt voor de Gmail API.
    |
    | De normale Google-login van Mashal Studio blijft via
    | GoogleAuthController lopen.
    |
    */

    private function makeGoogleClient(): GoogleClient
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.gmail_redirect');

        if (!$clientId) {
            throw new RuntimeException(
                'GOOGLE_CLIENT_ID ontbreekt.'
            );
        }

        if (!$clientSecret) {
            throw new RuntimeException(
                'GOOGLE_CLIENT_SECRET ontbreekt.'
            );
        }

        if (!$redirectUri) {
            throw new RuntimeException(
                'GOOGLE_GMAIL_REDIRECT_URI ontbreekt.'
            );
        }

        $client = new GoogleClient();

        $client->setClientId($clientId);

        $client->setClientSecret(
            $clientSecret
        );

        /*
        |--------------------------------------------------------------------------
        | Belangrijk
        |--------------------------------------------------------------------------
        |
        | Gmail gebruikt een aparte callback:
        |
        | https://mashalhussain.up.railway.app/gmail/callback
        |
        */

        $client->setRedirectUri(
            $redirectUri
        );

        /*
        |--------------------------------------------------------------------------
        | Offline access
        |--------------------------------------------------------------------------
        |
        | Hiermee kan Google een refresh token leveren.
        |
        | Daardoor hoeft de gebruiker niet bij ieder verlopen access token
        | opnieuw toestemming te geven.
        |
        */

        $client->setAccessType(
            'offline'
        );

        /*
        |--------------------------------------------------------------------------
        | Bestaande permissies meenemen
        |--------------------------------------------------------------------------
        */

        $client->setIncludeGrantedScopes(
            true
        );

        /*
        |--------------------------------------------------------------------------
        | Account selecteren
        |--------------------------------------------------------------------------
        |
        | We laten de gebruiker bewust het Google-account zien dat gekoppeld
        | gaat worden.
        |
        */

        $client->setPrompt(
            'select_account consent'
        );

        /*
        |--------------------------------------------------------------------------
        | Gmail scopes
        |--------------------------------------------------------------------------
        |
        | READONLY:
        |
        | - Inbox lezen
        | - Gearchiveerde e-mail lezen
        | - Verzonden e-mail lezen
        | - Spam lezen
        | - Prullenbak lezen
        | - Zoeken
        |
        | SEND:
        |
        | - Nieuwe e-mail versturen
        | - Antwoorden versturen
        |
        | We vragen op dit moment bewust nog geen gmail.modify.
        |
        */

        $client->addScope([
            Gmail::GMAIL_READONLY,
            Gmail::GMAIL_SEND,
        ]);

        return $client;
    }


    /*
    |--------------------------------------------------------------------------
    | Gmail-gebruiker controleren
    |--------------------------------------------------------------------------
    |
    | Mashal Mail is uitsluitend beschikbaar wanneer:
    |
    | - iemand op Mashal Studio is ingelogd;
    | - het geregistreerde Mashal-account eindigt op @gmail.com.
    |
    */

    private function requireGmailWebsiteUser(
        Request $request
    ) {
        $user = $request->user();

        if (!$user) {
            abort(
                401,
                'Je moet eerst ingelogd zijn.'
            );
        }

        $email = strtolower(
            trim(
                (string) $user->email
            )
        );

        if (
            !Str::endsWith(
                $email,
                '@gmail.com'
            )
        ) {
            abort(
                403,
                'Mashal Mail is alleen beschikbaar voor @gmail.com accounts.'
            );
        }

        return $user;
    }


    /*
    |--------------------------------------------------------------------------
    | Gmail OAuth starten
    |--------------------------------------------------------------------------
    |
    | GET /gmail/connect
    |
    */

    public function redirectToGoogle(
        Request $request
    ) {
        $user =
            $this->requireGmailWebsiteUser(
                $request
            );

        $client =
            $this->makeGoogleClient();

        /*
        |--------------------------------------------------------------------------
        | OAuth state
        |--------------------------------------------------------------------------
        |
        | Beschermt tegen OAuth/CSRF-aanvallen.
        |
        */

        $state =
            Str::random(64);

        session([
            'google_gmail_oauth_state' =>
                $state,
        ]);

        $client->setState(
            $state
        );

        /*
        |--------------------------------------------------------------------------
        | Login hint
        |--------------------------------------------------------------------------
        |
        | Google krijgt alvast het Gmail-adres van de ingelogde gebruiker.
        |
        | We controleren na de callback nog steeds streng of het werkelijk
        | hetzelfde account is.
        |
        */

        $client->setLoginHint(
            $user->email
        );

        return redirect()->away(
            $client->createAuthUrl()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Gmail OAuth callback
    |--------------------------------------------------------------------------
    |
    | GET /gmail/callback
    |
    */

    public function handleGoogleCallback(
        Request $request
    ) {
        $user =
            $this->requireGmailWebsiteUser(
                $request
            );

        /*
        |--------------------------------------------------------------------------
        | Toegang geweigerd
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('error')
        ) {
            return redirect()
                ->route('gmail.inbox')
                ->with(
                    'error',
                    'Je hebt geen toestemming gegeven voor Gmail.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | OAuth state controleren
        |--------------------------------------------------------------------------
        */

        $expectedState =
            session()->pull(
                'google_gmail_oauth_state'
            );

        $receivedState =
            $request->query(
                'state'
            );

        if (
            !$expectedState ||
            !$receivedState ||
            !hash_equals(
                $expectedState,
                $receivedState
            )
        ) {
            abort(
                419,
                'Ongeldige Google OAuth-sessie. Probeer Gmail opnieuw te koppelen.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Authorization code
        |--------------------------------------------------------------------------
        */

        $code =
            $request->query(
                'code'
            );

        if (!$code) {
            return redirect()
                ->route('gmail.inbox')
                ->with(
                    'error',
                    'Google heeft geen geldige authorization code teruggestuurd.'
                );
        }


        try {
            $client =
                $this->makeGoogleClient();

            /*
            |--------------------------------------------------------------------------
            | Code omwisselen voor token
            |--------------------------------------------------------------------------
            */

            $token =
                $client
                    ->fetchAccessTokenWithAuthCode(
                        $code
                    );

            if (
                !is_array($token)
            ) {
                throw new RuntimeException(
                    'Google heeft geen geldig token teruggestuurd.'
                );
            }

            if (
                isset($token['error'])
            ) {
                throw new RuntimeException(
                    $token['error_description']
                    ?? $token['error']
                );
            }

            $token['created'] =
                $token['created']
                ?? time();

            $client->setAccessToken(
                $token
            );


            /*
            |--------------------------------------------------------------------------
            | Werkelijk gekoppelde Gmail-account controleren
            |--------------------------------------------------------------------------
            */

            $gmail =
                new Gmail(
                    $client
                );

            $profile =
                $gmail
                    ->users
                    ->getProfile(
                        'me'
                    );

            $googleEmail =
                strtolower(
                    trim(
                        (string)
                        $profile
                            ->getEmailAddress()
                    )
                );

            $websiteEmail =
                strtolower(
                    trim(
                        (string)
                        $user->email
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Alleen @gmail.com
            |--------------------------------------------------------------------------
            */

            if (
                !Str::endsWith(
                    $googleEmail,
                    '@gmail.com'
                )
            ) {
                try {
                    $client->revokeToken();
                } catch (Throwable $e) {
                    report($e);
                }

                return redirect()
                    ->route('gmail.inbox')
                    ->with(
                        'error',
                        'Je moet een persoonlijk @gmail.com account koppelen.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Exact hetzelfde account
            |--------------------------------------------------------------------------
            |
            | Mashal:
            |
            | gebruiker@gmail.com
            |
            | mag niet:
            |
            | iemandanders@gmail.com
            |
            | koppelen.
            |
            */

            if (
                $googleEmail !==
                $websiteEmail
            ) {
                try {
                    $client->revokeToken();
                } catch (Throwable $e) {
                    report($e);
                }

                return redirect()
                    ->route('gmail.inbox')
                    ->with(
                        'error',
                        'Je moet hetzelfde Gmail-account koppelen waarmee je op Mashal Studio bent ingelogd.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Refresh token
            |--------------------------------------------------------------------------
            |
            | Google levert niet bij iedere nieuwe authorization response opnieuw
            | een refresh token.
            |
            | Daarom behouden we eventueel het bestaande token.
            |
            */

            $refreshToken =
                $token['refresh_token']
                ?? null;

            if (
                !$refreshToken &&
                $user->google_refresh_token
            ) {
                $refreshToken =
                    $this->decryptValue(
                        $user->google_refresh_token
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Refresh token niet dubbel opslaan
            |--------------------------------------------------------------------------
            */

            unset(
                $token['refresh_token']
            );


            /*
            |--------------------------------------------------------------------------
            | Access token versleutelen
            |--------------------------------------------------------------------------
            */

            $encodedToken =
                json_encode(
                    $token,
                    JSON_UNESCAPED_SLASHES
                );

            if (
                $encodedToken === false
            ) {
                throw new RuntimeException(
                    'Google access token kon niet worden opgeslagen.'
                );
            }

            $user->google_access_token =
                Crypt::encryptString(
                    $encodedToken
                );


            /*
            |--------------------------------------------------------------------------
            | Refresh token versleutelen
            |--------------------------------------------------------------------------
            */

            if ($refreshToken) {
                $user->google_refresh_token =
                    Crypt::encryptString(
                        $refreshToken
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Gmail-accountinformatie
            |--------------------------------------------------------------------------
            */

            $user->google_gmail_email =
                $googleEmail;

            $user->google_token_expires_at =
                now()->addSeconds(
                    (int) (
                        $token['expires_in']
                        ?? 3600
                    )
                );

            $user->gmail_connected_at =
                now();

            $user->save();


            return redirect()
                ->route(
                    'gmail.inbox',
                    [
                        'folder' =>
                            'inbox',
                    ]
                )
                ->with(
                    'success',
                    'Je Gmail-account is succesvol gekoppeld.'
                );

        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('gmail.inbox')
                ->with(
                    'error',
                    'Gmail koppelen is mislukt: ' .
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Geautoriseerde Google client
    |--------------------------------------------------------------------------
    |
    | Laadt het opgeslagen token.
    |
    | Wanneer het access token verlopen is gebruiken we automatisch het
    | refresh token.
    |
    */

    private function authorizedClient(
        $user
    ): GoogleClient {
        if (
            !$user->google_access_token
        ) {
            throw new RuntimeException(
                'Je Gmail-account is nog niet gekoppeld.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Access token ontsleutelen
        |--------------------------------------------------------------------------
        */

        $encryptedAccessToken =
            $this->decryptValue(
                $user->google_access_token
            );

        if (
            !$encryptedAccessToken
        ) {
            throw new RuntimeException(
                'Het opgeslagen Gmail access token is ongeldig.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | JSON token
        |--------------------------------------------------------------------------
        */

        $accessToken =
            json_decode(
                $encryptedAccessToken,
                true
            );

        if (
            !is_array($accessToken)
        ) {
            throw new RuntimeException(
                'Het Gmail access token heeft een ongeldig formaat.'
            );
        }


        $client =
            $this->makeGoogleClient();

        $client->setAccessToken(
            $accessToken
        );


        /*
        |--------------------------------------------------------------------------
        | Token vernieuwen
        |--------------------------------------------------------------------------
        */

        if (
            $client->isAccessTokenExpired()
        ) {
            $refreshToken =
                $this->decryptValue(
                    $user->google_refresh_token
                );

            if (
                !$refreshToken
            ) {
                throw new RuntimeException(
                    'Je Gmail-sessie is verlopen. Koppel Gmail opnieuw.'
                );
            }


            $newToken =
                $client
                    ->fetchAccessTokenWithRefreshToken(
                        $refreshToken
                    );

            if (
                !is_array($newToken)
            ) {
                throw new RuntimeException(
                    'Google heeft geen geldig nieuw access token teruggestuurd.'
                );
            }

            if (
                isset(
                    $newToken['error']
                )
            ) {
                throw new RuntimeException(
                    $newToken['error_description']
                    ?? $newToken['error']
                );
            }

            $newToken['created'] =
                $newToken['created']
                ?? time();


            /*
            |--------------------------------------------------------------------------
            | Eventueel nieuw refresh token
            |--------------------------------------------------------------------------
            */

            if (
                !empty(
                    $newToken['refresh_token']
                )
            ) {
                $refreshToken =
                    $newToken['refresh_token'];

                $user->google_refresh_token =
                    Crypt::encryptString(
                        $refreshToken
                    );
            }


            unset(
                $newToken['refresh_token']
            );


            $client->setAccessToken(
                $newToken
            );


            $encodedNewToken =
                json_encode(
                    $newToken,
                    JSON_UNESCAPED_SLASHES
                );

            if (
                $encodedNewToken === false
            ) {
                throw new RuntimeException(
                    'Het vernieuwde Google-token kon niet worden opgeslagen.'
                );
            }


            $user->google_access_token =
                Crypt::encryptString(
                    $encodedNewToken
                );

            $user->google_token_expires_at =
                now()->addSeconds(
                    (int) (
                        $newToken['expires_in']
                        ?? 3600
                    )
                );

            $user->save();
        }


        return $client;
    }


    /*
    |--------------------------------------------------------------------------
    | Mapnaam normaliseren
    |--------------------------------------------------------------------------
    */

    private function normalizeFolder(
        ?string $folder
    ): string {
        $folder =
            strtolower(
                trim(
                    (string)
                    $folder
                )
            );

        $allowed = [
            'inbox',
            'archive',
            'sent',
            'spam',
            'trash',
        ];

        if (
            !in_array(
                $folder,
                $allowed,
                true
            )
        ) {
            return 'inbox';
        }

        return $folder;
    }


    /*
    |--------------------------------------------------------------------------
    | Foldertitel
    |--------------------------------------------------------------------------
    */

    private function folderTitle(
        string $folder
    ): string {
        return match ($folder) {
            'archive' =>
                'Gearchiveerd',

            'sent' =>
                'Verzonden',

            'spam' =>
                'Spam',

            'trash' =>
                'Prullenbak',

            default =>
                'Inbox',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Gmail folderopties
    |--------------------------------------------------------------------------
    |
    | Gmail heeft geen speciaal ARCHIVE systeemlabel.
    |
    | Een gearchiveerde mail is feitelijk een mail zonder INBOX-label.
    |
    */

    private function folderOptions(
        string $folder
    ): array {
        return match ($folder) {
            /*
            |--------------------------------------------------------------------------
            | Verzonden
            |--------------------------------------------------------------------------
            */

            'sent' => [
                'labelIds' => [
                    'SENT',
                ],
                'folderQuery' => '',
                'includeSpamTrash' => false,
            ],


            /*
            |--------------------------------------------------------------------------
            | Spam
            |--------------------------------------------------------------------------
            */

            'spam' => [
                'labelIds' => [
                    'SPAM',
                ],
                'folderQuery' => '',
                'includeSpamTrash' => true,
            ],


            /*
            |--------------------------------------------------------------------------
            | Prullenbak
            |--------------------------------------------------------------------------
            */

            'trash' => [
                'labelIds' => [
                    'TRASH',
                ],
                'folderQuery' => '',
                'includeSpamTrash' => true,
            ],


            /*
            |--------------------------------------------------------------------------
            | Gearchiveerd
            |--------------------------------------------------------------------------
            |
            | Geen:
            |
            | - inbox
            | - sent
            | - spam
            | - trash
            | - drafts
            |
            */

            'archive' => [
                'labelIds' => [],
                'folderQuery' =>
                    '-in:inbox ' .
                    '-in:sent ' .
                    '-in:spam ' .
                    '-in:trash ' .
                    '-in:drafts',

                'includeSpamTrash' => false,
            ],


            /*
            |--------------------------------------------------------------------------
            | Inbox
            |--------------------------------------------------------------------------
            */

            default => [
                'labelIds' => [
                    'INBOX',
                ],
                'folderQuery' => '',
                'includeSpamTrash' => false,
            ],
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Mailbox
    |--------------------------------------------------------------------------
    |
    | GET /mail
    |
    | Ondersteund:
    |
    | /mail?folder=inbox
    | /mail?folder=archive
    | /mail?folder=sent
    | /mail?folder=spam
    | /mail?folder=trash
    |
    */

    public function inbox(
        Request $request
    ) {
        $user =
            $this->requireGmailWebsiteUser(
                $request
            );

        $folder =
            $this->normalizeFolder(
                $request->query(
                    'folder',
                    'inbox'
                )
            );

        $folderTitle =
            $this->folderTitle(
                $folder
            );


        /*
        |--------------------------------------------------------------------------
        | Gmail nog niet gekoppeld
        |--------------------------------------------------------------------------
        */

        if (
            !$user->google_access_token
        ) {
            return view(
                'gmail.inbox',
                [
                    'connected' =>
                        false,

                    'messages' =>
                        [],

                    'nextPageToken' =>
                        null,

                    'query' =>
                        '',

                    'folder' =>
                        $folder,

                    'folderTitle' =>
                        $folderTitle,
                ]
            );
        }


        try {
            $client =
                $this->authorizedClient(
                    $user
                );

            $gmail =
                new Gmail(
                    $client
                );


            /*
            |--------------------------------------------------------------------------
            | Zoekopdracht
            |--------------------------------------------------------------------------
            */

            $query =
                trim(
                    (string)
                    $request->query(
                        'q',
                        ''
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */

            $pageToken =
                $request->query(
                    'pageToken'
                );


            /*
            |--------------------------------------------------------------------------
            | Folderconfiguratie
            |--------------------------------------------------------------------------
            */

            $folderConfig =
                $this->folderOptions(
                    $folder
                );


            /*
            |--------------------------------------------------------------------------
            | Gmail API opties
            |--------------------------------------------------------------------------
            */

            $options = [
                'maxResults' =>
                    20,
            ];


            /*
            |--------------------------------------------------------------------------
            | Labels
            |--------------------------------------------------------------------------
            */

            if (
                !empty(
                    $folderConfig['labelIds']
                )
            ) {
                $options['labelIds'] =
                    $folderConfig['labelIds'];
            }


            /*
            |--------------------------------------------------------------------------
            | Spam / Trash
            |--------------------------------------------------------------------------
            */

            if (
                $folderConfig[
                    'includeSpamTrash'
                ]
            ) {
                $options[
                    'includeSpamTrash'
                ] = true;
            }


            /*
            |--------------------------------------------------------------------------
            | Search + folderquery combineren
            |--------------------------------------------------------------------------
            */

            $combinedQuery =
                trim(
                    (
                        $folderConfig[
                            'folderQuery'
                        ]
                        ?? ''
                    ) .
                    ' ' .
                    $query
                );

            if (
                $combinedQuery !== ''
            ) {
                $options['q'] =
                    $combinedQuery;
            }


            /*
            |--------------------------------------------------------------------------
            | Pagination token
            |--------------------------------------------------------------------------
            */

            if ($pageToken) {
                $options['pageToken'] =
                    $pageToken;
            }


            /*
            |--------------------------------------------------------------------------
            | Berichtenlijst ophalen
            |--------------------------------------------------------------------------
            */

            $list =
                $gmail
                    ->users_messages
                    ->listUsersMessages(
                        'me',
                        $options
                    );


            $messages = [];


            /*
            |--------------------------------------------------------------------------
            | Berichtmetadata
            |--------------------------------------------------------------------------
            */

            foreach (
                $list->getMessages()
                    ?? []
                as $messageReference
            ) {
                $message =
                    $gmail
                        ->users_messages
                        ->get(
                            'me',
                            $messageReference
                                ->getId(),
                            [
                                'format' =>
                                    'metadata',

                                'metadataHeaders' => [
                                    'From',
                                    'To',
                                    'Cc',
                                    'Subject',
                                    'Date',
                                ],
                            ]
                        );


                $headers =
                    $this->headersToArray(
                        $message
                            ->getPayload()
                            ?->getHeaders()
                            ?? []
                    );


                $messages[] = [
                    'id' =>
                        $message->getId(),

                    'thread_id' =>
                        $message->getThreadId(),

                    'from' =>
                        $headers['from']
                        ?? '(Onbekend)',

                    'to' =>
                        $headers['to']
                        ?? '',

                    'cc' =>
                        $headers['cc']
                        ?? '',

                    'subject' =>
                        $headers['subject']
                        ?? '(Geen onderwerp)',

                    'date' =>
                        $headers['date']
                        ?? '',

                    'snippet' =>
                        $message->getSnippet()
                        ?? '',

                    'label_ids' =>
                        $message->getLabelIds()
                        ?? [],
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | View
            |--------------------------------------------------------------------------
            */

            return view(
                'gmail.inbox',
                [
                    'connected' =>
                        true,

                    'messages' =>
                        $messages,

                    'nextPageToken' =>
                        $list
                            ->getNextPageToken(),

                    'query' =>
                        $query,

                    'folder' =>
                        $folder,

                    'folderTitle' =>
                        $folderTitle,
                ]
            );

        } catch (Throwable $e) {
            report($e);

            return view(
                'gmail.inbox',
                [
                    'connected' =>
                        false,

                    'messages' =>
                        [],

                    'nextPageToken' =>
                        null,

                    'query' =>
                        '',

                    'folder' =>
                        $folder,

                    'folderTitle' =>
                        $folderTitle,

                    'gmailError' =>
                        $e->getMessage(),
                ]
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Eén bericht openen
    |--------------------------------------------------------------------------
    |
    | GET /mail/message/{id}
    |
    */

    public function show(
        Request $request,
        string $id
    ) {
        $user =
            $this->requireGmailWebsiteUser(
                $request
            );

        $folder =
            $this->normalizeFolder(
                $request->query(
                    'folder',
                    'inbox'
                )
            );

        try {
            $client =
                $this->authorizedClient(
                    $user
                );

            $gmail =
                new Gmail(
                    $client
                );


            /*
            |--------------------------------------------------------------------------
            | Volledig bericht
            |--------------------------------------------------------------------------
            */

            $message =
                $gmail
                    ->users_messages
                    ->get(
                        'me',
                        $id,
                        [
                            'format' =>
                                'full',
                        ]
                    );


            $payload =
                $message->getPayload();


            $headers =
                $this->headersToArray(
                    $payload
                        ?->getHeaders()
                        ?? []
                );


            /*
            |--------------------------------------------------------------------------
            | Veilig leesbare inhoud
            |--------------------------------------------------------------------------
            |
            | We tonen eerst text/plain.
            |
            | Wanneer een bericht alleen HTML bevat wordt HTML omgezet naar
            | gewone tekst.
            |
            | Daardoor renderen we niet zomaar willekeurige externe HTML,
            | scripts of tracking-content.
            |
            */

            $body =
                $this->extractReadableBody(
                    $payload
                );


            return view(
                'gmail.show',
                [
                    'message' => [
                        'id' =>
                            $message->getId(),

                        'thread_id' =>
                            $message->getThreadId(),

                        'from' =>
                            $headers['from']
                            ?? '(Onbekend)',

                        'to' =>
                            $headers['to']
                            ?? '',

                        'cc' =>
                            $headers['cc']
                            ?? '',

                        'subject' =>
                            $headers['subject']
                            ?? '(Geen onderwerp)',

                        'date' =>
                            $headers['date']
                            ?? '',

                        'body' =>
                            $body,

                        'snippet' =>
                            $message->getSnippet()
                            ?? '',

                        'label_ids' =>
                            $message->getLabelIds()
                            ?? [],
                    ],

                    'folder' =>
                        $folder,

                    'folderTitle' =>
                        $this->folderTitle(
                            $folder
                        ),
                ]
            );

        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route(
                    'gmail.inbox',
                    [
                        'folder' =>
                            $folder,
                    ]
                )
                ->with(
                    'error',
                    'Deze e-mail kon niet worden geopend.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Mail versturen
    |--------------------------------------------------------------------------
    |
    | POST /mail/send
    |
    */

    public function send(
        Request $request
    ) {
        $user =
            $this->requireGmailWebsiteUser(
                $request
            );


        /*
        |--------------------------------------------------------------------------
        | Validatie
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
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
            $client =
                $this->authorizedClient(
                    $user
                );

            $gmail =
                new Gmail(
                    $client
                );


            /*
            |--------------------------------------------------------------------------
            | Header injection voorkomen
            |--------------------------------------------------------------------------
            */

            $to =
                $this->cleanHeaderValue(
                    $validated['to']
                );

            $subject =
                $this->cleanHeaderValue(
                    $validated['subject']
                    ?? ''
                );

            $from =
                strtolower(
                    trim(
                        (string)
                        $user->email
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | MIME / RFC bericht
            |--------------------------------------------------------------------------
            */

            $encodedSubject =
                mb_encode_mimeheader(
                    $subject,
                    'UTF-8',
                    'B',
                    "\r\n"
                );


            $rawMessage =
                'From: <' .
                $from .
                ">\r\n" .

                'To: <' .
                $to .
                ">\r\n" .

                'Subject: ' .
                $encodedSubject .
                "\r\n" .

                "MIME-Version: 1.0\r\n" .

                "Content-Type: text/plain; charset=UTF-8\r\n" .

                "Content-Transfer-Encoding: quoted-printable\r\n" .

                "\r\n" .

                quoted_printable_encode(
                    $validated['body']
                );


            /*
            |--------------------------------------------------------------------------
            | Gmail verwacht base64url
            |--------------------------------------------------------------------------
            */

            $encodedMessage =
                $this->base64UrlEncode(
                    $rawMessage
                );


            $message =
                new Message();

            $message->setRaw(
                $encodedMessage
            );


            /*
            |--------------------------------------------------------------------------
            | Versturen
            |--------------------------------------------------------------------------
            */

            $gmail
                ->users_messages
                ->send(
                    'me',
                    $message
                );


            return redirect()
                ->route(
                    'gmail.inbox',
                    [
                        'folder' =>
                            'sent',
                    ]
                )
                ->with(
                    'success',
                    'E-mail is succesvol verzonden.'
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


    /*
    |--------------------------------------------------------------------------
    | Gmail ontkoppelen
    |--------------------------------------------------------------------------
    |
    | POST /gmail/disconnect
    |
    */

    public function disconnect(
        Request $request
    ) {
        $user =
            $this->requireGmailWebsiteUser(
                $request
            );


        /*
        |--------------------------------------------------------------------------
        | Google token intrekken
        |--------------------------------------------------------------------------
        */

        try {
            if (
                $user->google_access_token
            ) {
                $client =
                    $this->authorizedClient(
                        $user
                    );

                try {
                    $client->revokeToken();
                } catch (Throwable $e) {
                    report($e);
                }
            }
        } catch (Throwable $e) {
            /*
            |--------------------------------------------------------------------------
            | Lokale gegevens worden ook verwijderd wanneer Google revoke faalt
            |--------------------------------------------------------------------------
            */

            report($e);
        }


        /*
        |--------------------------------------------------------------------------
        | Lokale Gmail-gegevens verwijderen
        |--------------------------------------------------------------------------
        */

        $user->google_access_token =
            null;

        $user->google_refresh_token =
            null;

        $user->google_token_expires_at =
            null;

        $user->google_gmail_email =
            null;

        $user->gmail_connected_at =
            null;

        $user->save();


        return redirect()
            ->route('gmail.inbox')
            ->with(
                'success',
                'Gmail is ontkoppeld van Mashal Studio.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Header value opschonen
    |--------------------------------------------------------------------------
    |
    | Voorkomt newline/header injection.
    |
    */

    private function cleanHeaderValue(
        ?string $value
    ): string {
        return trim(
            str_replace(
                [
                    "\r",
                    "\n",
                ],
                '',
                (string) $value
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Gmail headers naar array
    |--------------------------------------------------------------------------
    */

    private function headersToArray(
        iterable $headers
    ): array {
        $result = [];

        foreach (
            $headers
            as $header
        ) {
            $name =
                strtolower(
                    trim(
                        (string)
                        $header->getName()
                    )
                );

            if (
                $name === ''
            ) {
                continue;
            }

            $result[$name] =
                (string)
                $header->getValue();
        }

        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | Leesbare mailbody
    |--------------------------------------------------------------------------
    */

    private function extractReadableBody(
        $payload
    ): string {
        if (!$payload) {
            return '';
        }


        /*
        |--------------------------------------------------------------------------
        | Text/plain
        |--------------------------------------------------------------------------
        */

        $plain =
            $this->findMimePart(
                $payload,
                'text/plain'
            );

        if (
            $plain !== null &&
            trim($plain) !== ''
        ) {
            return trim(
                $plain
            );
        }


        /*
        |--------------------------------------------------------------------------
        | HTML fallback
        |--------------------------------------------------------------------------
        */

        $html =
            $this->findMimePart(
                $payload,
                'text/html'
            );

        if (
            $html !== null &&
            trim($html) !== ''
        ) {
            /*
            |--------------------------------------------------------------------------
            | Visuele HTML-regels behouden
            |--------------------------------------------------------------------------
            */

            $html =
                preg_replace(
                    '/<\s*br\s*\/?>/i',
                    "\n",
                    $html
                );

            $html =
                preg_replace(
                    '/<\/p\s*>/i',
                    "\n\n",
                    $html
                );

            $html =
                preg_replace(
                    '/<\/div\s*>/i',
                    "\n",
                    $html
                );

            $html =
                preg_replace(
                    '/<\/li\s*>/i',
                    "\n",
                    $html
                );


            /*
            |--------------------------------------------------------------------------
            | HTML verwijderen
            |--------------------------------------------------------------------------
            */

            $text =
                strip_tags(
                    $html
                );


            /*
            |--------------------------------------------------------------------------
            | Entities decoderen
            |--------------------------------------------------------------------------
            */

            $text =
                html_entity_decode(
                    $text,
                    ENT_QUOTES |
                    ENT_HTML5,
                    'UTF-8'
                );


            /*
            |--------------------------------------------------------------------------
            | Te veel lege regels beperken
            |--------------------------------------------------------------------------
            */

            $text =
                preg_replace(
                    "/\n{3,}/",
                    "\n\n",
                    $text
                );


            return trim(
                $text
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Simpel direct body-object
        |--------------------------------------------------------------------------
        */

        $data =
            $payload
                ->getBody()
                ?->getData();

        if ($data) {
            return trim(
                $this->base64UrlDecode(
                    $data
                )
            );
        }


        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | MIME part zoeken
    |--------------------------------------------------------------------------
    |
    | Gmail-berichten kunnen uit meerdere geneste multipart-secties bestaan.
    |
    | Daarom zoeken we recursief.
    |
    */

    private function findMimePart(
        $part,
        string $wantedMimeType
    ): ?string {
        if (!$part) {
            return null;
        }


        $mimeType =
            strtolower(
                trim(
                    (string)
                    $part->getMimeType()
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Gewenste MIME gevonden
        |--------------------------------------------------------------------------
        */

        if (
            $mimeType ===
            strtolower(
                $wantedMimeType
            )
        ) {
            $data =
                $part
                    ->getBody()
                    ?->getData();

            if ($data) {
                return $this
                    ->base64UrlDecode(
                        $data
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Child parts
        |--------------------------------------------------------------------------
        */

        foreach (
            $part->getParts()
                ?? []
            as $childPart
        ) {
            $found =
                $this->findMimePart(
                    $childPart,
                    $wantedMimeType
                );

            if (
                $found !== null
            ) {
                return $found;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Base64 URL encode
    |--------------------------------------------------------------------------
    */

    private function base64UrlEncode(
        string $value
    ): string {
        return rtrim(
            strtr(
                base64_encode(
                    $value
                ),
                '+/',
                '-_'
            ),
            '='
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Base64 URL decode
    |--------------------------------------------------------------------------
    */

    private function base64UrlDecode(
        string $value
    ): string {
        $value =
            strtr(
                $value,
                '-_',
                '+/'
            );


        /*
        |--------------------------------------------------------------------------
        | Padding herstellen
        |--------------------------------------------------------------------------
        */

        $remainder =
            strlen(
                $value
            ) % 4;

        if ($remainder) {
            $value .=
                str_repeat(
                    '=',
                    4 - $remainder
                );
        }


        $decoded =
            base64_decode(
                $value,
                true
            );


        return $decoded !== false
            ? $decoded
            : '';
    }


    /*
    |--------------------------------------------------------------------------
    | Versleutelde databasewaarde uitlezen
    |--------------------------------------------------------------------------
    */

    private function decryptValue(
        ?string $value
    ): ?string {
        if (!$value) {
            return null;
        }

        try {
            return Crypt::decryptString(
                $value
            );
        } catch (
            DecryptException $e
        ) {
            return null;
        }
    }
}