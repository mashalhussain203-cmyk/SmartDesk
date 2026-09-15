<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
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
                'summary' => 'Sportieve stijl voor het dagelijks gebruik en de weekendtrip.',
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
    | Registratie
    |--------------------------------------------------------------------------
    */

    public function register(): View
    {
        return view('site.register');
    }

    public function registerSubmit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        /*
        |--------------------------------------------------------------------------
        | Verificatiecode aanmaken
        |--------------------------------------------------------------------------
        */

        $code = random_int(100000, 999999);

        DB::table('email_verification_codes')
            ->where('user_id', $user->id)
            ->delete();

        DB::table('email_verification_codes')->insert([
            'user_id' => $user->id,
            'code' => (string) $code,
            'expires_at' => now()->addMinutes(15),
            'used_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verificatiemail
        |--------------------------------------------------------------------------
        */

        Mail::send(
            'emails.verification-code',
            [
                'user' => $user,
                'code' => $code,
            ],
            function ($message) use ($user) {
                $message->to($user->email, $user->name);
                $message->subject('Je verificatiecode voor SmartDesk');
            }
        );

        return redirect()
            ->route('verification.notice')
            ->with(
                'success',
                'Account aangemaakt. Controleer je e-mail voor de verificatiecode.'
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
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()
                ->intended(route('home'))
                ->with('success', 'Je bent ingelogd.');
        }

        return back()
            ->withErrors([
                'email' => 'Ongeldige inloggegevens.',
            ])
            ->onlyInput('email');
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

        return redirect()->route('home');
    }

    /*
    |--------------------------------------------------------------------------
    | Mijn account
    |--------------------------------------------------------------------------
    */

    public function account(): View
    {
        $user = Auth::user();

        $orders = DB::table('orders')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('site.account', compact('user', 'orders'));
    }

    /*
    |--------------------------------------------------------------------------
    | Accountgegevens wijzigen
    |--------------------------------------------------------------------------
    */

    public function updateAccount(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
        ]);

        $oldName = $user->name;
        $oldEmail = $user->email;

        $nameChanged = $oldName !== $data['name'];
        $emailChanged = strtolower($oldEmail) !== strtolower($data['email']);

        /*
        |--------------------------------------------------------------------------
        | Naam opslaan
        |--------------------------------------------------------------------------
        */

        $user->name = $data['name'];

        /*
        |--------------------------------------------------------------------------
        | E-mailadres wijzigen
        |--------------------------------------------------------------------------
        |
        | Bij een nieuw e-mailadres wordt de verificatiestatus verwijderd.
        | De klant moet het nieuwe adres opnieuw verifiëren.
        |
        */

        if ($emailChanged) {
            $user->email = $data['email'];
            $user->email_verified_at = null;
        }

        $user->save();

        /*
        |--------------------------------------------------------------------------
        | E-mailadres gewijzigd
        |--------------------------------------------------------------------------
        */

        if ($emailChanged) {
            $code = random_int(100000, 999999);

            DB::table('email_verification_codes')
                ->where('user_id', $user->id)
                ->delete();

            DB::table('email_verification_codes')->insert([
                'user_id' => $user->id,
                'code' => (string) $code,
                'expires_at' => now()->addMinutes(15),
                'used_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            | Verificatiecode naar het NIEUWE e-mailadres
            */

            Mail::send(
                'emails.verification-code',
                [
                    'user' => $user,
                    'code' => $code,
                ],
                function ($message) use ($user) {
                    $message->to($user->email, $user->name);
                    $message->subject(
                        'Bevestig je nieuwe e-mailadres - SmartDesk'
                    );
                }
            );

            /*
            | Bevestigingsmail naar het oude adres
            */

            if ($oldEmail !== $user->email) {
                Mail::send(
                    'emails.email-changed',
                    [
                        'user' => $user,
                        'oldEmail' => $oldEmail,
                        'newEmail' => $user->email,
                    ],
                    function ($message) use ($oldEmail, $oldName) {
                        $message->to($oldEmail, $oldName);
                        $message->subject(
                            'Je SmartDesk-e-mailadres is gewijzigd'
                        );
                    }
                );
            }

            return redirect()
                ->route('verification.notice')
                ->with(
                    'success',
                    'Je nieuwe e-mailadres is opgeslagen. We hebben een verificatiecode naar je nieuwe e-mailadres gestuurd.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Alleen naam gewijzigd
        |--------------------------------------------------------------------------
        */

        if ($nameChanged) {
            Mail::send(
                'emails.account-updated',
                [
                    'user' => $user,
                    'oldName' => $oldName,
                ],
                function ($message) use ($user) {
                    $message->to($user->email, $user->name);
                    $message->subject(
                        'Je SmartDesk-accountgegevens zijn gewijzigd'
                    );
                }
            );

            return redirect()
                ->route('account')
                ->with(
                    'success',
                    'Je naam is opgeslagen. We hebben een bevestiging naar je e-mailadres gestuurd.'
                );
        }

        return redirect()
            ->route('account')
            ->with('success', 'Je gegevens zijn opgeslagen.');
    }

    /*
    |--------------------------------------------------------------------------
    | Wachtwoord wijzigen terwijl klant is ingelogd
    |--------------------------------------------------------------------------
    */

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Huidig wachtwoord controleren
        |--------------------------------------------------------------------------
        */

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Je huidige wachtwoord is niet correct.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Nieuw wachtwoord opslaan
        |--------------------------------------------------------------------------
        */

        $user->password = Hash::make($data['password']);
        $user->save();

        /*
        |--------------------------------------------------------------------------
        | Beveiligingsmail
        |--------------------------------------------------------------------------
        */

        Mail::send(
            'emails.password-changed',
            [
                'user' => $user,
            ],
            function ($message) use ($user) {
                $message->to($user->email, $user->name);
                $message->subject(
                    'Je SmartDesk-wachtwoord is gewijzigd'
                );
            }
        );

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

        return view('site.catalog', compact('cars'));
    }

    /*
    |--------------------------------------------------------------------------
    | Auto bekijken
    |--------------------------------------------------------------------------
    */

    public function car(string $id): View
    {
        $cars = $this->catalogCars();

        $car = collect($cars)->firstWhere('id', (int) $id);

        abort_if(! $car, 404);

        return view('site.product', compact('car'));
    }

    /*
    |--------------------------------------------------------------------------
    | Winkelwagen
    |--------------------------------------------------------------------------
    */

    public function addToCart(string $id): RedirectResponse
    {
        $cars = $this->catalogCars();

        $car = collect($cars)->firstWhere('id', (int) $id);

        abort_if(! $car, 404);

        $cart = Session::get('cart', []);

        $cart[$car['id']] = [
            'id' => $car['id'],
            'brand' => $car['brand'],
            'model' => $car['model'],
            'price' => $car['price'],
            'image' => $car['image'],
            'qty' => ($cart[$car['id']]['qty'] ?? 0) + 1,
        ];

        Session::put('cart', $cart);

        return redirect()
            ->route('cart')
            ->with(
                'success',
                $car['brand'] . ' ' . $car['model'] . ' toegevoegd aan je wagen.'
            );
    }

    public function cart(): View
    {
        $cart = Session::get('cart', []);

        $total = collect($cart)->sum(
            fn ($item) => $item['qty'] * $item['price']
        );

        return view('site.cart', compact('cart', 'total'));
    }

    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    */

    public function checkout(): View
    {
        abort_unless(
            Auth::user()->email_verified_at,
            403,
            'Verifieer eerst je e-mailadres voordat je bestelt.'
        );

        $cart = Session::get('cart', []);

        $total = collect($cart)->sum(
            fn ($item) => $item['qty'] * $item['price']
        );

        return view('site.checkout', compact('cart', 'total'));
    }

    /*
    |--------------------------------------------------------------------------
    | Bestelling plaatsen
    |--------------------------------------------------------------------------
    */

    public function checkoutSubmit(Request $request): RedirectResponse
    {
        $user = Auth::user();

        abort_unless(
            $user->email_verified_at,
            403,
            'Verifieer eerst je e-mailadres voordat je bestelt.'
        );

        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart')
                ->withErrors([
                    'cart' => 'Je winkelwagen is leeg.',
                ]);
        }

        $total = collect($cart)->sum(
            fn ($item) => $item['qty'] * $item['price']
        );

        $orderNumber =
            'SD-' .
            now()->format('Ymd') .
            '-' .
            strtoupper(Str::random(6));

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

        /*
        |--------------------------------------------------------------------------
        | Bestelbevestiging
        |--------------------------------------------------------------------------
        */

        Mail::send(
            'emails.order-confirmation',
            [
                'user' => $user,
                'orderNumber' => $orderNumber,
                'orderDate' => now()->format('d-m-Y H:i'),
                'items' => $cart,
                'total' => $total,
            ],
            function ($message) use ($user) {
                $message->to($user->email, $user->name);
                $message->subject('Bestelbevestiging SmartDesk');
            }
        );

        Session::forget('cart');

        return redirect()
            ->route('account')
            ->with(
                'success',
                'Bestelling ' .
                $orderNumber .
                ' geplaatst. De bevestiging is naar ' .
                $user->email .
                ' gestuurd.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | E-mail verificatie
    |--------------------------------------------------------------------------
    */

    public function verifyNotice(): View
    {
        return view('site.verify');
    }

    public function sendVerificationCode(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'exists:users,email',
            ],
        ]);

        $user = User::where(
            'email',
            $request->email
        )->first();

        $code = random_int(100000, 999999);

        DB::table('email_verification_codes')
            ->where('user_id', $user->id)
            ->delete();

        DB::table('email_verification_codes')->insert([
            'user_id' => $user->id,
            'code' => (string) $code,
            'expires_at' => now()->addMinutes(15),
            'used_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Mail::send(
            'emails.verification-code',
            [
                'user' => $user,
                'code' => $code,
            ],
            function ($message) use ($user) {
                $message->to($user->email, $user->name);
                $message->subject(
                    'Je verificatiecode voor SmartDesk'
                );
            }
        );

        return redirect()
            ->route('verification.notice')
            ->with(
                'success',
                'Een verificatiecode is per e-mail verzonden.'
            );
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate([
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

        $user = User::where(
            'email',
            $request->email
        )->first();

        $record = DB::table('email_verification_codes')
            ->where('user_id', $user->id)
            ->where('code', $request->code)
            ->whereNull('used_at')
            ->first();

        if (! $record || now()->greaterThan($record->expires_at)) {
            return back()
                ->withErrors([
                    'code' => 'De code is ongeldig of verlopen.',
                ])
                ->onlyInput('email');
        }

        $user->email_verified_at = now();
        $user->save();

        DB::table('email_verification_codes')
            ->where('id', $record->id)
            ->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Bevestiging dat e-mail succesvol is geverifieerd
        |--------------------------------------------------------------------------
        */

        Mail::send(
            'emails.email-verified',
            [
                'user' => $user,
            ],
            function ($message) use ($user) {
                $message->to($user->email, $user->name);
                $message->subject(
                    'Je e-mailadres is geverifieerd - SmartDesk'
                );
            }
        );

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Je e-mailadres is succesvol geverifieerd. Je hebt hiervan ook een bevestiging per e-mail ontvangen.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Wachtwoord vergeten
    |--------------------------------------------------------------------------
    */

    public function forgotPassword(): View
    {
        return view('site.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'exists:users,email',
            ],
        ]);

        $user = User::where(
            'email',
            $request->email
        )->first();

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            [
                'email' => $user->email,
            ],
            [
                'email' => $user->email,
                'token' => $token,
                'created_at' => now(),
            ]
        );

        Mail::send(
            'emails.password-reset',
            [
                'user' => $user,
                'token' => $token,
            ],
            function ($message) use ($user) {
                $message->to($user->email, $user->name);
                $message->subject(
                    'Wachtwoord herstellen - SmartDesk'
                );
            }
        );

        return redirect()
            ->route('password.request')
            ->with(
                'success',
                'Een resetlink is per e-mail verzonden.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Resetformulier
    |--------------------------------------------------------------------------
    */

    public function showResetForm(
        string $token
    ): View|RedirectResponse {
        $record = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->first();

        if (
            ! $record ||
            now()->diffInMinutes($record->created_at) > 60
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

    /*
    |--------------------------------------------------------------------------
    | Wachtwoord resetten
    |--------------------------------------------------------------------------
    */

    public function resetPassword(
        Request $request,
        string $token
    ): RedirectResponse {
        $request->validate([
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

        $record = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->where('email', $request->email)
            ->first();

        if (
            ! $record ||
            now()->diffInMinutes($record->created_at) > 60
        ) {
            return back()
                ->withErrors([
                    'email' =>
                        'Deze resetlink is verlopen of ongeldig.',
                ]);
        }

        $user = User::where(
            'email',
            $request->email
        )->first();

        $user->password = Hash::make(
            $request->password
        );

        $user->save();

        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Beveiligingsmail na reset
        |--------------------------------------------------------------------------
        */

        Mail::send(
            'emails.password-changed',
            [
                'user' => $user,
            ],
            function ($message) use ($user) {
                $message->to($user->email, $user->name);
                $message->subject(
                    'Je SmartDesk-wachtwoord is gewijzigd'
                );
            }
        );

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Je wachtwoord is opnieuw ingesteld. We hebben een bevestiging naar je e-mailadres gestuurd.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */

    public function admin(): View
    {
        $users = User::latest()->get();

        return view(
            'admin.dashboard',
            compact('users')
        );
    }

    public function index(): View
    {
        $users = User::latest()->get();

        return view(
            'users.index',
            compact('users')
        );
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
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
                'unique:users',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $data['password'] = Hash::make(
            $data['password']
        );

        User::create($data);

        return redirect()
            ->route('admin.dashboard')
            ->with(
                'success',
                'Gebruiker succesvol opgeslagen.'
            );
    }
}
