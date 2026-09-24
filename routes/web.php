<?php

use App\Http\Controllers\AiChatController;
use App\Http\Controllers\EmailLoginController;
use App\Http\Controllers\FacebookAuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GitHubAuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ImageEditorController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\TikTokAuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Mashal Studio web routes
|--------------------------------------------------------------------------
|
| Publieke routes, authenticatie, image workflow, Mashal AI, account,
| beveiliging en admin-functionaliteit staan hieronder gegroepeerd.
|
*/


/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [UserController::class, 'home']
)->name('home');


/*
|--------------------------------------------------------------------------
| Afbeelding uploaden vóór login
|--------------------------------------------------------------------------
|
| Bezoekers mogen eerst een afbeelding uploaden zonder dat zij al zijn
| ingelogd.
|
| ImageUploadController:
|
| 1. valideert de afbeelding;
| 2. bewaart deze tijdelijk op de private local disk;
| 3. slaat de gegevens op in de sessie;
| 4. stuurt gasten naar de loginpagina;
| 5. stuurt ingelogde gebruikers rechtstreeks naar de claim-route.
|
| De throttle voorkomt dat één client onbeperkt uploads kan uitvoeren.
|
*/

Route::post(
    '/images/upload',
    [ImageUploadController::class, 'storeTemporary']
)
    ->middleware('throttle:20,1')
    ->name('images.upload');


/*
|--------------------------------------------------------------------------
| Login Security Browser Context
|--------------------------------------------------------------------------
|
| Deze route staat bewust buiten de guest- en auth-middlewaregroepen.
|
| De browser kan hiermee vóór een daadwerkelijke login tijdelijke
| beveiligingsinformatie in de Laravel-sessie opslaan.
|
| Mogelijke informatie:
|
| - browser timezone;
| - GPS latitude;
| - GPS longitude;
| - GPS accuracy;
| - location permission.
|
| Locatiegegevens mogen uiteraard alleen vanuit de browser worden
| doorgestuurd wanneer de gebruiker daarvoor toestemming heeft gegeven.
|
*/

Route::post(
    '/login-security/context',
    [SecurityController::class, 'storeBrowserContext']
)
    ->middleware('throttle:30,1')
    ->name('login-security.context');


/*
|--------------------------------------------------------------------------
| Gast-routes
|--------------------------------------------------------------------------
|
| Alleen toegankelijk wanneer de gebruiker niet is ingelogd.
|
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Registreren
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/register',
        [UserController::class, 'register']
    )->name('register');


    Route::post(
        '/register',
        [UserController::class, 'registerSubmit']
    )
        ->middleware('throttle:10,1')
        ->name('register.submit');


    /*
    |--------------------------------------------------------------------------
    | Normaal inloggen
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/login',
        [UserController::class, 'login']
    )->name('login');


    Route::post(
        '/login',
        [UserController::class, 'loginSubmit']
    )
        ->middleware('throttle:20,1')
        ->name('login.submit');


    /*
    |--------------------------------------------------------------------------
    | Passwordless login
    |--------------------------------------------------------------------------
    |
    | Ondersteund:
    |
    | - 6-cijferige e-mailcode;
    | - magic login link.
    |
    */

    Route::prefix('auth/email')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | E-mailcode aanvragen
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/send-code',
            [EmailLoginController::class, 'sendCode']
        )
            ->middleware('throttle:10,1')
            ->name('email-login.send');


        /*
        |--------------------------------------------------------------------------
        | E-mailcodeformulier
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/verify',
            [EmailLoginController::class, 'showVerifyForm']
        )
            ->name('email-login.form');


        /*
        |--------------------------------------------------------------------------
        | E-mailcode controleren
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/verify',
            [EmailLoginController::class, 'verifyCode']
        )
            ->middleware('throttle:20,1')
            ->name('email-login.verify');


        /*
        |--------------------------------------------------------------------------
        | Magic login link verzenden
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/send-link',
            [EmailLoginController::class, 'sendMagicLink']
        )
            ->middleware('throttle:10,1')
            ->name('email-login.link.send');


        /*
        |--------------------------------------------------------------------------
        | Magic login link controleren
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/link/verify',
            [EmailLoginController::class, 'verifyMagicLink']
        )
            ->middleware('throttle:30,1')
            ->name('email-login.link.verify');


        /*
        |--------------------------------------------------------------------------
        | Magic-link bevestigingspagina
        |--------------------------------------------------------------------------
        |
        | Op deze pagina kan browser-securitycontext worden verzameld voordat
        | de daadwerkelijke login wordt voltooid.
        |
        */

        Route::get(
            '/link/confirm',
            [EmailLoginController::class, 'showMagicLinkConfirmation']
        )
            ->name('email-login.link.confirm');


        /*
        |--------------------------------------------------------------------------
        | Magic-link login afronden
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/link/complete',
            [EmailLoginController::class, 'completeMagicLink']
        )
            ->middleware('throttle:20,1')
            ->name('email-login.link.complete');
    });


    /*
    |--------------------------------------------------------------------------
    | Google OAuth
    |--------------------------------------------------------------------------
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
    | LinkedIn OpenID Connect
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/auth/linkedin',
        [\App\Http\Controllers\LinkedInAuthController::class, 'redirect']
    )
        ->name('linkedin.redirect');


    Route::get(
        '/auth/linkedin/callback',
        [\App\Http\Controllers\LinkedInAuthController::class, 'callback']
    )
        ->name('linkedin.callback');

    /*
    |--------------------------------------------------------------------------
    | TikTok OAuth
    |--------------------------------------------------------------------------
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


    /*
    |--------------------------------------------------------------------------
    | Aanvullende TikTok-registratie
    |--------------------------------------------------------------------------
    */

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
    | Wachtwoord vergeten
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


    /*
    |--------------------------------------------------------------------------
    | Wachtwoord herstellen
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/reset-password/{token}',
        [UserController::class, 'showResetForm']
    )
        ->name('password.reset');


    Route::post(
        '/reset-password/{token}',
        [UserController::class, 'resetPassword']
    )
        ->middleware('throttle:10,1')
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
| Wordt onder andere gebruikt voor normale registraties met een
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
    | Afbeeldingen
    |--------------------------------------------------------------------------
    |
    | Volledige private image-workspace.
    |
    | Originelen en gegenereerde versies staan niet rechtstreeks publiek
    | toegankelijk. Bestanden worden via ImageEditorController aangeboden,
    | zodat bij ieder request eigenaarschap kan worden gecontroleerd.
    |
    */

    Route::prefix('images')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Tijdelijke upload claimen
        |--------------------------------------------------------------------------
        |
        | Na login wordt de afbeelding uit de sessie aan de huidige gebruiker
        | gekoppeld.
        |
        */

        Route::get(
            '/claim',
            [ImageUploadController::class, 'claim']
        )
            ->name('images.claim');


        /*
        |--------------------------------------------------------------------------
        | Persoonlijke bibliotheek
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [ImageEditorController::class, 'index']
        )
            ->name('images.index');


        /*
        |--------------------------------------------------------------------------
        | Editor openen
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/{image}/edit',
            [ImageEditorController::class, 'edit']
        )
            ->whereNumber('image')
            ->name('images.editor');


        /*
        |--------------------------------------------------------------------------
        | Originele private afbeelding bekijken
        |--------------------------------------------------------------------------
        |
        | Wordt bijvoorbeeld gebruikt door:
        |
        | - de library-thumbnail;
        | - de editor-preview;
        | - andere authenticated previews.
        |
        */

        Route::get(
            '/{image}/file',
            [ImageEditorController::class, 'file']
        )
            ->whereNumber('image')
            ->name('images.file');


        /*
        |--------------------------------------------------------------------------
        | Origineel downloaden
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/{image}/download',
            [ImageEditorController::class, 'downloadOriginal']
        )
            ->whereNumber('image')
            ->name('images.download');


        /*
        |--------------------------------------------------------------------------
        | Afbeelding verwerken
        |--------------------------------------------------------------------------
        |
        | De centrale processing-endpoint ondersteunt onder andere:
        |
        | - resize;
        | - crop;
        | - rotate;
        | - flip;
        | - enhance;
        | - pasfoto / ID-foto;
        | - compress;
        | - convert;
        | - achtergrond verwijderen;
        | - achtergrondkleur;
        | - achtergrondafbeelding.
        |
        | Iedere geslaagde bewerking wordt als nieuwe ImageVersion opgeslagen.
        |
        */

        Route::post(
            '/{image}/process',
            [ImageEditorController::class, 'process']
        )
            ->whereNumber('image')
            ->middleware('throttle:60,1')
            ->name('images.process');


        /*
        |--------------------------------------------------------------------------
        | Volledig image-project verwijderen
        |--------------------------------------------------------------------------
        |
        | Verwijdert:
        |
        | - database-record;
        | - origineel bestand;
        | - alle opgeslagen versies.
        |
        */

        Route::delete(
            '/{image}',
            [ImageEditorController::class, 'destroy']
        )
            ->whereNumber('image')
            ->name('images.destroy');


        /*
        |--------------------------------------------------------------------------
        | Versies
        |--------------------------------------------------------------------------
        */

        Route::prefix('{image}/versions')
            ->whereNumber('image')
            ->group(function () {

                /*
                |--------------------------------------------------------------------------
                | Versiebestand bekijken
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/{version}/file',
                    [ImageEditorController::class, 'versionFile']
                )
                    ->whereNumber('version')
                    ->name('images.versions.file');


                /*
                |--------------------------------------------------------------------------
                | Versie downloaden
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/{version}/download',
                    [ImageEditorController::class, 'downloadVersion']
                )
                    ->whereNumber('version')
                    ->name('images.versions.download');


                /*
                |--------------------------------------------------------------------------
                | Individuele versie verwijderen
                |--------------------------------------------------------------------------
                |
                | Alleen de gekozen bewerkte versie wordt verwijderd.
                | Het originele project blijft bestaan.
                |
                */

                Route::delete(
                    '/{version}',
                    [ImageEditorController::class, 'destroyVersion']
                )
                    ->whereNumber('version')
                    ->name('images.versions.destroy');
            });
    });


    /*
    |--------------------------------------------------------------------------
    | Mashal AI + Live Voice
    |--------------------------------------------------------------------------
    |
    | Zowel tekstchat als Live Voice blijven volledig server-side gekoppeld
    | aan Groq. De browser ontvangt nooit GROQ_API_KEY.
    |
    */

    Route::get(
        '/ai-chat',
        [AiChatController::class, 'index']
    )
        ->name('ai.chat');


    Route::post(
        '/ai-chat/message',
        [AiChatController::class, 'message']
    )
        ->middleware('throttle:20,1')
        ->name('ai.chat.message');


    Route::post(
        '/ai-chat/voice/turn',
        [AiChatController::class, 'voiceTurn']
    )
        ->middleware('throttle:30,1')
        ->name('ai.chat.voice.turn');


    Route::post(
        '/ai-chat/voice/tts',
        [AiChatController::class, 'speech']
    )
        ->middleware('throttle:60,1')
        ->name('ai.chat.voice.tts');


    /*
    |--------------------------------------------------------------------------
    | Favorieten
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/favorites',
        [FavoriteController::class, 'index']
    )
        ->name('favorites.index');


    Route::post(
        '/favorites/{id}',
        [FavoriteController::class, 'store']
    )
        ->whereNumber('id')
        ->name('favorites.store');


    Route::delete(
        '/favorites/{id}',
        [FavoriteController::class, 'destroy']
    )
        ->whereNumber('id')
        ->name('favorites.destroy');


    /*
    |--------------------------------------------------------------------------
    | Loginbeveiliging
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/account/security',
        [SecurityController::class, 'index']
    )
        ->name('security.index');


    Route::delete(
        '/account/security/history',
        [SecurityController::class, 'destroyHistory']
    )
        ->name('security.history.destroy');


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


    /*
    |--------------------------------------------------------------------------
    | Wachtwoord wijzigen
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/account/password',
        [UserController::class, 'updatePassword']
    )
        ->middleware('throttle:10,1')
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
        ->middleware('throttle:20,1')
        ->name('checkout.submit');


    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    |
    | Deze routes vereisen minimaal authenticatie.
    |
    | BELANGRIJK:
    |
    | UserController moet daarnaast blijven controleren of de gebruiker
    | daadwerkelijk administratorrechten heeft.
    |
    | Nog beter is om hiervoor uiteindelijk een aparte admin-middleware
    | te gebruiken.
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

// Mashal AI Workspace V5
require __DIR__.'/ai-workspace.php';


// Mashal AI Studio V6
require __DIR__.'/ai-studio.php';

require __DIR__.'/passkeys.php';
