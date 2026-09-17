<?php

use App\Http\Controllers\EmailLoginController;
use App\Http\Controllers\FacebookAuthController;
use App\Http\Controllers\GitHubAuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\TikTokAuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get('/', [UserController::class, 'home'])
    ->name('home');


/*
|--------------------------------------------------------------------------
| Gast-routes
|--------------------------------------------------------------------------
|
| Deze routes zijn alleen bedoeld voor bezoekers die nog niet zijn
| ingelogd. Daardoor kan een ingelogde gebruiker niet opnieuw de
| login-, registratie-, passwordless- of OAuth-flow openen.
|
| Als een ingelogde gebruiker bijvoorbeeld /login opent, grijpt de
| Laravel "guest" middleware in en wordt die gebruiker doorgestuurd.
|
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Registreren
    |--------------------------------------------------------------------------
    */

    Route::get('/register', [UserController::class, 'register'])
        ->name('register');

    Route::post('/register', [UserController::class, 'registerSubmit'])
        ->name('register.submit');


    /*
    |--------------------------------------------------------------------------
    | Normaal inloggen
    |--------------------------------------------------------------------------
    */

    Route::get('/login', [UserController::class, 'login'])
        ->name('login');

    Route::post('/login', [UserController::class, 'loginSubmit'])
        ->name('login.submit');


    /*
    |--------------------------------------------------------------------------
    | Inloggen met e-mailcode of magic link
    |--------------------------------------------------------------------------
    |
    | Flow:
    |
    | De gebruiker kan kiezen uit:
    |
    | - een 6-cijferige e-mailcode;
    | - een eenmalige magic login link via Brevo.
    |
    | Beide methodes loggen de gebruiker veilig in zonder dat daarvoor
    | een wachtwoord nodig is.
    |
    */

    Route::prefix('auth/email')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | 6-cijferige e-mailcode
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/send-code',
            [EmailLoginController::class, 'sendCode']
        )
            ->middleware('throttle:10,1')
            ->name('email-login.send');


        Route::get(
            '/verify',
            [EmailLoginController::class, 'showVerifyForm']
        )
            ->name('email-login.form');


        Route::post(
            '/verify',
            [EmailLoginController::class, 'verifyCode']
        )
            ->middleware('throttle:20,1')
            ->name('email-login.verify');


        /*
        |--------------------------------------------------------------------------
        | Magic login link
        |--------------------------------------------------------------------------
        |
        | De gebruiker ontvangt per e-mail een eenmalige loginlink.
        | Na openen van de geldige link wordt het account direct ingelogd.
        |
        */

        Route::post(
            '/send-link',
            [EmailLoginController::class, 'sendMagicLink']
        )
            ->middleware('throttle:10,1')
            ->name('email-login.link.send');


        Route::get(
            '/link/verify',
            [EmailLoginController::class, 'verifyMagicLink']
        )
            ->middleware('throttle:30,1')
            ->name('email-login.link.verify');
    });


    /*
    |--------------------------------------------------------------------------
    | Google OAuth
    |--------------------------------------------------------------------------
    |
    | Inloggen en registreren via Google met Laravel Socialite.
    |
    */

    Route::get(
        '/auth/google',
        [GoogleAuthController::class, 'redirect']
    )
        ->name('google.redirect');

    Route::get(
        '/auth/google/callback',
        [GoogleAuthController::class, 'callback']
    )
        ->name('google.callback');


    /*
    |--------------------------------------------------------------------------
    | GitHub OAuth
    |--------------------------------------------------------------------------
    |
    | Inloggen en registreren via GitHub met Laravel Socialite.
    |
    */

    Route::get(
        '/auth/github',
        [GitHubAuthController::class, 'redirect']
    )
        ->name('github.redirect');

    Route::get(
        '/auth/github/callback',
        [GitHubAuthController::class, 'callback']
    )
        ->name('github.callback');


    /*
    |--------------------------------------------------------------------------
    | Facebook OAuth
    |--------------------------------------------------------------------------
    |
    | Inloggen en registreren via Facebook met Laravel Socialite.
    |
    */

    Route::get(
        '/auth/facebook',
        [FacebookAuthController::class, 'redirect']
    )
        ->name('facebook.redirect');

    Route::get(
        '/auth/facebook/callback',
        [FacebookAuthController::class, 'callback']
    )
        ->name('facebook.callback');


    /*
    |--------------------------------------------------------------------------
    | TikTok OAuth
    |--------------------------------------------------------------------------
    |
    | Inloggen en registreren via TikTok Login Kit.
    |
    | Bestaande TikTok-gebruikers worden direct ingelogd.
    | Nieuwe TikTok-gebruikers ronden hun Mashal-account eerst af via
    | het aanvullende registratieformulier.
    |
    */

    Route::get(
        '/auth/tiktok',
        [TikTokAuthController::class, 'redirect']
    )
        ->name('tiktok.redirect');

    Route::get(
        '/auth/tiktok/callback',
        [TikTokAuthController::class, 'callback']
    )
        ->name('tiktok.callback');

    Route::get(
        '/auth/tiktok/complete',
        [TikTokAuthController::class, 'showCompleteRegistration']
    )
        ->name('tiktok.complete');

    Route::post(
        '/auth/tiktok/complete',
        [TikTokAuthController::class, 'completeRegistration']
    )
        ->middleware('throttle:10,1')
        ->name('tiktok.complete.submit');


    /*
    |--------------------------------------------------------------------------
    | Wachtwoord vergeten / herstellen
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/forgot-password',
        [UserController::class, 'forgotPassword']
    )
        ->name('password.request');


    Route::post(
        '/forgot-password',
        [UserController::class, 'sendResetLink']
    )
        ->middleware('throttle:5,1')
        ->name('password.email');


    Route::get(
        '/reset-password/{token}',
        [UserController::class, 'showResetForm']
    )
        ->name('password.reset');


    Route::post(
        '/reset-password/{token}',
        [UserController::class, 'resetPassword']
    )
        ->name('password.update');
});


/*
|--------------------------------------------------------------------------
| Uitloggen
|--------------------------------------------------------------------------
*/

Route::post(
    '/logout',
    [UserController::class, 'logout']
)
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| E-mailverificatie
|--------------------------------------------------------------------------
|
| Deze routes worden gebruikt voor normale registraties met
| e-mailadres en wachtwoord.
|
*/

Route::get(
    '/verify',
    [UserController::class, 'verifyNotice']
)
    ->name('verification.notice');


Route::post(
    '/verify/send',
    [UserController::class, 'sendVerificationCode']
)
    ->middleware('throttle:6,1')
    ->name('verification.send');


Route::post(
    '/verify',
    [UserController::class, 'verifyCode']
)
    ->middleware('throttle:10,1')
    ->name('verification.verify');


/*
|--------------------------------------------------------------------------
| Catalogus
|--------------------------------------------------------------------------
*/

Route::get(
    '/catalog',
    [UserController::class, 'catalog']
)
    ->name('catalog');


Route::get(
    '/car/{id}',
    [UserController::class, 'car']
)
    ->whereNumber('id')
    ->name('car');


/*
|--------------------------------------------------------------------------
| Winkelwagen
|--------------------------------------------------------------------------
*/

Route::get(
    '/cart',
    [UserController::class, 'cart']
)
    ->name('cart');


Route::post(
    '/cart/add/{id}',
    [UserController::class, 'addToCart']
)
    ->whereNumber('id')
    ->name('cart.add');


/*
|--------------------------------------------------------------------------
| Routes voor ingelogde gebruikers
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Mijn account
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/account',
        [UserController::class, 'account']
    )
        ->name('account');


    Route::put(
        '/account',
        [UserController::class, 'updateAccount']
    )
        ->name('account.update');


    Route::put(
        '/account/password',
        [UserController::class, 'updatePassword']
    )
        ->name('account.password.update');


    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/checkout',
        [UserController::class, 'checkout']
    )
        ->name('checkout');


    Route::post(
        '/checkout',
        [UserController::class, 'checkoutSubmit']
    )
        ->name('checkout.submit');


    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    |
    | Deze routes vereisen minimaal een ingelogde gebruiker.
    | De UserController moet daarnaast nog steeds controleren
    | of is_admin daadwerkelijk true is.
    |
    */

    Route::prefix('admin')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Admin dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [UserController::class, 'admin']
        )
            ->name('admin.dashboard');


        /*
        |--------------------------------------------------------------------------
        | Gebruikersoverzicht
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/users',
            [UserController::class, 'index']
        )
            ->name('users.index');


        /*
        |--------------------------------------------------------------------------
        | Gebruiker toevoegen
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/users/create',
            [UserController::class, 'create']
        )
            ->name('users.create');


        Route::post(
            '/users',
            [UserController::class, 'store']
        )
            ->name('users.store');


        /*
        |--------------------------------------------------------------------------
        | Gebruiker wijzigen
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/users/{user}/edit',
            [UserController::class, 'edit']
        )
            ->whereNumber('user')
            ->name('users.edit');


        Route::put(
            '/users/{user}',
            [UserController::class, 'update']
        )
            ->whereNumber('user')
            ->name('users.update');


        /*
        |--------------------------------------------------------------------------
        | Gebruiker verwijderen
        |--------------------------------------------------------------------------
        */

        Route::delete(
            '/users/{user}',
            [UserController::class, 'destroy']
        )
            ->whereNumber('user')
            ->name('users.destroy');
    });
});
