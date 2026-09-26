<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class XAuthController extends Controller
{
    /**
     * Hoe lang een OAuth- of registratieflow geldig blijft.
     */
    private const FLOW_TTL_SECONDS = 900;

    /**
     * Start normale X-login.
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
     * Start het koppelen van X aan de ingelogde gebruiker.
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
        foreach (['client_id', 'client_secret', 'redirect'] as $key) {
            abort_unless(
                filled(config("services.x.$key")),
                503,
                'X-login is nog niet ingesteld.'
            );
        }

        $state = bin2hex(random_bytes(32));

        $verifier = rtrim(
            strtr(
                base64_encode(random_bytes(64)),
                '+/',
                '-_'
            ),
            '='
        );

        $challenge = rtrim(
            strtr(
                base64_encode(
                    hash('sha256', $verifier, true)
                ),
                '+/',
                '-_'
            ),
            '='
        );

        $request->session()->put('x_oauth', [
            'state' => $state,
            'verifier' => $verifier,
            'user_id' => $userId,
            'started_at' => time(),
        ]);

        $url = 'https://x.com/i/oauth2/authorize?'
            . http_build_query(
                [
                    'response_type' => 'code',
                    'client_id' => config('services.x.client_id'),
                    'redirect_uri' => config('services.x.redirect'),
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
    public function callback(Request $request): RedirectResponse
    {
        $flow = $request->session()->pull('x_oauth');
        $state = $request->query('state');

        if (! $this->validOAuthFlow($flow, $state)) {
            return $this->failure(
                'Deze X-aanvraag is verlopen of ongeldig. Probeer opnieuw.'
            );
        }

        $verifier = $flow['verifier'];
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

        if ($request->has('error')) {
            Log::notice('X OAuth authorization error.', [
                'error' => $request->query('error'),
                'description' => $request->query('error_description'),
            ]);

            return $this->failure(
                'Inloggen met X is geannuleerd.'
            );
        }

        $code = $request->query('code');

        if (! is_string($code) || $code === '') {
            return $this->failure(
                'X heeft geen geldige autorisatiecode teruggestuurd.'
            );
        }

        try {
            $tokenResponse = Http::asForm()
                ->acceptJson()
                ->withBasicAuth(
                    (string) config('services.x.client_id'),
                    (string) config('services.x.client_secret')
                )
                ->connectTimeout(10)
                ->timeout(20)
                ->post(
                    'https://api.x.com/2/oauth2/token',
                    [
                        'grant_type' => 'authorization_code',
                        'code' => $code,
                        'redirect_uri' => config('services.x.redirect'),
                        'code_verifier' => $verifier,
                    ]
                );

            if (! $tokenResponse->successful()) {
                Log::warning('X OAuth token request failed.', [
                    'status' => $tokenResponse->status(),
                    'error' => $tokenResponse->json('error'),
                    'error_description' => $tokenResponse->json('error_description'),
                ]);

                return $this->failure(
                    'X kon de login niet bevestigen. Probeer opnieuw.'
                );
            }

            $token = $tokenResponse->json('access_token');

            if (! is_string($token) || $token === '') {
                Log::warning(
                    'X OAuth response contained no access token.'
                );

                return $this->failure(
                    'X heeft geen geldig toegangstoken teruggestuurd.'
                );
            }

            $profileResponse = Http::withToken($token)
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout(20)
                ->get(
                    'https://api.x.com/2/users/me',
                    [
                        'user.fields' => 'id,name,username',
                    ]
                );

            if (! $profileResponse->successful()) {
                Log::warning('X profile request failed.', [
                    'status' => $profileResponse->status(),
                    'error' => $profileResponse->json('errors.0.title'),
                ]);

                return $this->failure(
                    'Je X-profiel kon niet worden opgehaald.'
                );
            }

            $xId = $profileResponse->json('data.id');
            $xName = $profileResponse->json('data.name');
            $xUsername = $profileResponse->json('data.username');

            if (
                ! is_string($xId)
                || ! preg_match('/^[0-9]{1,32}$/D', $xId)
            ) {
                Log::warning('X returned an invalid user ID.');

                return $this->failure(
                    'X heeft geen geldig gebruikersprofiel teruggestuurd.'
                );
            }

            $xName = is_string($xName)
                ? trim($xName)
                : '';

            $xUsername = is_string($xUsername)
                ? trim($xUsername)
                : '';
        } catch (ConnectionException $exception) {
            Log::warning('Connection to X failed.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->failure(
                'X is momenteel niet bereikbaar. Probeer later opnieuw.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->failure(
                'Er ging iets mis tijdens de X-login. Probeer opnieuw.'
            );
        }

        /**
         * Koppel X aan een bestaand ingelogd account.
         */
        if ($linkUserId !== null) {
            return $this->finishLinking(
                $request,
                (string) $linkUserId,
                $xId
            );
        }

        /**
         * Normale login: bestaand gekoppeld X-account.
         */
        $user = User::query()
            ->where('x_id', $xId)
            ->first();

        if ($user) {
            return $this->loginUser(
                $request,
                $user
            );
        }

        /**
         * Eerste keer met X:
         * bewaar alleen noodzakelijke profieldata in de sessie
         * en laat de gebruiker zijn Mashal-account afmaken.
         */
        $request->session()->put('x_registration', [
            'x_id' => $xId,
            'name' => $xName,
            'username' => $xUsername,
            'started_at' => time(),
        ]);

        return redirect()
            ->route('x.registration')
            ->with(
                'status',
                'X is bevestigd. Vul je e-mailadres en wachtwoord in om je Mashal-account af te maken.'
            );
    }

    /**
     * Toon de pagina waarmee een nieuwe X-gebruiker
     * zijn Mashal-account afmaakt.
     */
    public function registration(Request $request): View|RedirectResponse
    {
        abort_if(Auth::check(), 403);

        $pending = $request->session()->get('x_registration');

        if (! $this->validRegistrationFlow($pending)) {
            $request->session()->forget('x_registration');

            return redirect()
                ->route('login')
                ->withErrors([
                    'x' => 'Je X-registratie is verlopen. Start opnieuw met X.',
                ]);
        }

        return view('site.register', [
            'xProfile' => [
                'name' => $pending['name'] ?? '',
                'username' => $pending['username'] ?? '',
            ],
        ]);
    }

    /**
     * Maak een nieuw Mashal-account aan na succesvolle X OAuth.
     */
    public function completeRegistration(
        Request $request
    ): RedirectResponse {
        abort_if(Auth::check(), 403);

        $pending = $request->session()->get('x_registration');

        if (! $this->validRegistrationFlow($pending)) {
            $request->session()->forget('x_registration');

            return redirect()
                ->route('login')
                ->withErrors([
                    'x' => 'Je X-registratie is verlopen. Start opnieuw met X.',
                ]);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $xId = (string) $pending['x_id'];

        try {
            $user = DB::transaction(
                function () use (
                    $validated,
                    $xId
                ): User {
                    $existingXUser = User::query()
                        ->where('x_id', $xId)
                        ->lockForUpdate()
                        ->first();

                    if ($existingXUser) {
                        throw new \RuntimeException(
                            'x_account_already_linked'
                        );
                    }

                    return User::query()->create([
                        'name' => trim($validated['name']),
                        'email' => mb_strtolower(
                            trim($validated['email'])
                        ),
                        'password' => Hash::make(
                            $validated['password']
                        ),
                        'x_id' => $xId,
                        'login_provider' => 'x',
                    ]);
                }
            );
        } catch (\RuntimeException $exception) {
            if ($exception->getMessage() === 'x_account_already_linked') {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'x' => 'Dit X-account is inmiddels al gekoppeld. Probeer opnieuw in te loggen met X.',
                    ]);
            }

            report($exception);

            return back()
                ->withInput(
                    $request->except(
                        'password',
                        'password_confirmation'
                    )
                )
                ->withErrors([
                    'x' => 'Je account kon niet worden aangemaakt. Probeer opnieuw.',
                ]);
        } catch (QueryException $exception) {
            Log::warning('X registration query failed.', [
                'x_id' => $xId,
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'x' => 'Dit X-account is inmiddels al gekoppeld of het e-mailadres is al in gebruik.',
                ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput(
                    $request->except(
                        'password',
                        'password_confirmation'
                    )
                )
                ->withErrors([
                    'x' => 'Je account kon niet worden aangemaakt. Probeer opnieuw.',
                ]);
        }

        $request->session()->forget('x_registration');

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect('/')
            ->with(
                'status',
                'Je Mashal-account is aangemaakt en gekoppeld aan X.'
            );
    }

    /**
     * Rond een X-koppeling af.
     */
    private function finishLinking(
        Request $request,
        string $linkUserId,
        string $xId
    ): RedirectResponse {
        try {
            $linked = DB::transaction(
                function () use (
                    $linkUserId,
                    $xId
                ): bool {
                    $user = User::query()
                        ->lockForUpdate()
                        ->findOrFail($linkUserId);

                    if (
                        filled($user->x_id)
                        && (string) $user->x_id !== $xId
                    ) {
                        return false;
                    }

                    $alreadyLinked = User::query()
                        ->where('x_id', $xId)
                        ->whereKeyNot($user->getKey())
                        ->exists();

                    if ($alreadyLinked) {
                        return false;
                    }

                    $user->forceFill([
                        'x_id' => $xId,
                    ])->save();

                    return true;
                }
            );
        } catch (QueryException $exception) {
            Log::warning('X account link query failed.', [
                'user_id' => $linkUserId,
                'x_id' => $xId,
                'message' => $exception->getMessage(),
            ]);

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

        $request->session()->regenerate();

        return redirect('/')
            ->with(
                'status',
                'Je X-account is gekoppeld. Je kunt voortaan inloggen met X.'
            );
    }

    /**
     * Log een bestaande gebruiker in via X.
     */
    private function loginUser(
        Request $request,
        User $user
    ): RedirectResponse {
        try {
            $user->forceFill([
                'login_provider' => 'x',
            ])->save();
        } catch (Throwable $exception) {
            report($exception);

            return $this->failure(
                'Je account kon niet worden bijgewerkt. Probeer opnieuw.'
            );
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect('/')
            ->with(
                'status',
                'Je bent succesvol ingelogd met X.'
            );
    }

    /**
     * Controleer OAuth state, verifier en TTL.
     */
    private function validOAuthFlow(
        mixed $flow,
        mixed $state
    ): bool {
        return is_array($flow)
            && is_string($state)
            && $state !== ''
            && is_string($flow['state'] ?? null)
            && hash_equals($flow['state'], $state)
            && is_string($flow['verifier'] ?? null)
            && $flow['verifier'] !== ''
            && isset($flow['started_at'])
            && is_numeric($flow['started_at'])
            && time() - (int) $flow['started_at']
                <= self::FLOW_TTL_SECONDS;
    }

    /**
     * Controleer tijdelijke X-registratie.
     */
    private function validRegistrationFlow(
        mixed $pending
    ): bool {
        return is_array($pending)
            && is_string($pending['x_id'] ?? null)
            && preg_match(
                '/^[0-9]{1,32}$/D',
                $pending['x_id']
            ) === 1
            && isset($pending['started_at'])
            && is_numeric($pending['started_at'])
            && time() - (int) $pending['started_at']
                <= self::FLOW_TTL_SECONDS;
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
        )->withErrors([
            'x' => $message,
        ]);
    }
}
