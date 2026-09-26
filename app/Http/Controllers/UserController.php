<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BrevoMailService;
use App\Services\LoginApprovalService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function __construct(
        private readonly BrevoMailService $brevoMail,
        private readonly LoginApprovalService $loginApproval
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Catalogus auto's
    |--------------------------------------------------------------------------
    */

    private function catalogCars(): array
    {
        return [
            [
                'id' => 1,
                'brand' => 'Audi',
                'model' => 'A6 Sportback',
                'year' => 2024,
                'type' => 'Sedan',
                'fuel' => 'Hybrid',
                'price' => 48990,
                'image' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1000&q=80',
                'summary' => 'Premium comfort voor dagelijks rijden en lange reizen.',
            ],
            [
                'id' => 2,
                'brand' => 'Mercedes',
                'model' => 'C-Class',
                'year' => 2024,
                'type' => 'Sedan',
                'fuel' => 'Diesel',
                'price' => 44990,
                'image' => 'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?auto=format&fit=crop&w=1000&q=80',
                'summary' => 'Comfort, design en premium technologie in één auto.',
            ],
            [
                'id' => 3,
                'brand' => 'BMW',
                'model' => 'X5',
                'year' => 2023,
                'type' => 'SUV',
                'fuel' => 'Petrol',
                'price' => 67850,
                'image' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1000&q=80',
                'summary' => 'Ruimte, stijl en krachtige prestaties voor elke route.',
            ],
            [
                'id' => 4,
                'brand' => 'Volkswagen',
                'model' => 'Golf GTI',
                'year' => 2024,
                'type' => 'Hatchback',
                'fuel' => 'Petrol',
                'price' => 34990,
                'image' => 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1000&q=80',
                'summary' => 'Sportieve stijl voor dagelijks gebruik en de weekendtrip.',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Home
    |--------------------------------------------------------------------------
    */

    public function home(): View
    {
        return view('site.home');
    }

    /*
    |--------------------------------------------------------------------------
    | Registreren
    |--------------------------------------------------------------------------
    */

    public function register(): View
    {
        return view('site.register');
    }

    public function registerSubmit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'terms' => [
                'accepted',
            ],
        ]);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'login_provider' => 'password',
            'is_admin' => false,
            'email_verified_at' => null,
        ]);

        $this->createVerificationCode($user);

        $emailSent = $this->sendVerificationEmail(
            $user,
            'Je verificatiecode voor Mashal Studio'
        );

        /*
        |--------------------------------------------------------------------------
        | Login-security context opruimen, pending image behouden
        |--------------------------------------------------------------------------
        |
        | De tijdelijke browserlocatie/security-context hoort niet mee te gaan,
        | maar pending_image en url.intended laten we bewust staan.
        |
        | Daardoor blijft deze flow intact:
        |
        | upload -> registratie -> verificatie -> claim -> editor.
        |
        */

        $this->forgetLoginSecurityBrowserContext($request);

        if ($this->hasPendingImage($request)) {
            $this->ensurePendingImageIntendedUrl($request);
        }

        if (! $emailSent) {
            return redirect()
                ->route('verification.notice')
                ->with(
                    'error',
                    'Je account is aangemaakt, maar de verificatiemail kon niet worden verzonden. Vraag een nieuwe verificatiecode aan.'
                )
                ->with('email', $user->email);
        }

        return redirect()
            ->route('verification.notice')
            ->with(
                'success',
                $this->hasPendingImage($request)
                    ? 'Account aangemaakt. Verifieer je e-mailadres; daarna openen we automatisch je geüploade afbeelding.'
                    : 'Account aangemaakt. Controleer je e-mail voor de verificatiecode.'
            )
            ->with('email', $user->email);
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Optioneel e-mailadres voorinvullen
        |--------------------------------------------------------------------------
        |
        | De forgot-email-resultaatpagina kan terugsturen naar:
        |
        | /login?email=naam@example.com
        |
        | De bestaande login-view gebruikt old('email'), daarom flashen we
        | alleen een geldig e-mailadres naar de old-input sessie.
        |
        */

        $prefillEmail = strtolower(
            trim(
                (string) $request->query(
                    'email',
                    ''
                )
            )
        );

        if (
            $prefillEmail !== '' &&
            filter_var(
                $prefillEmail,
                FILTER_VALIDATE_EMAIL
            ) !== false
        ) {
            $request->session()->flash(
                '_old_input',
                [
                    'email' => $prefillEmail,
                ]
            );
        }

        return view('site.login');
    }

    public function loginSubmit(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
            ],
            'password' => [
                'required',
                'string',
            ],
        ]);

        $credentials['email'] = strtolower(
            trim($credentials['email'])
        );

        $remember = $request->boolean('remember');

        /*
        |--------------------------------------------------------------------------
        | Provider vóór Auth::attempt bekendmaken
        |--------------------------------------------------------------------------
        */

        $request->merge([
            'login_provider' => 'password',
        ]);

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors([
                    'email' => 'Het e-mailadres of wachtwoord is niet correct.',
                ])
                ->onlyInput('email');
        }

        /*
        |--------------------------------------------------------------------------
        | Sessiefixatie voorkomen
        |--------------------------------------------------------------------------
        |
        | Laravel houdt normale sessiedata bij regenerate() in stand.
        | Daardoor blijft pending_image aanwezig na succesvolle login.
        |
        */

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        $user->forceFill([
            'login_provider' => 'password',
        ])->save();

        /*
        |--------------------------------------------------------------------------
        | Device approval vanaf een bestaand ingelogd apparaat
        |--------------------------------------------------------------------------
        |
        | Wanneer dezelfde gebruiker nog een andere actieve database-sessie
        | heeft, wordt deze nieuwe login eerst pending gemaakt. De bestaande
        | sessie krijgt op Mashal Studio drie nummers te zien. Alleen hetzelfde
        | nummer als op dit nieuwe apparaat keurt de login goed.
        |
        | Is nergens anders meer een actieve sessie, dan blijft de bestaande
        | login/TOTP-flow beschikbaar zodat de gebruiker zichzelf niet kan
        | buitensluiten.
        |
        */

        if (
            $this->loginApproval
                ->hasOtherActiveSession(
                    $user,
                    $request
                )
        ) {
            /*
             * Gewenste eindbestemming nu al bewaren. Na device approval kan de
             * LoginApprovalController redirect()->intended() gebruiken.
             */
            if ($this->hasPendingImage($request)) {
                $this->ensurePendingImageIntendedUrl(
                    $request
                );
            } elseif (
                $user->is_admin
                && ! $request
                    ->session()
                    ->has(
                        'url.intended'
                    )
            ) {
                $request
                    ->session()
                    ->put(
                        'url.intended',
                        route(
                            'admin.dashboard'
                        )
                    );
            } elseif (
                ! $request
                    ->session()
                    ->has(
                        'url.intended'
                    )
            ) {
                $request
                    ->session()
                    ->put(
                        'url.intended',
                        route('home')
                    );
            }

            $challenge =
                $this->loginApproval
                    ->createChallenge(
                        $user,
                        $request,
                        $remember,
                        'password'
                    );

            /*
             * De nieuwe browser mag nog niet authenticated blijven.
             */
            Auth::logout();

            $request
                ->session()
                ->regenerate();

            $request
                ->session()
                ->put([
                    'login_approval.challenge_id' =>
                        $challenge['id'],

                    'login_approval.number' =>
                        $challenge['number'],

                    'login_approval.expires_at' =>
                        $challenge['expires_at'],
                ]);

            return redirect()
                ->route(
                    'login.approval'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Authenticator 2FA vereist?
        |--------------------------------------------------------------------------
        |
        | Wanneer 2FA actief is, mag de gebruiker nog niet definitief door naar
        | de applicatie. We bewaren de gewenste eindbestemming, loggen de
        | tijdelijke Laravel-authenticatie weer uit en sturen de gebruiker naar
        | de aparte 2FA-challenge.
        |
        | De TwoFactorChallengeController logt de gebruiker pas opnieuw in nadat
        | een geldige TOTP-code of recovery code is ingevoerd.
        |
        */

        if ($user->two_factor_confirmed_at !== null) {
            if ($this->hasPendingImage($request)) {
                $this->ensurePendingImageIntendedUrl($request);
            } elseif ($user->is_admin) {
                $request->session()->put(
                    'url.intended',
                    route('admin.dashboard')
                );
            } elseif (! $request->session()->has('url.intended')) {
                $request->session()->put(
                    'url.intended',
                    route('home')
                );
            }

            $request->session()->put([
                'two_factor.login.user_id' => $user->id,
                'two_factor.login.remember' => $remember,
                'two_factor.login.provider' => 'password',
            ]);

            Auth::logout();

            $request->session()->regenerate();

            return redirect()
                ->route('two-factor.challenge')
                ->with(
                    'success',
                    'Voer de 6-cijferige code uit je Authenticator-app in om de login te voltooien.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Pending upload heeft voorrang
        |--------------------------------------------------------------------------
        |
        | Gebruik hier niet alleen redirect()->intended(), maar controleer de
        | pending upload expliciet. Zo kan een andere controller of middleware
        | de intended URL niet per ongeluk overschrijven.
        |
        */

        if ($this->hasPendingImage($request)) {
            $this->ensurePendingImageIntendedUrl($request);

            return redirect()
                ->route('images.claim')
                ->with(
                    'success',
                    'Je bent ingelogd. Je eerdere upload wordt nu aan je account gekoppeld.'
                );
        }

        if ($user->is_admin) {
            return redirect()
                ->intended(route('admin.dashboard'))
                ->with(
                    'success',
                    'Welkom terug, ' . $user->name . '.'
                );
        }

        return redirect()
            ->intended(route('home'))
            ->with(
                'success',
                'Je bent ingelogd.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with(
                'success',
                'Je bent veilig uitgelogd.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Mijn account
    |--------------------------------------------------------------------------
    */

    public function account(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $orders = DB::table('orders')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view(
            'site.account',
            compact(
                'user',
                'orders'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Eigen accountgegevens wijzigen
    |--------------------------------------------------------------------------
    */

    public function updateAccount(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Invoer valideren
        |--------------------------------------------------------------------------
        |
        | profile_photo:
        | - alleen echte afbeeldingen;
        | - jpg/jpeg/png/webp;
        | - maximaal 5 MB.
        |
        */

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'remove_profile_photo' => [
                'nullable',
                'boolean',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Nieuwe basisgegevens voorbereiden
        |--------------------------------------------------------------------------
        */

        $name = trim(
            (string) $data['name']
        );

        $email = strtolower(
            trim(
                (string) $data['email']
            )
        );

        $oldName = (string) $user->name;
        $oldEmail = (string) $user->email;

        $oldRecoveryEmail = strtolower(
            trim(
                (string) ($user->recovery_email ?? '')
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Hoofdadres mag niet gelijk worden aan het hersteladres
        |--------------------------------------------------------------------------
        |
        | recovery_email zelf wordt uitsluitend gewijzigd via
        | RecoveryEmailController nadat een 6-cijferige code is bevestigd.
        |
        */

        if (
            $oldRecoveryEmail !== ''
            && $oldRecoveryEmail === $email
            && strtolower($oldEmail) !== $email
        ) {
            return back()
                ->withErrors([
                    'email' =>
                        'Je gewone e-mailadres mag niet hetzelfde zijn als je actieve herstel-e-mailadres.',
                ])
                ->withInput();
        }

        $oldProfilePhoto = trim(
            (string) ($user->profile_photo ?? '')
        );

        $nameChanged =
            $oldName !== $name;

        $emailChanged =
            strtolower($oldEmail) !== $email;


        /*
        |--------------------------------------------------------------------------
        | Profielfoto voorbereiden
        |--------------------------------------------------------------------------
        */

        $newProfilePhoto = null;
        $profilePhotoChanged = false;

        if ($request->hasFile('profile_photo')) {
            $uploadedPhoto = $request->file(
                'profile_photo'
            );

            if (! $uploadedPhoto) {
                return redirect()
                    ->route('account')
                    ->with(
                        'error',
                        'De profielfoto kon niet worden gelezen. Probeer opnieuw.'
                    );
            }

            try {
                $newProfilePhoto = $uploadedPhoto->store(
                    'profile-photos',
                    'public'
                );
            } catch (Throwable $exception) {
                report($exception);

                return redirect()
                    ->route('account')
                    ->withInput()
                    ->with(
                        'error',
                        'De profielfoto kon niet worden opgeslagen. Probeer het later opnieuw.'
                    );
            }

            $profilePhotoChanged = true;
        } elseif (
            $request->boolean(
                'remove_profile_photo'
            ) &&
            $oldProfilePhoto !== ''
        ) {
            $profilePhotoChanged = true;
        }


        /*
        |--------------------------------------------------------------------------
        | Geen wijzigingen
        |--------------------------------------------------------------------------
        */

        if (
            ! $nameChanged &&
            ! $emailChanged &&
            ! $profilePhotoChanged
        ) {
            return redirect()
                ->route('account')
                ->with(
                    'success',
                    'Er zijn geen wijzigingen om op te slaan.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Gebruiker bijwerken
        |--------------------------------------------------------------------------
        */

        $user->name = $name;

        if ($emailChanged) {
            $user->email = $email;
            $user->email_verified_at = null;
        }

        if ($profilePhotoChanged) {
            $user->profile_photo =
                $newProfilePhoto;
        }


        /*
        |--------------------------------------------------------------------------
        | Opslaan
        |--------------------------------------------------------------------------
        |
        | Als database-opslag mislukt nadat een nieuwe foto al is opgeslagen,
        | verwijderen we die nieuwe foto weer om verweesde bestanden te voorkomen.
        |
        */

        try {
            $user->save();
        } catch (Throwable $exception) {
            if ($newProfilePhoto) {
                try {
                    Storage::disk('public')
                        ->delete(
                            $newProfilePhoto
                        );
                } catch (Throwable $storageException) {
                    report(
                        $storageException
                    );
                }
            }

            throw $exception;
        }


        /*
        |--------------------------------------------------------------------------
        | Oude lokale profielfoto opruimen
        |--------------------------------------------------------------------------
        */

        if (
            $profilePhotoChanged &&
            $oldProfilePhoto !== '' &&
            $oldProfilePhoto !==
                (string) $newProfilePhoto
        ) {
            $this->deleteLocalProfilePhoto(
                $oldProfilePhoto
            );
        }


        /*
        |--------------------------------------------------------------------------
        | E-mailadres gewijzigd
        |--------------------------------------------------------------------------
        */

        if ($emailChanged) {
            $this->createVerificationCode(
                $user
            );

            $verificationSent =
                $this->sendVerificationEmail(
                    $user,
                    'Bevestig je nieuwe e-mailadres - Mashal Studio'
                );


            /*
            |--------------------------------------------------------------------------
            | Beveiligingsmelding naar oude e-mailadres
            |--------------------------------------------------------------------------
            */

            $this->sendEmailSafely(
                $oldEmail,
                $oldName,
                'Je Mashal Studio-e-mailadres is gewijzigd',
                'emails.email-changed',
                [
                    'user' => $user,
                    'oldEmail' => $oldEmail,
                    'newEmail' => $user->email,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Accountwijziging naar nieuwe adres
            |--------------------------------------------------------------------------
            */

            if (
                $nameChanged ||
                $profilePhotoChanged
            ) {
                $this->sendEmailSafely(
                    $user->email,
                    $user->name,
                    'Je Mashal Studio-accountgegevens zijn gewijzigd',
                    'emails.account-updated',
                    [
                        'user' => $user,
                        'oldName' => $oldName,
                        'oldEmail' => $oldEmail,
                        'emailChanged' => true,
                    ]
                );
            }

            if (! $verificationSent) {
                return redirect()
                    ->route(
                        'verification.notice'
                    )
                    ->with(
                        'error',
                        'Je nieuwe e-mailadres is opgeslagen, maar de verificatiemail kon niet worden verzonden. Vraag een nieuwe verificatiecode aan.'
                    );
            }

            return redirect()
                ->route(
                    'verification.notice'
                )
                ->with(
                    'success',
                    'Je nieuwe e-mailadres is opgeslagen. We hebben een verificatiecode naar je nieuwe e-mailadres gestuurd.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Naam en/of profielfoto gewijzigd
        |--------------------------------------------------------------------------
        */

        if (
            $nameChanged ||
            $profilePhotoChanged
        ) {
            $emailSent =
                $this->sendEmailSafely(
                    $user->email,
                    $user->name,
                    'Je Mashal Studio-accountgegevens zijn gewijzigd',
                    'emails.account-updated',
                    [
                        'user' => $user,
                        'oldName' => $oldName,
                        'oldEmail' => $oldEmail,
                        'emailChanged' => false,
                    ]
                );

            if (! $emailSent) {
                return redirect()
                    ->route('account')
                    ->with(
                        'success',
                        'Je accountgegevens zijn opgeslagen. De bevestigingsmail kon niet worden verzonden.'
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Succes
        |--------------------------------------------------------------------------
        */

        $message =
            $profilePhotoChanged &&
            ! $nameChanged
                ? 'Je profielfoto is bijgewerkt.'
                : 'Je accountgegevens zijn opgeslagen.';

        return redirect()
            ->route('account')
            ->with(
                'success',
                $message
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Eigen wachtwoord wijzigen
    |--------------------------------------------------------------------------
    */

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'current_password' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        if (! Hash::check(
            $data['current_password'],
            $user->password
        )) {
            return back()
                ->withErrors([
                    'current_password' =>
                        'Je huidige wachtwoord is niet correct.',
                ]);
        }

        if (Hash::check(
            $data['password'],
            $user->password
        )) {
            return back()
                ->withErrors([
                    'password' =>
                        'Je nieuwe wachtwoord moet verschillen van je huidige wachtwoord.',
                ]);
        }

        $user->password = Hash::make(
            $data['password']
        );

        $user->save();

        $emailSent = $this->sendEmailSafely(
            $user->email,
            $user->name,
            'Je Mashal Studio-wachtwoord is gewijzigd',
            'emails.password-changed',
            [
                'user' => $user,
            ]
        );

        if (! $emailSent) {
            return redirect()
                ->route('account')
                ->with(
                    'success',
                    'Je wachtwoord is gewijzigd. De bevestigingsmail kon niet worden verzonden.'
                );
        }

        return redirect()
            ->route('account')
            ->with(
                'success',
                'Je wachtwoord is gewijzigd. We hebben een bevestiging naar je e-mailadres gestuurd.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Catalogus
    |--------------------------------------------------------------------------
    */

    public function catalog(): View
    {
        $cars = $this->catalogCars();

        return view(
            'site.catalog',
            compact('cars')
        );
    }

    public function car(string $id): View
    {
        $car = collect(
            $this->catalogCars()
        )->firstWhere(
            'id',
            (int) $id
        );

        abort_if(! $car, 404);

        return view(
            'site.product',
            compact('car')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Winkelwagen
    |--------------------------------------------------------------------------
    */

    public function addToCart(string $id): RedirectResponse
    {
        $car = collect(
            $this->catalogCars()
        )->firstWhere(
            'id',
            (int) $id
        );

        abort_if(! $car, 404);

        $cart = Session::get(
            'cart',
            []
        );

        $cart[$car['id']] = [
            'id' => $car['id'],
            'brand' => $car['brand'],
            'model' => $car['model'],
            'price' => $car['price'],
            'image' => $car['image'],
            'qty' =>
                ($cart[$car['id']]['qty'] ?? 0) + 1,
        ];

        Session::put(
            'cart',
            $cart
        );

        return redirect()
            ->route('cart')
            ->with(
                'success',
                $car['brand'] .
                ' ' .
                $car['model'] .
                ' is toegevoegd aan je winkelwagen.'
            );
    }

    public function cart(): View
    {
        $cart = Session::get(
            'cart',
            []
        );

        $total = collect($cart)->sum(
            fn ($item) =>
                $item['qty'] * $item['price']
        );

        return view(
            'site.cart',
            compact(
                'cart',
                'total'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    */

    public function checkout(): View
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless(
            $user->email_verified_at,
            403,
            'Verifieer eerst je e-mailadres voordat je bestelt.'
        );

        $cart = Session::get(
            'cart',
            []
        );

        $total = collect($cart)->sum(
            fn ($item) =>
                $item['qty'] * $item['price']
        );

        return view(
            'site.checkout',
            compact(
                'cart',
                'total'
            )
        );
    }

    public function checkoutSubmit(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless(
            $user->email_verified_at,
            403,
            'Verifieer eerst je e-mailadres voordat je bestelt.'
        );

        $cart = Session::get(
            'cart',
            []
        );

        if (empty($cart)) {
            return redirect()
                ->route('cart')
                ->withErrors([
                    'cart' => 'Je winkelwagen is leeg.',
                ]);
        }

        $total = collect($cart)->sum(
            fn ($item) =>
                $item['qty'] * $item['price']
        );

        $orderNumber =
            'SD-' .
            now()->format('Ymd') .
            '-' .
            strtoupper(
                Str::random(6)
            );

        DB::table('orders')->insert([
            'order_number' => $orderNumber,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'items' => json_encode(
                array_values($cart),
                JSON_THROW_ON_ERROR
            ),
            'total' => $total,
            'status' => 'placed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $emailSent = $this->sendEmailSafely(
            $user->email,
            $user->name,
            'Bestelbevestiging ' . $orderNumber,
            'emails.order-confirmation',
            [
                'user' => $user,
                'orderNumber' => $orderNumber,
                'orderDate' => now()->format('d-m-Y H:i'),
                'items' => $cart,
                'total' => $total,
            ]
        );

        Session::forget('cart');

        if (! $emailSent) {
            return redirect()
                ->route('account')
                ->with(
                    'success',
                    'Bestelling ' .
                    $orderNumber .
                    ' is geplaatst. De bevestigingsmail kon niet worden verzonden.'
                );
        }

        return redirect()
            ->route('account')
            ->with(
                'success',
                'Bestelling ' .
                $orderNumber .
                ' is geplaatst.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Verificatie
    |--------------------------------------------------------------------------
    */

    public function verifyNotice(): View
    {
        return view('site.verify');
    }

    public function sendVerificationCode(
        Request $request
    ): RedirectResponse {
        $data = $request->validate([
            'email' => [
                'required',
                'email',
                'exists:users,email',
            ],
        ]);

        $email = strtolower(
            trim($data['email'])
        );

        $user = User::where(
            'email',
            $email
        )->firstOrFail();

        if ($user->email_verified_at) {
            return redirect()
                ->route('login')
                ->with(
                    'success',
                    'Je e-mailadres is al geverifieerd.'
                );
        }

        $this->createVerificationCode(
            $user
        );

        $emailSent = $this->sendVerificationEmail(
            $user,
            'Je verificatiecode voor Mashal Studio'
        );

        if (! $emailSent) {
            return redirect()
                ->route('verification.notice')
                ->withErrors([
                    'email' =>
                        'De verificatiemail kon niet worden verzonden. Probeer het opnieuw.',
                ])
                ->withInput();
        }

        return redirect()
            ->route('verification.notice')
            ->with(
                'success',
                'Een nieuwe verificatiecode is per e-mail verzonden.'
            );
    }

    public function verifyCode(
        Request $request
    ): RedirectResponse {
        $data = $request->validate([
            'email' => [
                'required',
                'email',
                'exists:users,email',
            ],
            'code' => [
                'required',
                'digits:6',
            ],
        ]);

        $email = strtolower(
            trim($data['email'])
        );

        $code = trim(
            $data['code']
        );

        $user = User::where(
            'email',
            $email
        )->firstOrFail();

        $record = DB::table(
            'email_verification_codes'
        )
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'code',
                $code
            )
            ->whereNull(
                'used_at'
            )
            ->first();

        if (
            ! $record ||
            now()->greaterThan(
                Carbon::parse(
                    $record->expires_at
                )
            )
        ) {
            return back()
                ->withErrors([
                    'code' => 'De code is ongeldig of verlopen.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($user, $record): void {
            $user->email_verified_at = now();
            $user->save();

            DB::table(
                'email_verification_codes'
            )
                ->where(
                    'id',
                    $record->id
                )
                ->update([
                    'used_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        $this->sendEmailSafely(
            $user->email,
            $user->name,
            'Je e-mailadres is geverifieerd - Mashal Studio',
            'emails.email-verified',
            [
                'user' => $user,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Registratie afronden
        |--------------------------------------------------------------------------
        |
        | Een geldige verificatiecode bewijst toegang tot het e-mailadres.
        | Daarom loggen we de gebruiker hier direct in.
        |
        | Dit voorkomt dat iemand na:
        | upload -> registratie -> verificatie
        | nóg een keer handmatig moet inloggen om zijn upload te claimen.
        |
        */

        Auth::login($user);

        $request->session()->regenerate();

        $user->forceFill([
            'login_provider' => $user->login_provider ?: 'password',
        ])->save();

        if ($this->hasPendingImage($request)) {
            $this->ensurePendingImageIntendedUrl($request);

            return redirect()
                ->route('images.claim')
                ->with(
                    'success',
                    'Je e-mailadres is geverifieerd. Je eerdere upload wordt nu automatisch geopend.'
                );
        }

        return redirect()
            ->route('home')
            ->with(
                'success',
                'Je e-mailadres is succesvol geverifieerd en je bent ingelogd.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Wachtwoord vergeten
    |--------------------------------------------------------------------------
    */

    public function forgotPassword(): View
    {
        return view(
            'site.forgot-password'
        );
    }

    public function sendResetLink(
        Request $request
    ): RedirectResponse {
        $data = $request->validate([
            'email' => [
                'required',
                'email',
                'exists:users,email',
            ],
        ]);

        $email = strtolower(
            trim($data['email'])
        );

        $user = User::where(
            'email',
            $email
        )->firstOrFail();

        $token = Str::random(64);

        DB::table(
            'password_reset_tokens'
        )->updateOrInsert(
            [
                'email' => $user->email,
            ],
            [
                'token' => hash(
                    'sha256',
                    $token
                ),
                'created_at' => now(),
            ]
        );

        $emailSent = $this->sendEmailSafely(
            $user->email,
            $user->name,
            'Wachtwoord herstellen - Mashal Studio',
            'emails.password-reset',
            [
                'user' => $user,
                'token' => $token,
            ]
        );

        if (! $emailSent) {
            DB::table(
                'password_reset_tokens'
            )
                ->where(
                    'email',
                    $user->email
                )
                ->delete();

            return redirect()
                ->route('password.request')
                ->withErrors([
                    'email' =>
                        'De resetmail kon niet worden verzonden. Probeer het later opnieuw.',
                ])
                ->withInput();
        }

        return redirect()
            ->route('password.request')
            ->with(
                'success',
                'Een resetlink is per e-mail verzonden.'
            );
    }

    public function showResetForm(
        string $token
    ): View|RedirectResponse {
        $hashedToken = hash(
            'sha256',
            $token
        );

        $record = DB::table(
            'password_reset_tokens'
        )
            ->where(
                'token',
                $hashedToken
            )
            ->first();

        if (
            ! $record ||
            Carbon::parse(
                $record->created_at
            )
                ->addMinutes(60)
                ->isPast()
        ) {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'email' =>
                        'Deze resetlink is verlopen of ongeldig.',
                ]);
        }

        return view(
            'site.reset-password',
            compact('token')
        );
    }

    public function resetPassword(
        Request $request,
        string $token
    ): RedirectResponse {
        $data = $request->validate([
            'email' => [
                'required',
                'email',
                'exists:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $email = strtolower(
            trim($data['email'])
        );

        $hashedToken = hash(
            'sha256',
            $token
        );

        $record = DB::table(
            'password_reset_tokens'
        )
            ->where(
                'email',
                $email
            )
            ->where(
                'token',
                $hashedToken
            )
            ->first();

        if (
            ! $record ||
            Carbon::parse(
                $record->created_at
            )
                ->addMinutes(60)
                ->isPast()
        ) {
            return back()
                ->withErrors([
                    'email' =>
                        'Deze resetlink is verlopen of ongeldig.',
                ])
                ->withInput();
        }

        $user = User::where(
            'email',
            $email
        )->firstOrFail();

        if (Hash::check(
            $data['password'],
            $user->password
        )) {
            return back()
                ->withErrors([
                    'password' =>
                        'Kies een ander wachtwoord dan je huidige wachtwoord.',
                ]);
        }

        $user->password = Hash::make(
            $data['password']
        );

        $user->save();

        DB::table(
            'password_reset_tokens'
        )
            ->where(
                'email',
                $email
            )
            ->delete();

        $this->sendEmailSafely(
            $user->email,
            $user->name,
            'Je Mashal Studio-wachtwoord is gewijzigd',
            'emails.password-changed',
            [
                'user' => $user,
            ]
        );

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Je wachtwoord is opnieuw ingesteld.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin dashboard
    |--------------------------------------------------------------------------
    */

    public function admin(): View
    {
        $this->ensureAdmin();

        $users = User::latest()
            ->get();

        $totalUsers = $users->count();

        $verifiedUsers = $users
            ->whereNotNull(
                'email_verified_at'
            )
            ->count();

        $adminUsers = $users
            ->where(
                'is_admin',
                true
            )
            ->count();

        $totalOrders = DB::table(
            'orders'
        )->count();

        return view(
            'admin.dashboard',
            compact(
                'users',
                'totalUsers',
                'verifiedUsers',
                'adminUsers',
                'totalOrders'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin gebruikers
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $this->ensureAdmin();

        $users = User::latest()
            ->get();

        return view(
            'users.index',
            compact('users')
        );
    }

    public function create(): View
    {
        $this->ensureAdmin();

        return view(
            'users.create'
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $this->ensureAdmin();

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'is_admin' => [
                'nullable',
                'boolean',
            ],
            'email_verified' => [
                'nullable',
                'boolean',
            ],
        ]);

        $user = User::create([
            'name' => trim(
                $data['name']
            ),
            'email' => strtolower(
                trim($data['email'])
            ),
            'password' => Hash::make(
                $data['password']
            ),
            'login_provider' => 'password',
            'is_admin' =>
                $request->boolean(
                    'is_admin'
                ),
            'email_verified_at' =>
                $request->boolean(
                    'email_verified'
                )
                    ? now()
                    : null,
        ]);

        $code = null;

        if (! $user->email_verified_at) {
            $this->createVerificationCode($user);

            $code = DB::table('email_verification_codes')
                ->where('user_id', $user->id)
                ->whereNull('used_at')
                ->latest('created_at')
                ->value('code');

            if ($code === null) {
                return redirect()
                    ->route('users.index')
                    ->with('success', 'De gebruiker is aangemaakt, maar de verificatiecode kon niet worden opgehaald. Er is geen welkomstmail verzonden.');
            }
        }

        $emailSent = $this->sendEmailSafely(
            $user->email,
            (string) $user->name,
            'Welkom bij Mashal Studio - Je account is aangemaakt',
            'emails.account-created',
            [
                'user' => $user,
                'code' => $code,
            ]
        );

        $message = 'Gebruiker ' . $user->name . ' is succesvol aangemaakt.';

        if (! $emailSent) {
            $message .= ' De welkomstmail kon niet worden verzonden.';
        }

        return redirect()
            ->route('users.index')
            ->with('success', $message);
    }

    public function edit(User $user): View
    {
        $this->ensureAdmin();

        return view(
            'users.edit',
            compact('user')
        );
    }

    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        $this->ensureAdmin();

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(
                    'users',
                    'email'
                )->ignore(
                    $user->id
                ),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'is_admin' => [
                'nullable',
                'boolean',
            ],
            'email_verified' => [
                'nullable',
                'boolean',
            ],
        ]);

        if (
            Auth::id() === $user->id &&
            ! $request->boolean(
                'is_admin'
            )
        ) {
            return back()
                ->withErrors([
                    'is_admin' =>
                        'Je kunt je eigen administratorrechten niet verwijderen.',
                ])
                ->withInput();
        }

        $oldName = $user->name;
        $oldEmail = $user->email;

        $newEmail = strtolower(
            trim($data['email'])
        );

        $emailChanged =
            strtolower($oldEmail) !==
            $newEmail;

        $user->name = trim(
            $data['name']
        );

        $user->email = $newEmail;

        $user->is_admin =
            $request->boolean(
                'is_admin'
            );

        if (
            $request->boolean(
                'email_verified'
            )
        ) {
            $user->email_verified_at =
                $user->email_verified_at
                ?? now();
        } else {
            $user->email_verified_at = null;
        }

        if (! empty(
            $data['password']
        )) {
            $user->password =
                Hash::make(
                    $data['password']
                );
        }

        if (
            $emailChanged &&
            ! $request->boolean(
                'email_verified'
            )
        ) {
            $user->email_verified_at = null;
        }

        $user->save();

        $verificationSent = true;

        if ($user->email_verified_at) {
            DB::table(
                'email_verification_codes'
            )
                ->where(
                    'user_id',
                    $user->id
                )
                ->delete();
        } elseif ($emailChanged) {
            $this->createVerificationCode(
                $user
            );

            $verificationSent =
                $this->sendVerificationEmail(
                    $user,
                    'Bevestig je e-mailadres - Mashal Studio'
                );
        }

        if ($emailChanged) {
            DB::table(
                'password_reset_tokens'
            )
                ->where(
                    'email',
                    $oldEmail
                )
                ->delete();
        }

        $notificationSent = $this->sendEmailSafely(
            $user->email,
            $user->name,
            'Je accountgegevens zijn bijgewerkt door een beheerder - Mashal Studio',
            'emails.account-updated',
            [
                'user' => $user,
                'oldName' => $oldName,
                'oldEmail' => $oldEmail,
                'emailChanged' => $emailChanged,
            ]
        );

        if (! $notificationSent || ! $verificationSent) {
            return redirect()
                ->route('users.index')
                ->with(
                    'success',
                    'De gegevens van ' .
                    $user->name .
                    ' zijn bijgewerkt, maar niet alle e-mails konden worden verzonden.'
                );
        }

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'De gegevens van ' .
                $user->name .
                ' zijn bijgewerkt.'
            );
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureAdmin();

        if (Auth::id() === $user->id) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'Je kunt je eigen administratoraccount niet verwijderen.'
                );
        }

        // Bewaar de ontvanger voordat het account wordt verwijderd.
        $name = (string) $user->name;
        $email = (string) $user->email;

        $profilePhoto = trim(
            (string) ($user->profile_photo ?? '')
        );

        DB::transaction(function () use ($user, $email) {
            DB::table('email_verification_codes')
                ->where('user_id', $user->id)
                ->delete();

            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();

            if (! $user->delete()) {
                throw new \RuntimeException(
                    'Het account kon niet worden verwijderd.'
                );
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Eventuele lokale profielfoto verwijderen
        |--------------------------------------------------------------------------
        */

        if ($profilePhoto !== '') {
            $this->deleteLocalProfilePhoto(
                $profilePhoto
            );
        }

        // De verwijdering is opgeslagen voordat de mail wordt verstuurd.
        $emailSent = $this->sendEmailSafely(
            $email,
            $name,
            'Je Mashal Studio-account is verwijderd',
            'emails.account-deleted',
            [
                'name' => $name,
            ]
        );

        $message = 'Gebruiker ' . $name . ' is verwijderd.';

        if (! $emailSent) {
            $message .= ' De bevestigingsmail kon niet worden verzonden.';
        }

        return redirect()
            ->route('users.index')
            ->with('success', $message);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Tijdelijke login-security browsercontext opruimen
    |--------------------------------------------------------------------------
    |
    | Dezelfde sessiesleutels worden gebruikt door SecurityController en
    | LoginSecurityService. Registratie zelf is geen login, dus na een
    | succesvolle registratie moet deze tijdelijke context worden verwijderd.
    |
    */

    /**
     * Controleer of deze browsersessie nog een upload heeft die geclaimd moet
     * worden.
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

    /**
     * Zorg dat Laravel na authenticatie altijd naar de claim-route wijst
     * wanneer er een pending upload bestaat.
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


    private function forgetLoginSecurityBrowserContext(
        Request $request
    ): void {
        if (! $request->hasSession()) {
            return;
        }

        $request->session()->forget([
            'login_security.browser_timezone',
            'login_security.latitude',
            'login_security.longitude',
            'login_security.location_accuracy',
            'login_security.location_permission',
            'login_security.context_captured_at',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Lokale profielfoto veilig verwijderen
    |--------------------------------------------------------------------------
    |
    | Externe Google/GitHub/Facebook avatar-URL's worden nooit verwijderd.
    |
    */

    private function deleteLocalProfilePhoto(
        ?string $profilePhoto
    ): void {
        $profilePhoto = trim(
            (string) $profilePhoto
        );

        if ($profilePhoto === '') {
            return;
        }

        if (
            Str::startsWith(
                $profilePhoto,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return;
        }

        try {
            Storage::disk('public')
                ->delete(
                    $profilePhoto
                );
        } catch (Throwable $exception) {
            report($exception);
        }
    }


    private function ensureAdmin(): void
    {
        abort_unless(
            Auth::check() &&
            (bool) Auth::user()->is_admin,
            403,
            'Je hebt geen toestemming om deze beheerpagina te bekijken.'
        );
    }

    private function createVerificationCode(
        User $user
    ): void {
        $code = random_int(
            100000,
            999999
        );

        DB::table(
            'email_verification_codes'
        )
            ->where(
                'user_id',
                $user->id
            )
            ->delete();

        DB::table(
            'email_verification_codes'
        )->insert([
            'user_id' => $user->id,
            'code' => (string) $code,
            'expires_at' =>
                now()->addMinutes(15),
            'used_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function sendVerificationEmail(
        User $user,
        string $subject
    ): bool {
        $record = DB::table(
            'email_verification_codes'
        )
            ->where(
                'user_id',
                $user->id
            )
            ->whereNull(
                'used_at'
            )
            ->latest(
                'created_at'
            )
            ->first();

        if (! $record) {
            return false;
        }

        return $this->sendEmailSafely(
            $user->email,
            $user->name,
            $subject,
            'emails.verification-code',
            [
                'user' => $user,
                'code' => $record->code,
            ]
        );
    }

    private function sendEmailSafely(
        string $toEmail,
        string $toName,
        string $subject,
        string $view,
        array $data = []
    ): bool {
        try {
            $this->brevoMail->send(
                $toEmail,
                $toName,
                $subject,
                $view,
                $data
            );

            return true;
        } catch (Throwable $exception) {
            logger()->error('Mashal Studio e-mail kon niet worden verzonden.', [
                'recipient' => $toEmail,
                'subject' => $subject,
                'view' => $view,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            report($exception);

            return false;
        }
    }
}
