<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class XAuthController extends Controller
{
    /**
     * Start de normale X-login.
     */
    public function redirect(Request $request): RedirectResponse
    {
        abort_if(
            Auth::check(),
            403,
            'Gebruik de knop X-account koppelen.'
        );

        return $this->start($request, null);
    }

    /**
     * Start het koppelen van een X-account
     * aan een bestaande ingelogde gebruiker.
     */
    public function link(Request $request): RedirectResponse
    {
        abort_unless(Auth::check(), 403);

        return $this->start(
            $request,
            (string) Auth::id()
        );
    }

    /**
     * Start OAuth 2.0 Authorization Code Flow met PKCE.
     */
    private function start(
        Request $request,
        ?string $userId
    ): RedirectResponse {
        foreach (
            ['client_id', 'client_secret', 'redirect'] as $key
        ) {
            abort_unless(
                filled(config("services.x.$key")),
                503,
                'X-login is nog niet ingesteld.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | OAuth state
        |--------------------------------------------------------------------------
        */

        $state = bin2hex(
            random_bytes(32)
        );

        /*
        |--------------------------------------------------------------------------
        | PKCE verifier
        |--------------------------------------------------------------------------
        */

        $verifier = rtrim(
            strtr(
                base64_encode(
                    random_bytes(64)
                ),
                '+/',
                '-_'
            ),
            '='
        );

        /*
        |--------------------------------------------------------------------------
        | PKCE challenge
        |--------------------------------------------------------------------------
        */

        $challenge = rtrim(
            strtr(
                base64_encode(
                    hash(
                        'sha256',
                        $verifier,
                        true
                    )
                ),
                '+/',
                '-_'
            ),
            '='
        );

        /*
        |--------------------------------------------------------------------------
        | OAuth-flow tijdelijk in sessie bewaren
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'x_oauth',
            [
                'state' => $state,
                'verifier' => $verifier,
                'user_id' => $userId,
                'started_at' => time(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Authorization URL
        |--------------------------------------------------------------------------
        */

        $url = 'https://x.com/i/oauth2/authorize?'
            . http_build_query(
                [
                    'response_type' => 'code',

                    'client_id' => config(
                        'services.x.client_id'
                    ),

                    'redirect_uri' => config(
                        'services.x.redirect'
                    ),

                    'scope' => 'tweet.read users.read',

                    'state' => $state,

                    'code_challenge' => $challenge,

                    'code_challenge_method' => 'S256',
                ],
                '',
                '&',
                PHP_QUERY_RFC3986
            );

        return redirect()->away($url);
    }

    /**
     * Verwerk de OAuth callback van X.
     */
    public function callback(
        Request $request
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | OAuth-flow ophalen en direct verwijderen
        |--------------------------------------------------------------------------
        */

        $flow = $request
            ->session()
            ->pull('x_oauth');

        $state = $request->query('state');

        /*
        |--------------------------------------------------------------------------
        | Flow controleren
        |--------------------------------------------------------------------------
        */

        if (
            ! is_array($flow)
            || ! is_string($state)
            || $state === ''
            || ! is_string($flow['state'] ?? null)
            || ! hash_equals(
                $flow['state'],
                $state
            )
            || ! isset($flow['started_at'])
            || ! is_numeric($flow['started_at'])
            || time() - (int) $flow['started_at'] > 600
        ) {
            return $this->failure(
                'Deze X-aanvraag is verlopen of ongeldig. Probeer opnieuw.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PKCE verifier controleren
        |--------------------------------------------------------------------------
        */

        $verifier = $flow['verifier'] ?? null;

        if (
            ! is_string($verifier)
            || $verifier === ''
        ) {
            return $this->failure(
                'De X-login kon niet veilig worden voltooid. Probeer opnieuw.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Login of account-koppeling?
        |--------------------------------------------------------------------------
        */

        $linkUserId = $flow['user_id'] ?? null;

        if ($linkUserId !== null) {
            abort_unless(
                Auth::check()
                && (string) Auth::id() === (string) $linkUserId,
                403,
                'Je sessie is gewijzigd. Start het koppelen opnieuw.'
            );
        } else {
            abort_if(
                Auth::check(),
                403,
                'Je bent inmiddels al ingelogd.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | X heeft de login geannuleerd of geweigerd
        |--------------------------------------------------------------------------
        */

        if ($request->has('error')) {
            Log::notice(
                'X OAuth authorization error.',
                [
                    'error' => $request->query('error'),
                    'description' => $request->query(
                        'error_description'
                    ),
                ]
            );

            return $this->failure(
                'Inloggen met X is geannuleerd.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Authorization code controleren
        |--------------------------------------------------------------------------
        */

        $code = $request->query('code');

        if (
            ! is_string($code)
            || $code === ''
        ) {
            return $this->failure(
                'X heeft geen geldige autorisatiecode teruggestuurd.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Access token en profiel ophalen
        |--------------------------------------------------------------------------
        */

        try {
            $tokenResponse = Http::asForm()
                ->acceptJson()
                ->withBasicAuth(
                    (string) config(
                        'services.x.client_id'
                    ),
                    (string) config(
                        'services.x.client_secret'
                    )
                )
                ->connectTimeout(10)
                ->timeout(20)
                ->post(
                    'https://api.x.com/2/oauth2/token',
                    [
                        'grant_type' => 'authorization_code',

                        'code' => $code,

                        'redirect_uri' => config(
                            'services.x.redirect'
                        ),

                        'code_verifier' => $verifier,
                    ]
                );

            /*
            |--------------------------------------------------------------------------
            | Token-response controleren
            |--------------------------------------------------------------------------
            */

            if (! $tokenResponse->successful()) {
                Log::warning(
                    'X OAuth token request failed.',
                    [
                        'status' => $tokenResponse->status(),
                        'body' => $tokenResponse->json(),
                    ]
                );

                return $this->failure(
                    'X kon de login niet bevestigen. Probeer opnieuw.'
                );
            }

            $token = $tokenResponse->json(
                'access_token'
            );

            if (
                ! is_string($token)
                || $token === ''
            ) {
                Log::warning(
                    'X OAuth response contained no access token.'
                );

                return $this->failure(
                    'X heeft geen geldig toegangstoken teruggestuurd.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | X-gebruikersprofiel ophalen
            |--------------------------------------------------------------------------
            */

            $profileResponse = Http::withToken(
                $token
            )
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout(20)
                ->get(
                    'https://api.x.com/2/users/me'
                );

            if (! $profileResponse->successful()) {
                Log::warning(
                    'X profile request failed.',
                    [
                        'status' => $profileResponse->status(),
                        'body' => $profileResponse->json(),
                    ]
                );

                return $this->failure(
                    'Je X-profiel kon niet worden opgehaald.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | X User ID ophalen
            |--------------------------------------------------------------------------
            */

            $xId = $profileResponse->json(
                'data.id'
            );

            if (
                ! is_string($xId)
                || ! preg_match(
                    '/^[0-9]{1,32}$/D',
                    $xId
                )
            ) {
                Log::warning(
                    'X returned an invalid user ID.',
                    [
                        'x_id' => $xId,
                    ]
                );

                return $this->failure(
                    'X heeft geen geldig gebruikersprofiel teruggestuurd.'
                );
            }
        } catch (ConnectionException $exception) {
            Log::warning(
                'Connection to X failed.',
                [
                    'message' => $exception->getMessage(),
                ]
            );

            return $this->failure(
                'X is momenteel niet bereikbaar. Probeer later opnieuw.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->failure(
                'Er ging iets mis tijdens de X-login. Probeer opnieuw.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | X-account koppelen aan bestaande gebruiker
        |--------------------------------------------------------------------------
        */

        if ($linkUserId !== null) {
            try {
                $linked = DB::transaction(
                    function () use (
                        $linkUserId,
                        $xId
                    ): bool {
                        $user = User::query()
                            ->lockForUpdate()
                            ->findOrFail(
                                $linkUserId
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | Gebruiker heeft al ander X-account
                        |--------------------------------------------------------------------------
                        */

                        if (
                            filled($user->x_id)
                            && (string) $user->x_id !== $xId
                        ) {
                            return false;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | X-account is al gekoppeld aan andere gebruiker
                        |--------------------------------------------------------------------------
                        */

                        $alreadyLinked = User::query()
                            ->where(
                                'x_id',
                                $xId
                            )
                            ->whereKeyNot(
                                $user->getKey()
                            )
                            ->exists();

                        if ($alreadyLinked) {
                            return false;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Account koppelen
                        |--------------------------------------------------------------------------
                        */

                        $user->forceFill(
                            [
                                'x_id' => $xId,
                            ]
                        )->save();

                        return true;
                    }
                );
            } catch (QueryException $exception) {
                Log::warning(
                    'X account link query failed.',
                    [
                        'user_id' => $linkUserId,
                        'x_id' => $xId,
                        'message' => $exception->getMessage(),
                    ]
                );

                return $this->failure(
                    'Het X-account kon niet worden gekoppeld.'
                );
            } catch (Throwable $exception) {
                report($exception);

                return $this->failure(
                    'Het X-account kon niet worden gekoppeld.'
                );
            }

            if (! $linked) {
                return $this->failure(
                    'Dit Mashal-account heeft al een andere X-koppeling of dit X-account is al aan een ander account gekoppeld.'
                );
            }

            $request
                ->session()
                ->regenerate();

            return redirect('/')
                ->with(
                    'status',
                    'Je X-account is gekoppeld. Je kunt voortaan inloggen met X.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Normale login met X
        |--------------------------------------------------------------------------
        */

        $user = User::query()
            ->where(
                'x_id',
                $xId
            )
            ->first();

        if (! $user) {
            return $this->failure(
                'Dit X-account is nog niet gekoppeld. Log eerst in met je bestaande Mashal-account en koppel daarna je X-account.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Laatste loginprovider opslaan
        |--------------------------------------------------------------------------
        */

        try {
            $user->forceFill(
                [
                    'login_provider' => 'x',
                ]
            )->save();
        } catch (Throwable $exception) {
            report($exception);

            return $this->failure(
                'Je account kon niet worden bijgewerkt. Probeer opnieuw.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Inloggen
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user
        );

        /*
        |--------------------------------------------------------------------------
        | Nieuwe sessie-ID tegen session fixation
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();

        return redirect('/')
            ->with(
                'status',
                'Je bent succesvol ingelogd met X.'
            );
    }

    /**
     * Centrale foutafhandeling.
     */
    private function failure(
        string $message
    ): RedirectResponse {
        return redirect(
            Auth::check()
                ? '/'
                : route('login')
        )->withErrors(
            [
                'x' => $message,
            ]
        );
    }
}