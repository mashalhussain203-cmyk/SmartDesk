<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Auto catalogus
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

            'email' => strtolower(
                trim($data['email'])
            ),

            'password' => Hash::make(
                $data['password']
            ),

            'is_admin' => false,

            'email_verified_at' => null,
        ]);


        $this->createVerificationCode($user);


        $this->sendVerificationEmail(
            $user,
            'Je verificatiecode voor SmartDesk'
        );


        return redirect()
            ->route('verification.notice')
            ->with(
                'success',
                'Je account is aangemaakt. Controleer je e-mail voor de verificatiecode.'
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


        $remember = $request->boolean('remember');


        if (! Auth::attempt(
            $credentials,
            $remember
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
                ->intended(
                    route('admin.dashboard')
                )
                ->with(
                    'success',
                    'Welkom terug, ' . $user->name . '.'
                );
        }


        return redirect()
            ->intended(
                route('home')
            )
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
            ->where(
                'user_id',
                $user->id
            )
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
        ]);


        $newName = trim(
            $data['name']
        );


        $newEmail = strtolower(
            trim($data['email'])
        );


        $oldName = $user->name;

        $oldEmail = $user->email;


        $nameChanged =
            $oldName !== $newName;


        $emailChanged =
            strtolower($oldEmail) !==
            $newEmail;


        if (
            ! $nameChanged &&
            ! $emailChanged
        ) {
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


        /*
        |--------------------------------------------------------------------------
        | E-mailadres gewijzigd
        |--------------------------------------------------------------------------
        */

        if ($emailChanged) {
            $this->createVerificationCode(
                $user
            );


            $this->sendVerificationEmail(
                $user,
                'Bevestig je nieuwe e-mailadres - SmartDesk'
            );


            /*
            |--------------------------------------------------------------------------
            | Beveiligingsmail naar oude adres
            |--------------------------------------------------------------------------
            */

            Mail::send(
                'emails.email-changed',
                [
                    'user' => $user,
                    'oldEmail' => $oldEmail,
                    'newEmail' => $newEmail,
                ],
                function ($message) use (
                    $oldEmail,
                    $oldName
                ) {
                    $message->to(
                        $oldEmail,
                        $oldName
                    );

                    $message->subject(
                        'Je SmartDesk-e-mailadres is gewijzigd'
                    );
                }
            );


            /*
            |--------------------------------------------------------------------------
            | Accountwijziging naar nieuwe adres
            |--------------------------------------------------------------------------
            */

            Mail::send(
                'emails.account-updated',
                [
                    'user' => $user,
                    'oldName' => $oldName,
                    'oldEmail' => $oldEmail,
                    'emailChanged' => true,
                ],
                function ($message) use ($user) {
                    $message->to(
                        $user->email,
                        $user->name
                    );

                    $message->subject(
                        'Je SmartDesk-accountgegevens zijn gewijzigd'
                    );
                }
            );


            /*
            |--------------------------------------------------------------------------
            | Oude resetlinks ongeldig maken
            |--------------------------------------------------------------------------
            */

            DB::table(
                'password_reset_tokens'
            )
                ->where(
                    'email',
                    $oldEmail
                )
                ->delete();


            return redirect()
                ->route('verification.notice')
                ->with(
                    'success',
                    'Je nieuwe e-mailadres is opgeslagen. Controleer het nieuwe e-mailadres voor de verificatiecode.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Alleen naam gewijzigd
        |--------------------------------------------------------------------------
        */

        Mail::send(
            'emails.account-updated',
            [
                'user' => $user,
                'oldName' => $oldName,
                'oldEmail' => $oldEmail,
                'emailChanged' => false,
            ],
            function ($message) use ($user) {
                $message->to(
                    $user->email,
                    $user->name
                );

                $message->subject(
                    'Je SmartDesk-accountgegevens zijn gewijzigd'
                );
            }
        );


        return redirect()
            ->route('account')
            ->with(
                'success',
                'Je accountgegevens zijn opgeslagen.'
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


        Mail::send(
            'emails.password-changed',
            [
                'user' => $user,
            ],
            function ($message) use ($user) {
                $message->to(
                    $user->email,
                    $user->name
                );

                $message->subject(
                    'Je SmartDesk-wachtwoord is gewijzigd'
                );
            }
        );


        return redirect()
            ->route('account')
            ->with(
                'success',
                'Je wachtwoord is gewijzigd. We hebben hiervan een bevestiging naar je e-mailadres gestuurd.'
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


    /*
    |--------------------------------------------------------------------------
    | Auto bekijken
    |--------------------------------------------------------------------------
    */

    public function car(string $id): View
    {
        $car = collect(
            $this->catalogCars()
        )->firstWhere(
            'id',
            (int) $id
        );


        abort_if(
            ! $car,
            404
        );


        return view(
            'site.product',
            compact('car')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Winkelwagen toevoegen
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


        abort_if(
            ! $car,
            404
        );


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


    /*
    |--------------------------------------------------------------------------
    | Winkelwagen
    |--------------------------------------------------------------------------
    */

    public function cart(): View
    {
        $cart = Session::get(
            'cart',
            []
        );


        $total = collect(
            $cart
        )->sum(
            fn ($item) =>
                $item['qty'] *
                $item['price']
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


        $total = collect(
            $cart
        )->sum(
            fn ($item) =>
                $item['qty'] *
                $item['price']
        );


        return view(
            'site.checkout',
            compact(
                'cart',
                'total'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Bestelling plaatsen
    |--------------------------------------------------------------------------
    */

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
                    'cart' =>
                        'Je winkelwagen is leeg.',
                ]);
        }


        $total = collect(
            $cart
        )->sum(
            fn ($item) =>
                $item['qty'] *
                $item['price']
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


        Mail::send(
            'emails.order-confirmation',
            [
                'user' => $user,

                'orderNumber' =>
                    $orderNumber,

                'orderDate' =>
                    now()->format(
                        'd-m-Y H:i'
                    ),

                'items' =>
                    $cart,

                'total' =>
                    $total,
            ],
            function ($message) use (
                $user,
                $orderNumber
            ) {
                $message->to(
                    $user->email,
                    $user->name
                );

                $message->subject(
                    'Bestelbevestiging ' .
                    $orderNumber
                );
            }
        );


        Session::forget('cart');


        return redirect()
            ->route('account')
            ->with(
                'success',
                'Bestelling ' .
                $orderNumber .
                ' is geplaatst. De bevestiging is naar ' .
                $user->email .
                ' gestuurd.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Verificatiepagina
    |--------------------------------------------------------------------------
    */

    public function verifyNotice(): View
    {
        return view('site.verify');
    }


    /*
    |--------------------------------------------------------------------------
    | Nieuwe verificatiecode sturen
    |--------------------------------------------------------------------------
    */

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


        $this->sendVerificationEmail(
            $user,
            'Je verificatiecode voor SmartDesk'
        );


        return redirect()
            ->route('verification.notice')
            ->with(
                'success',
                'Een nieuwe verificatiecode is per e-mail verzonden.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Verificatiecode controleren
    |--------------------------------------------------------------------------
    */

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
            ->latest(
                'created_at'
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
                    'code' =>
                        'De code is ongeldig of verlopen.',
                ])
                ->withInput();
        }


        $user->email_verified_at =
            now();


        $user->save();


        /*
        |--------------------------------------------------------------------------
        | Alle openstaande verificatiecodes opruimen
        |--------------------------------------------------------------------------
        */

        DB::table(
            'email_verification_codes'
        )
            ->where(
                'user_id',
                $user->id
            )
            ->delete();


        Mail::send(
            'emails.email-verified',
            [
                'user' =>
                    $user,
            ],
            function ($message) use ($user) {
                $message->to(
                    $user->email,
                    $user->name
                );

                $message->subject(
                    'Je e-mailadres is geverifieerd - SmartDesk'
                );
            }
        );


        return redirect()
            ->route('login')
            ->with(
                'success',
                'Je e-mailadres is succesvol geverifieerd.'
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


    /*
    |--------------------------------------------------------------------------
    | Resetlink versturen
    |--------------------------------------------------------------------------
    */

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
                'email' =>
                    $user->email,
            ],
            [
                'token' => hash(
                    'sha256',
                    $token
                ),

                'created_at' =>
                    now(),
            ]
        );


        Mail::send(
            'emails.password-reset',
            [
                'user' =>
                    $user,

                'token' =>
                    $token,
            ],
            function ($message) use ($user) {
                $message->to(
                    $user->email,
                    $user->name
                );

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


    /*
    |--------------------------------------------------------------------------
    | Wachtwoord resetten
    |--------------------------------------------------------------------------
    */

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


        Mail::send(
            'emails.password-changed',
            [
                'user' =>
                    $user,
            ],
            function ($message) use ($user) {
                $message->to(
                    $user->email,
                    $user->name
                );

                $message->subject(
                    'Je SmartDesk-wachtwoord is gewijzigd'
                );
            }
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
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */


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


        $totalUsers =
            $users->count();


        $verifiedUsers =
            $users
                ->whereNotNull(
                    'email_verified_at'
                )
                ->count();


        $adminUsers =
            $users
                ->where(
                    'is_admin',
                    true
                )
                ->count();


        $totalOrders =
            DB::table(
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
    | Alle gebruikers
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


    /*
    |--------------------------------------------------------------------------
    | Nieuwe gebruiker formulier
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $this->ensureAdmin();


        return view(
            'users.create'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Gebruiker door admin aanmaken
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
            'name' => trim(
                $data['name']
            ),

            'email' => strtolower(
                trim($data['email'])
            ),

            'password' => Hash::make(
                $data['password']
            ),

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


        /*
        |--------------------------------------------------------------------------
        | Account-aanmaakmail
        |--------------------------------------------------------------------------
        |
        | Wachtwoord wordt bewust NIET in de e-mail gezet.
        |
        */

        Mail::send(
            'emails.admin-account-created',
            [
                'user' => $user,

                'isAdmin' =>
                    (bool) $user->is_admin,

                'isVerified' =>
                    $user->email_verified_at !== null,

                'createdAt' =>
                    now()->format(
                        'd-m-Y H:i'
                    ),
            ],
            function ($message) use ($user) {
                $message->to(
                    $user->email,
                    $user->name
                );

                $message->subject(
                    'Je SmartDesk-account is aangemaakt'
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Niet geverifieerd?
        |--------------------------------------------------------------------------
        */

        if (! $user->email_verified_at) {
            $this->createVerificationCode(
                $user
            );


            $this->sendVerificationEmail(
                $user,
                'Verifieer je SmartDesk-account'
            );
        }


        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Gebruiker ' .
                $user->name .
                ' is succesvol aangemaakt.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Gebruiker bewerken
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
    | Gebruiker door admin bijwerken
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


        /*
        |--------------------------------------------------------------------------
        | Eigen administratorrechten beschermen
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Oude waarden bewaren
        |--------------------------------------------------------------------------
        */

        $oldName =
            $user->name;


        $oldEmail =
            $user->email;


        $oldIsAdmin =
            (bool) $user->is_admin;


        $oldVerified =
            $user->email_verified_at !== null;


        /*
        |--------------------------------------------------------------------------
        | Nieuwe waarden
        |--------------------------------------------------------------------------
        */

        $newName = trim(
            $data['name']
        );


        $newEmail = strtolower(
            trim($data['email'])
        );


        $newIsAdmin =
            $request->boolean(
                'is_admin'
            );


        $newVerified =
            $request->boolean(
                'email_verified'
            );


        /*
        |--------------------------------------------------------------------------
        | Wijzigingen bepalen
        |--------------------------------------------------------------------------
        */

        $nameChanged =
            $oldName !==
            $newName;


        $emailChanged =
            strtolower($oldEmail) !==
            $newEmail;


        $adminChanged =
            $oldIsAdmin !==
            $newIsAdmin;


        $verifiedChanged =
            $oldVerified !==
            $newVerified;


        $passwordChanged =
            filled(
                $data['password'] ?? null
            );


        /*
        |--------------------------------------------------------------------------
        | Geen wijzigingen
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Nieuw wachtwoord controleren vóór opslaan
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Basisgegevens opslaan
        |--------------------------------------------------------------------------
        */

        $user->name =
            $newName;


        $user->email =
            $newEmail;


        $user->is_admin =
            $newIsAdmin;


        /*
        |--------------------------------------------------------------------------
        | Verificatiestatus
        |--------------------------------------------------------------------------
        */

        if ($newVerified) {
            $user->email_verified_at =
                $user->email_verified_at
                ?? now();
        } else {
            $user->email_verified_at =
                null;
        }


        /*
        |--------------------------------------------------------------------------
        | Wachtwoord
        |--------------------------------------------------------------------------
        */

        if ($passwordChanged) {
            $user->password =
                Hash::make(
                    $data['password']
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Nieuw e-mailadres blijft ongeverifieerd tenzij admin dit expliciet
        | als geverifieerd heeft aangevinkt.
        |--------------------------------------------------------------------------
        */

        if (
            $emailChanged &&
            ! $newVerified
        ) {
            $user->email_verified_at =
                null;
        }


        $user->save();


        /*
        |--------------------------------------------------------------------------
        | Werkelijke nieuwe verificatiestatus
        |--------------------------------------------------------------------------
        */

        $actualNewVerified =
            $user->email_verified_at !== null;


        /*
        |--------------------------------------------------------------------------
        | Verificatiecodes beheren
        |--------------------------------------------------------------------------
        */

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


            $this->sendVerificationEmail(
                $user,
                'Verifieer je e-mailadres - SmartDesk'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Resetlinks verwijderen als e-mailadres verandert
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
        | Informatie voor wijzigingsmail
        |--------------------------------------------------------------------------
        */

        $mailData = [
            'user' =>
                $user,

            'oldName' =>
                $oldName,

            'newName' =>
                $newName,

            'oldEmail' =>
                $oldEmail,

            'newEmail' =>
                $newEmail,

            'nameChanged' =>
                $nameChanged,

            'emailChanged' =>
                $emailChanged,

            'adminChanged' =>
                $adminChanged,

            'oldIsAdmin' =>
                $oldIsAdmin,

            'newIsAdmin' =>
                $newIsAdmin,

            'verifiedChanged' =>
                $verifiedChanged,

            'oldVerified' =>
                $oldVerified,

            'newVerified' =>
                $actualNewVerified,

            'passwordChanged' =>
                $passwordChanged,

            'changedAt' =>
                now()->format(
                    'd-m-Y H:i'
                ),
        ];


        /*
        |--------------------------------------------------------------------------
        | Overzichtsmail naar huidige / nieuwe e-mailadres
        |--------------------------------------------------------------------------
        */

        Mail::send(
            'emails.admin-account-updated',
            $mailData,
            function ($message) use ($user) {
                $message->to(
                    $user->email,
                    $user->name
                );

                $message->subject(
                    'Je SmartDesk-account is gewijzigd'
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Bij e-mailadreswijziging ook oude adres waarschuwen
        |--------------------------------------------------------------------------
        */

        if ($emailChanged) {
            Mail::send(
                'emails.admin-account-updated',
                $mailData,
                function ($message) use (
                    $oldEmail,
                    $oldName
                ) {
                    $message->to(
                        $oldEmail,
                        $oldName
                    );

                    $message->subject(
                        'Beveiligingsmelding: je SmartDesk-account is gewijzigd'
                    );
                }
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
    | Gebruiker verwijderen
    |--------------------------------------------------------------------------
    */

    public function destroy(
        User $user
    ): RedirectResponse {
        $this->ensureAdmin();


        /*
        |--------------------------------------------------------------------------
        | Eigen account beschermen
        |--------------------------------------------------------------------------
        */

        if (
            Auth::id() ===
            $user->id
        ) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'Je kunt je eigen administratoraccount niet verwijderen.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Gegevens bewaren voor e-mail
        |--------------------------------------------------------------------------
        */

        $deletedUserName =
            $user->name;


        $deletedUserEmail =
            $user->email;


        $wasAdmin =
            (bool) $user->is_admin;


        $deletedAt =
            now()->format(
                'd-m-Y H:i'
            );


        /*
        |--------------------------------------------------------------------------
        | Gebruiker eerst informeren
        |--------------------------------------------------------------------------
        */

        Mail::send(
            'emails.account-deleted',
            [
                'user' =>
                    $user,

                'name' =>
                    $deletedUserName,

                'email' =>
                    $deletedUserEmail,

                'wasAdmin' =>
                    $wasAdmin,

                'deletedAt' =>
                    $deletedAt,
            ],
            function ($message) use (
                $deletedUserEmail,
                $deletedUserName
            ) {
                $message->to(
                    $deletedUserEmail,
                    $deletedUserName
                );

                $message->subject(
                    'Je SmartDesk-account is verwijderd'
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Verificatiecodes verwijderen
        |--------------------------------------------------------------------------
        */

        DB::table(
            'email_verification_codes'
        )
            ->where(
                'user_id',
                $user->id
            )
            ->delete();


        /*
        |--------------------------------------------------------------------------
        | Wachtwoordresetlinks verwijderen
        |--------------------------------------------------------------------------
        */

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
        | Bestellingen worden bewust NIET verwijderd.
        |--------------------------------------------------------------------------
        */


        $user->delete();


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
    |--------------------------------------------------------------------------
    | PRIVATE HELPERS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Administrator controleren
    |--------------------------------------------------------------------------
    */

    private function ensureAdmin(): void
    {
        abort_unless(
            Auth::check() &&
            Auth::user()->is_admin,
            403,
            'Je hebt geen toestemming om deze beheerpagina te bekijken.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verificatiecode aanmaken
    |--------------------------------------------------------------------------
    */

    private function createVerificationCode(
        User $user
    ): void {
        $code = random_int(
            100000,
            999999
        );


        /*
        |--------------------------------------------------------------------------
        | Oude codes verwijderen
        |--------------------------------------------------------------------------
        */

        DB::table(
            'email_verification_codes'
        )
            ->where(
                'user_id',
                $user->id
            )
            ->delete();


        /*
        |--------------------------------------------------------------------------
        | Nieuwe code opslaan
        |--------------------------------------------------------------------------
        */

        DB::table(
            'email_verification_codes'
        )->insert([
            'user_id' =>
                $user->id,

            'code' =>
                (string) $code,

            'expires_at' =>
                now()->addMinutes(15),

            'used_at' =>
                null,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Verificatiemail versturen
    |--------------------------------------------------------------------------
    */

    private function sendVerificationEmail(
        User $user,
        string $subject
    ): void {
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
            return;
        }


        Mail::send(
            'emails.verification-code',
            [
                'user' =>
                    $user,

                'code' =>
                    $record->code,
            ],
            function ($message) use (
                $user,
                $subject
            ) {
                $message->to(
                    $user->email,
                    $user->name
                );

                $message->subject(
                    $subject
                );
            }
        );
    }
}

