<?php

namespace App\Http\Controllers;

use App\Models\EmailLoginCode;
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
            $loginCode->increment(
                'attempts'
            );

            $remainingAttempts =
                self::MAX_CODE_ATTEMPTS -
                ($loginCode->attempts + 1);

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

        $loginCode->forceFill([
            'used_at' => now(),
        ])->save();


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
                $email
            );
        } else {

            /*
            |--------------------------------------------------------------------------
            | Bestaande gebruiker als geverifieerd markeren
            |--------------------------------------------------------------------------
            */

            if (! $user->email_verified_at) {
                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();
            }
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
    | Nieuwe gebruiker maken
    |--------------------------------------------------------------------------
    */

    private function createUser(string $email): User
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
}