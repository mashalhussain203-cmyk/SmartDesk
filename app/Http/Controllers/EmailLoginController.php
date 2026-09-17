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

    public function sendCode(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Invoer valideren
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
        ]);

        $email = strtolower(
            trim(
                (string) $validated['email']
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Rate limiting
        |--------------------------------------------------------------------------
        |
        | Voorkomt dat iemand onbeperkt e-mails kan laten versturen.
        |
        */

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
        | Oude codes opruimen
        |--------------------------------------------------------------------------
        |
        | Zodra een nieuwe code wordt aangevraagd worden eerdere ongebruikte
        | codes voor hetzelfde e-mailadres verwijderd.
        |
        */

        EmailLoginCode::where(
            'email',
            $email
        )
            ->whereNull('used_at')
            ->delete();


        /*
        |--------------------------------------------------------------------------
        | Nieuwe 6-cijferige code genereren
        |--------------------------------------------------------------------------
        */

        $code = (string) random_int(
            100000,
            999999
        );


        /*
        |--------------------------------------------------------------------------
        | Code veilig opslaan
        |--------------------------------------------------------------------------
        |
        | De echte code wordt nooit leesbaar in de database opgeslagen.
        |
        */

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
        | Code versturen via Brevo
        |--------------------------------------------------------------------------
        */

        try {
            $this->brevoMail->send(
                $email,
                '',
                'Je inlogcode voor Mashal Automotive',
                'emails.login-code',
                [
                    'code' => $code,

                    'email' => $email,

                    'expiresInMinutes' =>
                        self::CODE_EXPIRES_MINUTES,
                ]
            );
        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | Code verwijderen wanneer e-mail verzenden mislukt
            |--------------------------------------------------------------------------
            */

            try {
                $loginCode->delete();
            } catch (Throwable $deleteException) {
                report($deleteException);
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'De inlogcode kon niet worden verzonden. Probeer het later opnieuw.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | E-mailadres tijdelijk in sessie bewaren
        |--------------------------------------------------------------------------
        |
        | Hierdoor kunnen we het verificatieformulier automatisch invullen.
        |
        */

        $request->session()->put(
            'email_login_email',
            $email
        );


        /*
        |--------------------------------------------------------------------------
        | Naar verificatiepagina
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('email-login.form')
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

    public function showVerifyForm(Request $request): View
    {
        $email = strtolower(
            trim(
                (string) $request
                    ->session()
                    ->get(
                        'email_login_email',
                        ''
                    )
            )
        );

        return view(
            'auth.email-login',
            [
                'email' => $email,

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

    public function verifyCode(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Invoer valideren
        |--------------------------------------------------------------------------
        */

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

        $email = strtolower(
            trim(
                (string) $validated['email']
            )
        );

        $code = trim(
            (string) $validated['code']
        );


        /*
        |--------------------------------------------------------------------------
        | Rate limiting verificatie
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
        | Laatste ongebruikte code zoeken
        |--------------------------------------------------------------------------
        */

        $loginCode = EmailLoginCode::where(
            'email',
            $email
        )
            ->whereNull('used_at')
            ->latest('id')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Code bestaat niet
        |--------------------------------------------------------------------------
        */

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
        | Vervaldatum controleren
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
        | Maximum verkeerde pogingen controleren
        |--------------------------------------------------------------------------
        */

        if (
            $loginCode->attempts >=
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
                $loginCode->code_hash
            )
        ) {
            $loginCode->registerFailedAttempt();

            $remainingAttempts =
                self::MAX_CODE_ATTEMPTS -
                $loginCode->attempts;

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
        | Code direct als gebruikt markeren
        |--------------------------------------------------------------------------
        |
        | Hierdoor kan dezelfde code niet opnieuw worden gebruikt.
        |
        */

        $loginCode->markAsUsed();


        /*
        |--------------------------------------------------------------------------
        | Gebruiker zoeken
        |--------------------------------------------------------------------------
        */

        $user = User::where(
            'email',
            $email
        )->first();


        /*
        |--------------------------------------------------------------------------
        | Nieuwe gebruiker
        |--------------------------------------------------------------------------
        |
        | Het bezit van het e-mailadres is nu bevestigd door de juiste code.
        | Daarom kan het e-mailadres direct als geverifieerd worden gemarkeerd.
        |
        */

        if (! $user) {
            $user = $this->createUser(
                $email,
                'email_code'
            );
        } else {

            /*
            |--------------------------------------------------------------------------
            | Bestaande gebruiker bijwerken
            |--------------------------------------------------------------------------
            |
            | Een succesvolle e-mailcode bevestigt het e-mailadres en registreert
            | meteen dat de laatste login via e-mailcode plaatsvond.
            |
            */

            $changes = [
                'login_provider' => 'email_code',
            ];

            if (! $user->email_verified_at) {
                $changes['email_verified_at'] = now();
            }

            $user->forceFill(
                $changes
            )->save();
        }


        /*
        |--------------------------------------------------------------------------
        | Gebruiker inloggen
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user,
            true
        );


        /*
        |--------------------------------------------------------------------------
        | Session-ID vernieuwen
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Tijdelijke login-sessie opruimen
        |--------------------------------------------------------------------------
        */

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

        EmailLoginCode::where(
            'email',
            $email
        )
            ->whereNull('used_at')
            ->delete();


        /*
        |--------------------------------------------------------------------------
        | Naar accountpagina
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('account')
            ->with(
                'success',
                'Je bent succesvol ingelogd met je e-mailcode.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Magic login link versturen
    |--------------------------------------------------------------------------
    */

    public function sendMagicLink(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Invoer valideren
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
        ]);

        $email = strtolower(
            trim(
                (string) $validated['email']
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Rate limiting
        |--------------------------------------------------------------------------
        */

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
        | Alleen de SHA-256 hash wordt in de database opgeslagen.
        | De echte token bestaat uitsluitend in de e-mail-URL.
        |
        */

        $token = bin2hex(
            random_bytes(32)
        );

        $tokenHash = hash(
            'sha256',
            $token
        );


        /*
        |--------------------------------------------------------------------------
        | Magic link opslaan
        |--------------------------------------------------------------------------
        */

        $loginLink = EmailLoginLink::create([
            'email' => $email,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addMinutes(
                self::MAGIC_LINK_EXPIRES_MINUTES
            ),
            'used_at' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Veilige URL maken
        |--------------------------------------------------------------------------
        */

        $magicLinkUrl = route(
            'email-login.link.verify',
            [
                'token' => $token,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Magic link versturen via Brevo
        |--------------------------------------------------------------------------
        */

        try {
            $this->brevoMail->send(
                $email,
                '',
                'Je veilige loginlink voor Mashal Automotive',
                'emails.magic-login-link',
                [
                    'email' => $email,
                    'magicLinkUrl' => $magicLinkUrl,
                    'expiresInMinutes' =>
                        self::MAGIC_LINK_EXPIRES_MINUTES,
                ]
            );
        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | Link verwijderen wanneer e-mail verzenden mislukt
            |--------------------------------------------------------------------------
            */

            try {
                $loginLink->delete();
            } catch (Throwable $deleteException) {
                report($deleteException);
            }

            report($exception);

            return back()
                ->withInput([
                    'email' => $email,
                ])
                ->with(
                    'error',
                    'De loginlink kon niet worden verzonden. Probeer het later opnieuw.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Succesmelding
        |--------------------------------------------------------------------------
        */

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
    | Magic login link controleren
    |--------------------------------------------------------------------------
    */

    public function verifyMagicLink(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Token valideren
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Algemene verificatie-rate-limit
        |--------------------------------------------------------------------------
        */

        $rateLimitKey = $this->magicLinkVerifyRateLimitKey(
            $request
        );

        if (
            RateLimiter::tooManyAttempts(
                $rateLimitKey,
                self::MAX_MAGIC_LINK_VERIFY_ATTEMPTS
            )
        ) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Te veel loginlink-pogingen. Probeer het later opnieuw.'
                );
        }

        RateLimiter::hit(
            $rateLimitKey,
            300
        );


        /*
        |--------------------------------------------------------------------------
        | Token hashen en link zoeken
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Link bestaat niet
        |--------------------------------------------------------------------------
        */

        if (! $loginLink) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Deze loginlink is ongeldig. Vraag een nieuwe loginlink aan.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Link al gebruikt
        |--------------------------------------------------------------------------
        */

        if ($loginLink->isUsed()) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Deze loginlink is al gebruikt. Vraag een nieuwe loginlink aan.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Link verlopen
        |--------------------------------------------------------------------------
        */

        if ($loginLink->isExpired()) {
            $loginLink->delete();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Deze loginlink is verlopen. Vraag een nieuwe loginlink aan.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | E-mailadres uit link
        |--------------------------------------------------------------------------
        */

        $email = strtolower(
            trim(
                (string) $loginLink->email
            )
        );

        if ($email === '') {
            return redirect()
                ->route('login')
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

        $user = User::where(
            'email',
            $email
        )->first();

        if (! $user) {
            $user = $this->createUser(
                $email,
                'magic_link'
            );
        } else {
            $changes = [
                'login_provider' => 'magic_link',
            ];

            if (! $user->email_verified_at) {
                $changes['email_verified_at'] = now();
            }

            $user->forceFill(
                $changes
            )->save();
        }


        /*
        |--------------------------------------------------------------------------
        | Link als gebruikt markeren
        |--------------------------------------------------------------------------
        */

        $loginLink->markAsUsed();


        /*
        |--------------------------------------------------------------------------
        | Gebruiker inloggen
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user,
            true
        );

        $request
            ->session()
            ->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Oude tijdelijke authenticatiegegevens opruimen
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget(
                'email_login_email'
            );

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


        /*
        |--------------------------------------------------------------------------
        | Naar accountpagina
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('account')
            ->with(
                'success',
                'Je bent succesvol ingelogd via je veilige loginlink.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Nieuwe gebruiker maken
    |--------------------------------------------------------------------------
    */

    private function createUser(
        string $email,
        string $loginProvider = 'email_code'
    ): User
    {
        /*
        |--------------------------------------------------------------------------
        | Naam uit e-mailadres halen
        |--------------------------------------------------------------------------
        */

        $emailName = trim(
            Str::before(
                $email,
                '@'
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Leesbaardere naam maken
        |--------------------------------------------------------------------------
        |
        | Voorbeeld:
        |
        | mashal.hussain -> Mashal Hussain
        |
        */

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
            $displayName = 'Mashal gebruiker';
        }


        /*
        |--------------------------------------------------------------------------
        | Account aanmaken
        |--------------------------------------------------------------------------
        */

        return User::create([
            'name' => $displayName,

            'email' => $email,

            /*
            |--------------------------------------------------------------------------
            | Willekeurig intern wachtwoord
            |--------------------------------------------------------------------------
            |
            | De gebruiker hoeft dit wachtwoord niet te kennen omdat hij
            | via de e-mailcode inlogt.
            |
            */

            'password' => Hash::make(
                Str::random(64)
            ),

            'email_verified_at' => now(),

            'login_provider' => $loginProvider,

            'is_admin' => false,
        ]);
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
        return 'email-login-send:' .
            sha1(
                $request->ip() .
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
        return 'email-login-verify:' .
            sha1(
                $request->ip() .
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
        return 'email-magic-link-send:' .
            sha1(
                $request->ip() .
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
        return 'email-magic-link-verify:' .
            sha1(
                (string) $request->ip()
            );
    }
}