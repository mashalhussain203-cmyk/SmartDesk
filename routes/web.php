<?php

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
| Registreren
|--------------------------------------------------------------------------
*/

Route::get('/register', [UserController::class, 'register'])
    ->name('register');

Route::post('/register', [UserController::class, 'registerSubmit'])
    ->name('register.submit');


/*
|--------------------------------------------------------------------------
| Inloggen
|--------------------------------------------------------------------------
*/

Route::get('/login', [UserController::class, 'login'])
    ->name('login');

Route::post('/login', [UserController::class, 'loginSubmit'])
    ->name('login.submit');


/*
|--------------------------------------------------------------------------
| Uitloggen
|--------------------------------------------------------------------------
*/

Route::post('/logout', [UserController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| E-mailverificatie
|--------------------------------------------------------------------------
*/

Route::get('/verify', [UserController::class, 'verifyNotice'])
    ->name('verification.notice');

Route::post('/verify/send', [UserController::class, 'sendVerificationCode'])
    ->name('verification.send');

Route::post('/verify', [UserController::class, 'verifyCode'])
    ->name('verification.verify');


/*
|--------------------------------------------------------------------------
| Wachtwoord vergeten / herstellen
|--------------------------------------------------------------------------
*/

Route::get('/forgot-password', [UserController::class, 'forgotPassword'])
    ->name('password.request');

Route::post('/forgot-password', [UserController::class, 'sendResetLink'])
    ->name('password.email');

Route::get('/reset-password/{token}', [UserController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/reset-password/{token}', [UserController::class, 'resetPassword'])
    ->name('password.update');


/*
|--------------------------------------------------------------------------
| Catalogus
|--------------------------------------------------------------------------
*/

Route::get('/catalog', [UserController::class, 'catalog'])
    ->name('catalog');

Route::get('/car/{id}', [UserController::class, 'car'])
    ->whereNumber('id')
    ->name('car');


/*
|--------------------------------------------------------------------------
| Winkelwagen
|--------------------------------------------------------------------------
*/

Route::get('/cart', [UserController::class, 'cart'])
    ->name('cart');

Route::post('/cart/add/{id}', [UserController::class, 'addToCart'])
    ->whereNumber('id')
    ->name('cart.add');


/*
|--------------------------------------------------------------------------
| Ingelogde gebruikers
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Mijn account
    |--------------------------------------------------------------------------
    */

    Route::get('/account', [UserController::class, 'account'])
        ->name('account');

    Route::put('/account', [UserController::class, 'updateAccount'])
        ->name('account.update');

    Route::put('/account/password', [UserController::class, 'updatePassword'])
        ->name('account.password.update');


    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    |
    | De controller controleert daarnaast of email_verified_at is ingevuld.
    |
    */

    Route::get('/checkout', [UserController::class, 'checkout'])
        ->name('checkout');

    Route::post('/checkout', [UserController::class, 'checkoutSubmit'])
        ->name('checkout.submit');


    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    |
    | Deze routes vereisen een ingelogde gebruiker.
    |
    | De UserController voert daarnaast in elke adminfunctie ensureAdmin()
    | uit. Daardoor moet de gebruiker is_admin = true hebben.
    |
    */

    Route::prefix('admin')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/', [UserController::class, 'admin'])
            ->name('admin.dashboard');


        /*
        |--------------------------------------------------------------------------
        | Gebruikersbeheer
        |--------------------------------------------------------------------------
        */

        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');


        /*
        |--------------------------------------------------------------------------
        | Gebruiker toevoegen
        |--------------------------------------------------------------------------
        */

        Route::get('/users/create', [UserController::class, 'create'])
            ->name('users.create');

        Route::post('/users', [UserController::class, 'store'])
            ->name('users.store');


        /*
        |--------------------------------------------------------------------------
        | Gebruiker wijzigen
        |--------------------------------------------------------------------------
        */

        Route::get('/users/{user}/edit', [UserController::class, 'edit'])
            ->whereNumber('user')
            ->name('users.edit');

        Route::put('/users/{user}', [UserController::class, 'update'])
            ->whereNumber('user')
            ->name('users.update');


        /*
        |--------------------------------------------------------------------------
        | Gebruiker verwijderen
        |--------------------------------------------------------------------------
        */

        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->whereNumber('user')
            ->name('users.destroy');
    });
});

