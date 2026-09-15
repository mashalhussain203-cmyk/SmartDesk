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
| Registratie
|--------------------------------------------------------------------------
*/

Route::get('/register', [UserController::class, 'register'])
    ->name('register');

Route::post('/register', [UserController::class, 'registerSubmit'])
    ->name('register.submit');


/*
|--------------------------------------------------------------------------
| E-mail verificatie
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
| Wachtwoord vergeten / resetten
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
| Login / Logout
|--------------------------------------------------------------------------
*/

Route::get('/login', [UserController::class, 'login'])
    ->name('login');

Route::post('/login', [UserController::class, 'loginSubmit'])
    ->name('login.submit');

Route::post('/logout', [UserController::class, 'logout'])
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Mijn account
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Account bekijken
    Route::get('/account', [UserController::class, 'account'])
        ->name('account');

    // Naam en e-mailadres wijzigen
    Route::put('/account', [UserController::class, 'updateAccount'])
        ->name('account.update');

    // Wachtwoord wijzigen
    Route::put('/account/password', [UserController::class, 'updatePassword'])
        ->name('account.password');

});


/*
|--------------------------------------------------------------------------
| Catalogus
|--------------------------------------------------------------------------
*/

Route::get('/catalog', [UserController::class, 'catalog'])
    ->name('catalog');

Route::get('/car/{id}', [UserController::class, 'car'])
    ->name('car');


/*
|--------------------------------------------------------------------------
| Winkelwagen
|--------------------------------------------------------------------------
*/

Route::post('/cart/add/{id}', [UserController::class, 'addToCart'])
    ->name('cart.add');

Route::get('/cart', [UserController::class, 'cart'])
    ->name('cart');


/*
|--------------------------------------------------------------------------
| Checkout
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/checkout', [UserController::class, 'checkout'])
        ->name('checkout');

    Route::post('/checkout', [UserController::class, 'checkoutSubmit'])
        ->name('checkout.submit');

});


/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::get('/admin', [UserController::class, 'admin'])
    ->name('admin.dashboard');

Route::get('/users', [UserController::class, 'index'])
    ->name('users.index');

Route::get('/users/create', [UserController::class, 'create'])
    ->name('users.create');

Route::post('/users', [UserController::class, 'store'])
    ->name('users.store');
