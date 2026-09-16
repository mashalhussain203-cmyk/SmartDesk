<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BrevoMailService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly BrevoMailService $brevoMail
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Catalogus
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
        ]);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'is_admin' => false,
            'email_verified_at' => null,
        ]);

        $this->createVerificationCode($user);

        $mailSent = $this->sendVerificationEmail(
            $user,
            'Je verificatiecode voor SmartDesk'
        );

        return redirect()
            ->route('verification.notice')
            ->with(
                $mailSent ? 'success' : 'error',
                $mailSent
                    ? 'Je account is aangemaakt. Controleer je e-mail voor de verificatiecode.'
                    : 'Je account is aangemaakt, maar de verificatiemail kon niet worden verzonden.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(): View
    {
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

        if (! Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors([
                    'email' => 'Het e-mailadres of wachtwoord is niet correct.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

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
    | Account
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
    | Eigen account wijzigen
    |--------------------------------------------------------------------------
    */

    public function updateAccount(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

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
        ]);

        $newName = trim($data['name']);
        $newEmail = strtolower(trim($data['email']));

        $oldName = $user->name;
        $oldEmail = $user->email;

        $nameChanged = $oldName !== $newName;
        $emailChanged = strtolower($oldEmail) !== $newEmail;

        if (! $nameChanged && ! $emailChanged) {
            return redirect()
                ->route('account')
                ->with(
                    'success',
                    'Er zijn geen wijzigingen om op te slaan.'
                );
        }

        $user->name = $newName;

        if ($emailChanged) {
            $user->email = $newEmail;
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            DB::table('password_reset_tokens')
                ->where('email', $oldEmail)
                ->delete();

            $this->createVerificationCode($user);

            $verificationSent = $this->sendVerificationEmail(
                $user,
                'Bevestig je nieuwe e-mailadres - SmartDesk'
            );

            $oldAddressMailSent = $this->sendEmailSafely(
                $oldEmail,
                $oldName,
                'Je SmartDesk-e-mailadres is gewijzigd',
                'emails.email-changed',
                [
                    'user' => $user,
                    'oldEmail' => $oldEmail,
                    'newEmail' => $newEmail,
                ]
            );

            $accountMailSent = $this->sendEmailSafely(
                $user->email,
                $user->name,
                'Je SmartDesk-accountgegevens zijn gewijzigd',
                'emails.account-updated',
                [
                    'user' => $user,
                    'oldName' => $oldName,
                    'oldEmail' => $oldEmail,
                    'emailChanged' => true,
                ]
            );

            return redirect()
                ->route('verification.notice')
                ->with(
                    ($verificationSent && $oldAddressMailSent && $accountMailSent)
                        ? 'success'
                        : 'error',
                    ($verificationSent && $oldAddressMailSent && $accountMailSent)
                        ? 'Je nieuwe e-mailadres is opgeslagen. Controleer je nieuwe e-mailadres voor de verificatiecode.'
                        : 'Je gegevens zijn opgeslagen, maar één of meer e-mails konden niet worden verzonden.'
                );
        }

        $mailSent = $this->sendEmailSafely(
            $user->email,
            $user->name,
            'Je SmartDesk-accountgegevens zijn gewijzigd',
            'emails.account-updated',
            [
                'user' => $user,
                'oldName' => $oldName,
                'oldEmail' => $oldEmail,
                'emailChanged' => false,
            ]
        );

        return redirect()
            ->route('account')
            ->with(
                $mailSent ? 'success' : 'error',
                $mailSent
                    ? 'Je accountgegevens zijn opgeslagen.'
                    : 'Je accountgegevens zijn opgeslagen, maar de bevestigingsmail kon niet worden verzonden.'
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

        $mailSent = $this->sendEmailSafely(
            $user->email,
            $user->name,
            'Je SmartDesk-wachtwoord is gewijzigd',
            'emails.password-changed',
            [
                'user' => $user,
            ]
        );

        return redirect()
            ->route('account')
            ->with(
                $mailSent ? 'success' : 'error',
                $mailSent
                    ? 'Je wachtwoord is gewijzigd. We hebben hiervan een bevestiging naar je e-mailadres gestuurd.'
                    : 'Je wachtwoord is gewijzigd, maar de bevestigingsmail kon niet worden verzonden.'
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
            'qty' => ($cart[$car['id']]['qty'] ?? 0) + 1,
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

        $mailSent = $this->sendEmailSafely(
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

        return redirect()
            ->route('account')
            ->with(
                $mailSent ? 'success' : 'error',
                $mailSent
                    ? 'Bestelling ' . $orderNumber . ' is geplaatst. De bevestiging is naar ' . $user->email . ' gestuurd.'
                    : 'Bestelling ' . $orderNumber . ' is geplaatst, maar de bevestigingsmail kon niet worden verzonden.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | E-mailverificatie
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

        $mailSent = $this->sendVerificationEmail(
            $user,
            'Je verificatiecode voor SmartDesk'
        );

        return redirect()
            ->route('verification.notice')
            ->with(
                $mailSent ? 'success' : 'error',
                $mailSent
                    ? 'Een nieuwe verificatiecode is per e-mail verzonden.'
                    : 'Er is een nieuwe verificatiecode aangemaakt, maar de e-mail kon niet worden verzonden.'
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

        if ($user->email_verified_at) {
            return redirect()
                ->route('login')
                ->with(
                    'success',
                    'Je e-mailadres is al geverifieerd.'
                );
        }

        $record = DB::table(
            'email_verification_codes'
        )
            ->where('user_id', $user->id)
            ->where('code', $code)
            ->whereNull('used_at')
            ->latest('created_at')
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
                    'code' =>
                        'De code is ongeldig of verlopen.',
                ])
                ->withInput();
        }

        $user->email_verified_at = now();
        $user->save();

        DB::table(
            'email_verification_codes'
        )
            ->where(
                'user_id',
                $user->id
            )
            ->delete();

        $mailSent = $this->sendEmailSafely(
            $user->email,
            $user->name,
            'Je e-mailadres is geverifieerd - SmartDesk',
            'emails.email-verified',
            [
                'user' => $user,
            ]
        );

        return redirect()
            ->route('login')
            ->with(
                $mailSent ? 'success' : 'error',
                $mailSent
                    ? 'Je e-mailadres is succesvol geverifieerd.'
                    : 'Je e-mailadres is geverifieerd, maar de bevestigingsmail kon niet worden verzonden.'
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

        $mailSent = $this->sendEmailSafely(
            $user->email,
            $user->name,
            'Wachtwoord herstellen - SmartDesk',
            'emails.password-reset',
            [
                'user' => $user,
                'token' => $token,
            ]
        );

        return redirect()
            ->route('password.request')
            ->with(
                $mailSent ? 'success' : 'error',
                $mailSent
                    ? 'Een resetlink is per e-mail verzonden.'
                    : 'De resetlink is aangemaakt, maar de e-mail kon niet worden verzonden.'
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
            ->where('email', $email)
            ->where('token', $hashedToken)
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

        $mailSent = $this->sendEmailSafely(
            $user->email,
            $user->name,
            'Je SmartDesk-wachtwoord is gewijzigd',
            'emails.password-changed',
            [
                'user' => $user,
            ]
        );

        return redirect()
            ->route('login')
            ->with(
                $mailSent ? 'success' : 'error',
                $mailSent
                    ? 'Je wachtwoord is opnieuw ingesteld.'
                    : 'Je wachtwoord is opnieuw ingesteld, maar de bevestigingsmail kon niet worden verzonden.'
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

        $users = User::latest()->get();

        $totalUsers = $users->count();

        $verifiedUsers = $users
            ->whereNotNull('email_verified_at')
            ->count();

        $adminUsers = $users
            ->where('is_admin', true)
            ->count();

        $totalOrders = DB::table('orders')
            ->count();

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

    /*
    |--------------------------------------------------------------------------
    | Admin gebruiker aanmaken
    |--------------------------------------------------------------------------
    */

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
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'is_admin' => $request->boolean('is_admin'),
            'email_verified_at' =>
                $request->boolean('email_verified')
                    ? now()
                    : null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Altijd account-aanmaakmail
        |--------------------------------------------------------------------------
        */

        $accountMailSent = $this->sendEmailSafely(
            $user->email,
            $user->name,
            'Je SmartDesk-account is aangemaakt',
            'emails.admin-account-created',
            [
                'user' => $user,
                'isAdmin' => (bool) $user->is_admin,
                'isVerified' => $user->email_verified_at !== null,
                'createdAt' => now()->format('d-m-Y H:i'),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Indien niet geverifieerd ook verificatiecode sturen
        |--------------------------------------------------------------------------
        */

        $verificationMailSent = true;

        if (! $user->email_verified_at) {
            $this->createVerificationCode(
                $user
            );

            $verificationMailSent = $this->sendVerificationEmail(
                $user,
                'Verifieer je SmartDesk-account'
            );
        }

        if (! $accountMailSent || ! $verificationMailSent) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'Gebruiker ' .
                    $user->name .
                    ' is aangemaakt, maar één of meer e-mails konden niet worden verzonden.'
                );
        }

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Gebruiker ' .
                $user->name .
                ' is aangemaakt en per e-mail geïnformeerd.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin gebruiker bewerken
    |--------------------------------------------------------------------------
    */

    public function edit(User $user): View
    {
        $this->ensureAdmin();

        return view(
            'users.edit',
            compact('user')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin gebruiker bijwerken
    |--------------------------------------------------------------------------
    */

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
                Rule::unique('users', 'email')
                    ->ignore($user->id),
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

        /*
        |--------------------------------------------------------------------------
        | Eigen adminrechten beschermen
        |--------------------------------------------------------------------------
        */

        if (
            Auth::id() === $user->id &&
            ! $request->boolean('is_admin')
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
        $oldIsAdmin = (bool) $user->is_admin;
        $oldVerified = $user->email_verified_at !== null;

        $newName = trim(
            $data['name']
        );

        $newEmail = strtolower(
            trim($data['email'])
        );

        $newIsAdmin = $request->boolean(
            'is_admin'
        );

        $newVerified = $request->boolean(
            'email_verified'
        );

        $nameChanged =
            $oldName !== $newName;

        $emailChanged =
            strtolower($oldEmail) !== $newEmail;

        $adminChanged =
            $oldIsAdmin !== $newIsAdmin;

        $verifiedChanged =
            $oldVerified !== $newVerified;

        $passwordChanged =
            filled($data['password'] ?? null);

        if (
            ! $nameChanged &&
            ! $emailChanged &&
            ! $adminChanged &&
            ! $verifiedChanged &&
            ! $passwordChanged
        ) {
            return redirect()
                ->route('users.index')
                ->with(
                    'success',
                    'Er waren geen wijzigingen om op te slaan.'
                );
        }

        if (
            $passwordChanged &&
            Hash::check(
                $data['password'],
                $user->password
            )
        ) {
            return back()
                ->withErrors([
                    'password' =>
                        'Het nieuwe wachtwoord moet verschillen van het huidige wachtwoord.',
                ])
                ->withInput();
        }

        $user->name = $newName;
        $user->email = $newEmail;
        $user->is_admin = $newIsAdmin;

        if ($newVerified) {
            $user->email_verified_at =
                $user->email_verified_at ?? now();
        } else {
            $user->email_verified_at = null;
        }

        if ($passwordChanged) {
            $user->password = Hash::make(
                $data['password']
            );
        }

        if (
            $emailChanged &&
            ! $newVerified
        ) {
            $user->email_verified_at = null;
        }

        $user->save();

        $actualNewVerified =
            $user->email_verified_at !== null;

        /*
        |--------------------------------------------------------------------------
        | Verificatiecodes
        |--------------------------------------------------------------------------
        */

        $verificationMailSent = true;

        if ($actualNewVerified) {
            DB::table(
                'email_verification_codes'
            )
                ->where(
                    'user_id',
                    $user->id
                )
                ->delete();
        } elseif (
            $emailChanged ||
            $verifiedChanged
        ) {
            $this->createVerificationCode(
                $user
            );

            $verificationMailSent = $this->sendVerificationEmail(
                $user,
                'Verifieer je e-mailadres - SmartDesk'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Oude resetlinks verwijderen
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Admin wijzigingsmail
        |--------------------------------------------------------------------------
        */

        $mailData = [
            'user' => $user,

            'oldName' => $oldName,
            'newName' => $newName,

            'oldEmail' => $oldEmail,
            'newEmail' => $newEmail,

            'nameChanged' => $nameChanged,
            'emailChanged' => $emailChanged,

            'adminChanged' => $adminChanged,
            'oldIsAdmin' => $oldIsAdmin,
            'newIsAdmin' => $newIsAdmin,

            'verifiedChanged' => $verifiedChanged,
            'oldVerified' => $oldVerified,
            'newVerified' => $actualNewVerified,

            'passwordChanged' => $passwordChanged,

            'changedAt' => now()->format(
                'd-m-Y H:i'
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | Mail naar huidige / nieuwe adres
        |--------------------------------------------------------------------------
        */

        $primaryMailSent = $this->sendEmailSafely(
            $user->email,
            $user->name,
            'Je SmartDesk-account is gewijzigd',
            'emails.admin-account-updated',
            $mailData
        );

        /*
        |--------------------------------------------------------------------------
        | Bij gewijzigd e-mailadres ook oude adres waarschuwen
        |--------------------------------------------------------------------------
        */

        $oldAddressMailSent = true;

        if ($emailChanged) {
            $oldAddressMailSent = $this->sendEmailSafely(
                $oldEmail,
                $oldName,
                'Beveiligingsmelding: je SmartDesk-account is gewijzigd',
                'emails.admin-account-updated',
                $mailData
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Extra wachtwoordbeveiligingsmail
        |--------------------------------------------------------------------------
        */

        $passwordMailSent = true;

        if ($passwordChanged) {
            $passwordMailSent = $this->sendEmailSafely(
                $user->email,
                $user->name,
                'Je SmartDesk-wachtwoord is gewijzigd',
                'emails.password-changed',
                [
                    'user' => $user,
                ]
            );
        }

        if (
            ! $verificationMailSent ||
            ! $primaryMailSent ||
            ! $oldAddressMailSent ||
            ! $passwordMailSent
        ) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'De gegevens van ' .
                    $user->name .
                    ' zijn opgeslagen, maar één of meer e-mails konden niet worden verzonden.'
                );
        }

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'De gegevens van ' .
                $user->name .
                ' zijn bijgewerkt en de gebruiker is per e-mail geïnformeerd.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin gebruiker verwijderen
    |--------------------------------------------------------------------------
    */

    public function destroy(
        User $user
    ): RedirectResponse {
        $this->ensureAdmin();

        if (
            Auth::id() === $user->id
        ) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'Je kunt je eigen administratoraccount niet verwijderen.'
                );
        }

        $deletedUserName = $user->name;
        $deletedUserEmail = $user->email;
        $wasAdmin = (bool) $user->is_admin;

        /*
        |--------------------------------------------------------------------------
        | Gebruiker vóór verwijderen informeren
        |--------------------------------------------------------------------------
        */

        $deletionMailSent = $this->sendEmailSafely(
            $deletedUserEmail,
            $deletedUserName,
            'Je SmartDesk-account is verwijderd',
            'emails.account-deleted',
            [
                'user' => $user,
                'name' => $deletedUserName,
                'email' => $deletedUserEmail,
                'wasAdmin' => $wasAdmin,
                'deletedAt' => now()->format(
                    'd-m-Y H:i'
                ),
            ]
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
            'password_reset_tokens'
        )
            ->where(
                'email',
                $deletedUserEmail
            )
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Bestellingen worden bewust behouden
        |--------------------------------------------------------------------------
        */

        $user->delete();

        if (! $deletionMailSent) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'Gebruiker ' .
                    $deletedUserName .
                    ' is verwijderd, maar de bevestigingsmail kon niet worden verzonden.'
                );
        }

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Gebruiker ' .
                $deletedUserName .
                ' is verwijderd en heeft hiervan een e-mail ontvangen.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin controleren
    |--------------------------------------------------------------------------
    */

    private function ensureAdmin(): void
    {
        abort_unless(
            Auth::check() &&
            (bool) Auth::user()->is_admin,
            403,
            'Je hebt geen toestemming om deze beheerpagina te bekijken.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Verificatiecode maken
    |--------------------------------------------------------------------------
    */

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
            'expires_at' => now()->addMinutes(15),
            'used_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Brevo veilig versturen
    |--------------------------------------------------------------------------
    |
    | Mailproblemen mogen opgeslagen wijzigingen niet veranderen in een
    | HTTP 500. De fout wordt wel gelogd via Laravel.
    |
    */

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
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Verificatiemail
    |--------------------------------------------------------------------------
    */

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
}
