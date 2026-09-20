<?php

namespace App\Http\Controllers;

use App\Models\EmailLoginCode;
use App\Models\EmailLoginLink;
use App\Models\User;
use App\Services\BrevoMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class EmailLoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Instellingen
    |--------------------------------------------------------------------------
    */

    private const CODE_EXPIRES_MINUTES = 5;

    private const MAX_CODE_ATTEMPTS = 5;

    private const MAX_SEND_ATTEMPTS = 5;

    private const MAX_VERIFY_ATTEMPTS = 10;

    private const MAGIC_LINK_EXPIRES_MINUTES = 10;

    private const MAX_MAGIC_LINK_SEND_ATTEMPTS = 5;

    private const MAX_MAGIC_LINK_VERIFY_ATTEMPTS = 20;


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly BrevoMailService $brevoMail
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Login-code versturen
    |--------------------------------------------------------------------------
    */

    public function sendCode(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
        ]);

        $email = $this->normalizeEmail(
            $validated['email']
        );

        $rateLimitKey = $this->sendRateLimitKey(
            $request,
            $email
        );

        if (
            RateLimiter::tooManyAttempts(
                $rateLimitKey,
                self::MAX_SEND_ATTEMPTS
            )
        ) {
            $seconds = RateLimiter::availableIn(
                $rateLimitKey
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Je hebt te vaak een inlogcode aangevraagd. Probeer het over ' .
                    max(1, $seconds) .
                    ' seconden opnieuw.'
                );
        }

        RateLimiter::hit(
            $rateLimitKey,
            60
        );

        /*
        |--------------------------------------------------------------------------
        | Oude ongebruikte codes verwijderen
        |--------------------------------------------------------------------------
        */

        EmailLoginCode::query()
            ->where(
                'email',
                $email
            )
            ->whereNull(
                'used_at'
            )
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Nieuwe code genereren en veilig opslaan
        |--------------------------------------------------------------------------
        */

        $code = (string) random_int(
            100000,
            999999
        );

        $loginCode = EmailLoginCode::create([
            'email' => $email,

            'code_hash' => Hash::make(
                $code
            ),

            'expires_at' => now()->addMinutes(
                self::CODE_EXPIRES_MINUTES
            ),

            'attempts' => 0,

            'used_at' => null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Code via Brevo versturen
        |--------------------------------------------------------------------------
        */

        try {
            $this->brevoMail->send(
                $email,
                '',
                'Je inlogcode voor Mashal Studio',
                'emails.login-code',
                [
                    'code' => $code,

                    'email' => $email,

                    'expiresInMinutes' =>
                        self::CODE_EXPIRES_MINUTES,
                ]
            );
        } catch (Throwable $exception) {
            try {
                $loginCode->delete();
            } catch (Throwable $deleteException) {
                report(
                    $deleteException
                );
            }

            report(
                $exception
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'De inlogcode kon niet worden verzonden. Probeer het later opnieuw.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | E-mailadres tijdelijk bewaren
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'email_login_email',
            $email
        );

        $this->ensurePendingImageIntendedUrl(
            $request
        );

        return redirect()
            ->route(
                'email-login.form'
            )
            ->with(
                'success',
                'We hebben een 6-cijferige inlogcode naar je e-mailadres gestuurd. De code is ' .
                self::CODE_EXPIRES_MINUTES .
                ' minuten geldig.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Verificatieformulier tonen
    |--------------------------------------------------------------------------
    */

    public function showVerifyForm(
        Request $request
    ): View {
        $email = $this->normalizeEmail(
            $request
                ->session()
                ->get(
                    'email_login_email',
                    ''
                )
        );

        return view(
            'auth.email-login',
            [
                'email' =>
                    $email,

                'expiresInMinutes' =>
                    self::CODE_EXPIRES_MINUTES,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Login-code controleren
    |--------------------------------------------------------------------------
    */

    public function verifyCode(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],

            'code' => [
                'required',
                'digits:6',
            ],
        ]);

        $email = $this->normalizeEmail(
            $validated['email']
        );

        $code = trim(
            (string) $validated['code']
        );

        /*
        |--------------------------------------------------------------------------
        | Rate limiting
        |--------------------------------------------------------------------------
        */

        $rateLimitKey = $this->verifyRateLimitKey(
            $request,
            $email
        );

        if (
            RateLimiter::tooManyAttempts(
                $rateLimitKey,
                self::MAX_VERIFY_ATTEMPTS
            )
        ) {
            return back()
                ->withInput([
                    'email' => $email,
                ])
                ->with(
                    'error',
                    'Te veel verificatiepogingen. Vraag een nieuwe inlogcode aan.'
                );
        }

        RateLimiter::hit(
            $rateLimitKey,
            300
        );

        /*
        |--------------------------------------------------------------------------
        | Laatste actieve code zoeken
        |--------------------------------------------------------------------------
        */

        $loginCode = EmailLoginCode::query()
            ->where(
                'email',
                $email
            )
            ->whereNull(
                'used_at'
            )
            ->latest(
                'id'
            )
            ->first();

        if (! $loginCode) {
            return back()
                ->withInput([
                    'email' => $email,
                ])
                ->with(
                    'error',
                    'Er is geen actieve inlogcode gevonden. Vraag een nieuwe code aan.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Vervaldatum
        |--------------------------------------------------------------------------
        */

        if (
            ! $loginCode->expires_at ||
            $loginCode->expires_at->isPast()
        ) {
            $loginCode->delete();

            return back()
                ->withInput([
                    'email' => $email,
                ])
                ->with(
                    'error',
                    'Deze inlogcode is verlopen. Vraag een nieuwe code aan.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Pogingen
        |--------------------------------------------------------------------------
        */

        if (
            (int) $loginCode->attempts >=
            self::MAX_CODE_ATTEMPTS
        ) {
            return back()
                ->withInput([
                    'email' => $email,
                ])
                ->with(
                    'error',
                    'Deze inlogcode is geblokkeerd vanwege te veel verkeerde pogingen. Vraag een nieuwe code aan.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Code controleren
        |--------------------------------------------------------------------------
        */

        if (
            ! Hash::check(
                $code,
                (string) $loginCode->code_hash
            )
        ) {
            $loginCode->registerFailedAttempt();

            $loginCode->refresh();

            $remainingAttempts = max(
                0,
                self::MAX_CODE_ATTEMPTS -
                (int) $loginCode->attempts
            );

            if ($remainingAttempts <= 0) {
                return back()
                    ->withInput([
                        'email' => $email,
                    ])
                    ->with(
                        'error',
                        'De code is niet correct. Deze inlogcode is nu geblokkeerd. Vraag een nieuwe code aan.'
                    );
            }

            return back()
                ->withInput([
                    'email' => $email,
                ])
                ->with(
                    'error',
                    'De ingevoerde code is niet correct. Je hebt nog ' .
                    $remainingAttempts .
                    ' poging' .
                    ($remainingAttempts === 1 ? '' : 'en') .
                    ' over.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Code meteen als gebruikt markeren
        |--------------------------------------------------------------------------
        */

        $loginCode->markAsUsed();

        /*
        |--------------------------------------------------------------------------
        | Gebruiker zoeken of aanmaken
        |--------------------------------------------------------------------------
        */

        $user = User::query()
            ->where(
                'email',
                $email
            )
            ->first();

        if (! $user) {
            $user = $this->createUser(
                $email,
                'email_code'
            );
        } else {
            $changes = [
                'login_provider' =>
                    'email_code',
            ];

            if (! $user->email_verified_at) {
                $changes['email_verified_at'] =
                    now();
            }

            $user->forceFill(
                $changes
            )->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Login-provider expliciet op huidige request zetten
        |--------------------------------------------------------------------------
        |
        | De LoginSecurityService kan dit gebruiken wanneer het Login-event
        | wordt afgevuurd.
        |
        */

        $request->merge([
            'login_provider' =>
                'email_code',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Gebruiker inloggen
        |--------------------------------------------------------------------------
        |
        | Auth::login vuurt Laravel's Login-event af. De security-listener
        | leest op dat moment de browser-context die door de verificatiepagina
        | in de sessie is opgeslagen.
        |
        */

        Auth::login(
            $user,
            true
        );

        /*
        |--------------------------------------------------------------------------
        | Sessiebeveiliging
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();

        $request
            ->session()
            ->forget(
                'email_login_email'
            );

        /*
        |--------------------------------------------------------------------------
        | Rate limits opruimen
        |--------------------------------------------------------------------------
        */

        RateLimiter::clear(
            $rateLimitKey
        );

        RateLimiter::clear(
            $this->sendRateLimitKey(
                $request,
                $email
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Andere ongebruikte codes verwijderen
        |--------------------------------------------------------------------------
        */

        EmailLoginCode::query()
            ->where(
                'email',
                $email
            )
            ->whereNull(
                'used_at'
            )
            ->delete();

        return $this->redirectAfterSuccessfulLogin(
            $request,
            'Je bent succesvol ingelogd met je e-mailcode.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Magic login link versturen
    |--------------------------------------------------------------------------
    */

    public function sendMagicLink(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
        ]);

        $email = $this->normalizeEmail(
            $validated['email']
        );

        $rateLimitKey = $this->magicLinkSendRateLimitKey(
            $request,
            $email
        );

        if (
            RateLimiter::tooManyAttempts(
                $rateLimitKey,
                self::MAX_MAGIC_LINK_SEND_ATTEMPTS
            )
        ) {
            $seconds = RateLimiter::availableIn(
                $rateLimitKey
            );

            return back()
                ->withInput([
                    'email' => $email,
                ])
                ->with(
                    'error',
                    'Je hebt te vaak een loginlink aangevraagd. Probeer het over ' .
                    max(1, $seconds) .
                    ' seconden opnieuw.'
                );
        }

        RateLimiter::hit(
            $rateLimitKey,
            60
        );

        /*
        |--------------------------------------------------------------------------
        | Oude ongebruikte links verwijderen
        |--------------------------------------------------------------------------
        */

        EmailLoginLink::deleteUnusedForEmail(
            $email
        );

        /*
        |--------------------------------------------------------------------------
        | Veilige token genereren
        |--------------------------------------------------------------------------
        |
        | Alleen de SHA-256 hash wordt opgeslagen.
        |
        */

        $token = bin2hex(
            random_bytes(
                32
            )
        );

        $tokenHash = hash(
            'sha256',
            $token
        );

        $loginLink = EmailLoginLink::create([
            'email' =>
                $email,

            'token_hash' =>
                $tokenHash,

            'expires_at' =>
                now()->addMinutes(
                    self::MAGIC_LINK_EXPIRES_MINUTES
                ),

            'used_at' =>
                null,
        ]);

        $magicLinkUrl = route(
            'email-login.link.verify',
            [
                'token' =>
                    $token,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Magic link versturen
        |--------------------------------------------------------------------------
        */

        try {
            $this->brevoMail->send(
                $email,
                '',
                'Je veilige loginlink voor Mashal Studio',
                'emails.magic-login-link',
                [
                    'email' =>
                        $email,

                    'magicLinkUrl' =>
                        $magicLinkUrl,

                    'expiresInMinutes' =>
                        self::MAGIC_LINK_EXPIRES_MINUTES,
                ]
            );
        } catch (Throwable $exception) {
            try {
                $loginLink->delete();
            } catch (Throwable $deleteException) {
                report(
                    $deleteException
                );
            }

            report(
                $exception
            );

            return back()
                ->withInput([
                    'email' => $email,
                ])
                ->with(
                    'error',
                    'De loginlink kon niet worden verzonden. Probeer het later opnieuw.'
                );
        }

        $this->ensurePendingImageIntendedUrl(
            $request
        );

        return back()
            ->withInput([
                'email' => $email,
            ])
            ->with(
                'success',
                'We hebben een veilige loginlink naar je e-mailadres gestuurd. De link is ' .
                self::MAGIC_LINK_EXPIRES_MINUTES .
                ' minuten geldig en kan één keer worden gebruikt.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Magic link: token controleren
    |--------------------------------------------------------------------------
    |
    | BELANGRIJK:
    |
    | We loggen hier bewust nog NIET direct in.
    |
    | Eerst wordt de token gecontroleerd en tijdelijk aan de huidige
    | browsersessie gekoppeld. Daarna gaat de gebruiker naar een korte
    | bevestigingspagina waarop browser-timezone en, na toestemming,
    | GPS-locatie kunnen worden opgeslagen.
    |
    | Daardoor registreren we het apparaat waarop de magic link werkelijk
    | wordt geopend, ook wanneer dat een ander apparaat is dan waarop de
    | loginlink oorspronkelijk werd aangevraagd.
    |
    */

    public function verifyMagicLink(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'token' => [
                'required',
                'string',
                'size:64',
            ],
        ]);

        $token = trim(
            (string) $validated['token']
        );

        $rateLimitKey =
            $this->magicLinkVerifyRateLimitKey(
                $request
            );

        if (
            RateLimiter::tooManyAttempts(
                $rateLimitKey,
                self::MAX_MAGIC_LINK_VERIFY_ATTEMPTS
            )
        ) {
            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'error',
                    'Te veel loginlink-pogingen. Probeer het later opnieuw.'
                );
        }

        RateLimiter::hit(
            $rateLimitKey,
            300
        );

        $tokenHash = hash(
            'sha256',
            $token
        );

        $loginLink = EmailLoginLink::query()
            ->where(
                'token_hash',
                $tokenHash
            )
            ->first();

        $invalidResponse =
            $this->validateMagicLinkForUse(
                $loginLink
            );

        if ($invalidResponse !== null) {
            $this->clearPendingMagicLink(
                $request
            );

            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'error',
                    $invalidResponse
                );
        }

        $email = $this->normalizeEmail(
            $loginLink->email
        );

        if ($email === '') {
            $this->clearPendingMagicLink(
                $request
            );

            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'error',
                    'Deze loginlink bevat geen geldig e-mailadres.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Token-hash veilig in huidige sessie bewaren
        |--------------------------------------------------------------------------
        |
        | De echte token hoeft na dit punt niet meer in een formulier of URL
        | te worden meegestuurd.
        |
        */

        $request->session()->put([
            'email_magic_login.token_hash' =>
                $tokenHash,

            'email_magic_login.email' =>
                $email,

            'email_magic_login.started_at' =>
                now()->toIso8601String(),
        ]);

        $this->ensurePendingImageIntendedUrl(
            $request
        );

        return redirect()
            ->route(
                'email-login.link.confirm'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Magic link: bevestigingspagina tonen
    |--------------------------------------------------------------------------
    */

    public function showMagicLinkConfirmation(
        Request $request
    ): View|RedirectResponse {
        $tokenHash = trim(
            (string) $request
                ->session()
                ->get(
                    'email_magic_login.token_hash',
                    ''
                )
        );

        if ($tokenHash === '') {
            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'error',
                    'Er is geen actieve loginlink-sessie gevonden. Open de loginlink opnieuw vanuit je e-mail.'
                );
        }

        $loginLink = EmailLoginLink::query()
            ->where(
                'token_hash',
                $tokenHash
            )
            ->first();

        $invalidResponse =
            $this->validateMagicLinkForUse(
                $loginLink
            );

        if ($invalidResponse !== null) {
            $this->clearPendingMagicLink(
                $request
            );

            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'error',
                    $invalidResponse
                );
        }

        $email = $this->normalizeEmail(
            $loginLink->email
        );

        return view(
            'auth.magic-link-confirm',
            [
                'email' =>
                    $email,

                'maskedEmail' =>
                    $this->maskEmail(
                        $email
                    ),

                'expiresInMinutes' =>
                    self::MAGIC_LINK_EXPIRES_MINUTES,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Magic link: definitief inloggen
    |--------------------------------------------------------------------------
    |
    | De browser heeft vóór deze POST de login-security-context opgeslagen
    | via /login-security/context.
    |
    */

    public function completeMagicLink(
        Request $request
    ): RedirectResponse {
        $request->validate([
            'login_provider' => [
                'nullable',
                'string',
                'in:magic_link',
            ],
        ]);

        $tokenHash = trim(
            (string) $request
                ->session()
                ->get(
                    'email_magic_login.token_hash',
                    ''
                )
        );

        if ($tokenHash === '') {
            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'error',
                    'Je loginlink-sessie is verlopen. Open de loginlink opnieuw vanuit je e-mail.'
                );
        }

        $rateLimitKey =
            $this->magicLinkVerifyRateLimitKey(
                $request
            );

        if (
            RateLimiter::tooManyAttempts(
                $rateLimitKey,
                self::MAX_MAGIC_LINK_VERIFY_ATTEMPTS
            )
        ) {
            $this->clearPendingMagicLink(
                $request
            );

            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'error',
                    'Te veel loginlink-pogingen. Probeer het later opnieuw.'
                );
        }

        RateLimiter::hit(
            $rateLimitKey,
            300
        );

        $loginLink = EmailLoginLink::query()
            ->where(
                'token_hash',
                $tokenHash
            )
            ->first();

        $invalidResponse =
            $this->validateMagicLinkForUse(
                $loginLink
            );

        if ($invalidResponse !== null) {
            $this->clearPendingMagicLink(
                $request
            );

            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'error',
                    $invalidResponse
                );
        }

        $email = $this->normalizeEmail(
            $loginLink->email
        );

        if ($email === '') {
            $this->clearPendingMagicLink(
                $request
            );

            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'error',
                    'Deze loginlink bevat geen geldig e-mailadres.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Gebruiker zoeken of aanmaken
        |--------------------------------------------------------------------------
        */

        $user = User::query()
            ->where(
                'email',
                $email
            )
            ->first();

        if (! $user) {
            $user = $this->createUser(
                $email,
                'magic_link'
            );
        } else {
            $changes = [
                'login_provider' =>
                    'magic_link',
            ];

            if (! $user->email_verified_at) {
                $changes['email_verified_at'] =
                    now();
            }

            $user->forceFill(
                $changes
            )->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Link vóór de login als gebruikt markeren
        |--------------------------------------------------------------------------
        */

        $loginLink->markAsUsed();

        /*
        |--------------------------------------------------------------------------
        | Provider expliciet beschikbaar maken voor LoginSecurityService
        |--------------------------------------------------------------------------
        */

        $request->merge([
            'login_provider' =>
                'magic_link',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Gebruiker inloggen
        |--------------------------------------------------------------------------
        |
        | Op dit moment wordt Laravel's Login-event afgevuurd.
        |
        | De LoginSecurityService kan nu de browsercontext lezen die op de
        | bevestigingspagina is opgeslagen.
        |
        */

        Auth::login(
            $user,
            true
        );

        /*
        |--------------------------------------------------------------------------
        | Sessie vernieuwen en tijdelijke data opruimen
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();

        $request
            ->session()
            ->forget(
                'email_login_email'
            );

        $this->clearPendingMagicLink(
            $request
        );

        /*
        |--------------------------------------------------------------------------
        | Rate limits opruimen
        |--------------------------------------------------------------------------
        */

        RateLimiter::clear(
            $rateLimitKey
        );

        RateLimiter::clear(
            $this->magicLinkSendRateLimitKey(
                $request,
                $email
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Andere ongebruikte magic links verwijderen
        |--------------------------------------------------------------------------
        */

        EmailLoginLink::deleteUnusedForEmail(
            $email
        );

        return $this->redirectAfterSuccessfulLogin(
            $request,
            'Je bent succesvol ingelogd via je veilige loginlink.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Redirect na succesvolle passwordless login
    |--------------------------------------------------------------------------
    |
    | Een upload van vóór de login heeft altijd voorrang.
    |
    | Daardoor werkt zowel de e-mailcode-flow als de magic-link-flow zo:
    |
    | upload -> login -> claim -> editor.
    |
    */

    private function redirectAfterSuccessfulLogin(
        Request $request,
        string $successMessage
    ): RedirectResponse {
        if ($this->hasPendingImage($request)) {
            $this->ensurePendingImageIntendedUrl(
                $request
            );

            return redirect()
                ->route('images.claim')
                ->with(
                    'success',
                    $successMessage . ' Je eerdere upload wordt nu automatisch geopend.'
                );
        }

        return redirect()
            ->intended(
                route('account')
            )
            ->with(
                'success',
                $successMessage
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Pending image controleren
    |--------------------------------------------------------------------------
    */

    private function hasPendingImage(
        Request $request
    ): bool {
        if (! $request->hasSession()) {
            return false;
        }

        $pending = $request->session()->get(
            'pending_image'
        );

        return is_array($pending)
            && ! empty($pending['path']);
    }


    /*
    |--------------------------------------------------------------------------
    | Claim-route als intended URL bewaren
    |--------------------------------------------------------------------------
    |
    | Intermediate forms en sessieregeneratie mogen de oorspronkelijke upload
    | niet laten verdwijnen uit de gebruikersflow.
    |
    */

    private function ensurePendingImageIntendedUrl(
        Request $request
    ): void {
        if (
            ! $request->hasSession() ||
            ! $this->hasPendingImage($request)
        ) {
            return;
        }

        $request->session()->put(
            'url.intended',
            route('images.claim')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Magic link veilig controleren
    |--------------------------------------------------------------------------
    */

    private function validateMagicLinkForUse(
        ?EmailLoginLink $loginLink
    ): ?string {
        if (! $loginLink) {
            return 'Deze loginlink is ongeldig. Vraag een nieuwe loginlink aan.';
        }

        if ($loginLink->isUsed()) {
            return 'Deze loginlink is al gebruikt. Vraag een nieuwe loginlink aan.';
        }

        if ($loginLink->isExpired()) {
            try {
                $loginLink->delete();
            } catch (Throwable $exception) {
                report(
                    $exception
                );
            }

            return 'Deze loginlink is verlopen. Vraag een nieuwe loginlink aan.';
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Tijdelijke magic-link sessie opruimen
    |--------------------------------------------------------------------------
    */

    private function clearPendingMagicLink(
        Request $request
    ): void {
        if (! $request->hasSession()) {
            return;
        }

        $request->session()->forget([
            'email_magic_login.token_hash',
            'email_magic_login.email',
            'email_magic_login.started_at',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Nieuwe gebruiker maken
    |--------------------------------------------------------------------------
    */

    private function createUser(
        string $email,
        string $loginProvider = 'email_code'
    ): User {
        $emailName = trim(
            Str::before(
                $email,
                '@'
            )
        );

        $displayName = Str::of(
            $emailName
        )
            ->replace(
                [
                    '.',
                    '_',
                    '-',
                ],
                ' '
            )
            ->squish()
            ->title()
            ->toString();

        if ($displayName === '') {
            $displayName =
                'Mashal gebruiker';
        }

        return User::create([
            'name' =>
                $displayName,

            'email' =>
                $email,

            /*
            |--------------------------------------------------------------------------
            | Willekeurig intern wachtwoord
            |--------------------------------------------------------------------------
            |
            | Passwordless-gebruikers hoeven dit wachtwoord niet te kennen.
            |
            */

            'password' =>
                Hash::make(
                    Str::random(
                        64
                    )
                ),

            'email_verified_at' =>
                now(),

            'login_provider' =>
                $loginProvider,

            'is_admin' =>
                false,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | E-mailadres normaliseren
    |--------------------------------------------------------------------------
    */

    private function normalizeEmail(
        mixed $email
    ): string {
        if (! is_scalar($email)) {
            return '';
        }

        return strtolower(
            trim(
                (string) $email
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | E-mailadres maskeren
    |--------------------------------------------------------------------------
    |
    | Voorbeeld:
    |
    | mashal@example.com -> m*****@example.com
    |
    */

    private function maskEmail(
        string $email
    ): string {
        if (
            $email === '' ||
            ! str_contains(
                $email,
                '@'
            )
        ) {
            return $email;
        }

        [$localPart, $domain] =
            explode(
                '@',
                $email,
                2
            );

        $firstCharacter =
            Str::substr(
                $localPart,
                0,
                1
            );

        $maskedLength = max(
            3,
            Str::length(
                $localPart
            ) - 1
        );

        return
            $firstCharacter .
            str_repeat(
                '*',
                $maskedLength
            ) .
            '@' .
            $domain;
    }


    /*
    |--------------------------------------------------------------------------
    | Rate-limit key voor code versturen
    |--------------------------------------------------------------------------
    */

    private function sendRateLimitKey(
        Request $request,
        string $email
    ): string {
        return
            'email-login-send:' .
            sha1(
                (string) $request->ip() .
                '|' .
                $email
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Rate-limit key voor code controleren
    |--------------------------------------------------------------------------
    */

    private function verifyRateLimitKey(
        Request $request,
        string $email
    ): string {
        return
            'email-login-verify:' .
            sha1(
                (string) $request->ip() .
                '|' .
                $email
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Rate-limit key voor magic link versturen
    |--------------------------------------------------------------------------
    */

    private function magicLinkSendRateLimitKey(
        Request $request,
        string $email
    ): string {
        return
            'email-magic-link-send:' .
            sha1(
                (string) $request->ip() .
                '|' .
                $email
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Rate-limit key voor magic link controleren
    |--------------------------------------------------------------------------
    */

    private function magicLinkVerifyRateLimitKey(
        Request $request
    ): string {
        return
            'email-magic-link-verify:' .
            sha1(
                (string) $request->ip()
            );
    }
}
