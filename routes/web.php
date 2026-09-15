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
| Gast / authenticatie
|--------------------------------------------------------------------------
*/


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
|
| Uitloggen gebeurt bewust via POST.
| Daardoor kan een externe link iemand niet zomaar uitloggen.
|
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
|
| De catalogus is openbaar toegankelijk.
|
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
|
| Bezoekers mogen auto's aan hun winkelwagen toevoegen.
| Voor checkout moet de gebruiker wel ingelogd zijn.
|
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


    /*
    |--------------------------------------------------------------------------
    | Accountgegevens wijzigen
    |--------------------------------------------------------------------------
    */

    Route::put('/account', [UserController::class, 'updateAccount'])
        ->name('account.update');


    /*
    |--------------------------------------------------------------------------
    | Wachtwoord wijzigen
    |--------------------------------------------------------------------------
    */

    Route::put('/account/password', [UserController::class, 'updatePassword'])
        ->name('account.password.update');


    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    |
    | De controller controleert daarnaast of email_verified_at gevuld is.
    |
    */

    Route::get('/checkout', [UserController::class, 'checkout'])
        ->name('checkout');

    Route::post('/checkout', [UserController::class, 'checkoutSubmit'])
        ->name('checkout.submit');


    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    |
    | Alle adminroutes vereisen minimaal een ingelogde gebruiker.
    |
    | De UserController controleert daarnaast via ensureAdmin()
    | of de ingelogde gebruiker daadwerkelijk is_admin = true heeft.
    |
    | Daardoor kan een normale gebruiker niet simpelweg /admin openen.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Admin dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/admin', [UserController::class, 'admin'])
        ->name('admin.dashboard');


    /*
    |--------------------------------------------------------------------------
    | Admin - gebruikers bekijken
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/users', [UserController::class, 'index'])
        ->name('users.index');


    /*
    |--------------------------------------------------------------------------
    | Admin - gebruiker toevoegen
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/users/create', [UserController::class, 'create'])
        ->name('users.create');

    Route::post('/admin/users', [UserController::class, 'store'])
        ->name('users.store');


    /*
    |--------------------------------------------------------------------------
    | Admin - gebruiker wijzigen
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])
        ->whereNumber('user')
        ->name('users.edit');

    Route::put('/admin/users/{user}', [UserController::class, 'update'])
        ->whereNumber('user')
        ->name('users.update');


    /*
    |--------------------------------------------------------------------------
    | Admin - gebruiker verwijderen
    |--------------------------------------------------------------------------
    */

    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])
        ->whereNumber('user')
        ->name('users.destroy');
});

