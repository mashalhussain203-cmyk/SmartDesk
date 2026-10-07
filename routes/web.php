<?php

use App\Http\Controllers\AiChatController;

use App\Http\Controllers\ContactController;

use App\Http\Controllers\GuestChatController;

use App\Http\Controllers\EmailLoginController;

use App\Http\Controllers\FacebookAuthController;

use App\Http\Controllers\FavoriteController;

use App\Http\Controllers\ForgotEmailController;

use App\Http\Controllers\GitHubAuthController;

use App\Http\Controllers\GmailController;

use App\Http\Controllers\GoogleAuthController;

use App\Http\Controllers\ImageEditorController;

use App\Http\Controllers\ImageUploadController;

use App\Http\Controllers\LinkedInAuthController;

use App\Http\Controllers\Auth\MicrosoftAuthController;

use App\Http\Controllers\Auth\TelegramAuthController;

use App\Http\Controllers\LoginApprovalController;

use App\Http\Controllers\RecoveryEmailController;

use App\Http\Controllers\SecurityController;

use App\Http\Controllers\TikTokAuthController;
use App\Http\Controllers\TikTokCounterController;

use App\Http\Controllers\TwoFactorAuthenticationController;

use App\Http\Controllers\TwoFactorChallengeController;

use App\Http\Controllers\UserController;

use App\Http\Controllers\XAuthController;

use Illuminate\Support\Facades\Route;

Route::post('/guest-chat/message', [GuestChatController::class, 'store'])

    ->middleware('throttle:10,1')

    ->name('guest-chat.message');

/*

|--------------------------------------------------------------------------

| Mashal Studio Web Routes

|--------------------------------------------------------------------------

|

| Publieke routes, authenticatie, image workflow, Mashal AI, Gmail,

| accountbeheer, beveiliging en admin-functionaliteit.

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

| Publieke informatiepagina's

|--------------------------------------------------------------------------

*/

Route::view(

    '/privacy',

    'site.privacy'

)->name('privacy');

Route::get(

    '/contact',

    [ContactController::class, 'show']

)->name('contact');

Route::post(

    '/contact',

    [ContactController::class, 'send']

)

    ->middleware('throttle:5,1')

    ->name('contact.send');

Route::view(

    '/over-ons',

    'site.about'

)->name('about');

Route::view(

    '/terms',

    'site.terms'

)->name('terms');

Route::redirect(

    '/voorwaarden',

    '/terms',

    301

);

/*

|--------------------------------------------------------------------------

| Afbeelding uploaden vóór login

|--------------------------------------------------------------------------

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

*/

Route::post(

    '/login-security/context',

    [SecurityController::class, 'storeBrowserContext']

)

    ->middleware('throttle:30,1')

    ->name('login-security.context');

/*

|--------------------------------------------------------------------------

| X OAuth Callback

|--------------------------------------------------------------------------

|

| Deze callback staat bewust buiten de guest- en auth-groepen.

|

*/

Route::get(

    '/auth/x/callback',

    [XAuthController::class, 'callback']

)

    ->middleware('throttle:30,1')

    ->name('x.callback');

/*

|--------------------------------------------------------------------------

| Telegram Main Mini App

|--------------------------------------------------------------------------

|

| Deze routes staan bewust buiten de guest-groep.

| De Mini App draait in Telegram en maakt daarna een eenmalige browser-handoff.

|

*/

Route::get(

    '/auth/telegram/mini-app',

    [TelegramAuthController::class, 'miniApp']

)

    ->middleware('throttle:60,1')

    ->name('auth.telegram.mini-app');

Route::post(

    '/auth/telegram/mini-app/auth',

    [TelegramAuthController::class, 'miniAppAuthenticate']

)

    ->middleware('throttle:30,1')

    ->name('auth.telegram.mini-app.auth');

Route::get(

    '/auth/telegram/handoff/{token}',

    [TelegramAuthController::class, 'handoff']

)

    ->where('token', '[A-Za-z0-9]{64}')

    ->middleware('throttle:30,1')

    ->name('auth.telegram.handoff');

/*

|--------------------------------------------------------------------------

| Gast-routes

|--------------------------------------------------------------------------

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

    | Device Login Approval

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/login/approval',

        [LoginApprovalController::class, 'show']

    )->name('login.approval');

    Route::get(

        '/login/approval/status',

        [LoginApprovalController::class, 'status']

    )

        ->middleware('throttle:60,1')

        ->name('login.approval.status');

    Route::post(

        '/login/approval/complete',

        [LoginApprovalController::class, 'complete']

    )

        ->middleware('throttle:10,1')

        ->name('login.approval.complete');

    Route::post(

        '/login/approval/cancel',

        [LoginApprovalController::class, 'cancel']

    )

        ->middleware('throttle:10,1')

        ->name('login.approval.cancel');

    /*

    |--------------------------------------------------------------------------

    | Passwordless Login

    |--------------------------------------------------------------------------

    */

    Route::prefix('auth/email')->group(function () {

        Route::post(

            '/send-code',

            [EmailLoginController::class, 'sendCode']

        )

            ->middleware('throttle:10,1')

            ->name('email-login.send');

        Route::get(

            '/verify',

            [EmailLoginController::class, 'showVerifyForm']

        )->name('email-login.form');

        Route::post(

            '/verify',

            [EmailLoginController::class, 'verifyCode']

        )

            ->middleware('throttle:20,1')

            ->name('email-login.verify');

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

        Route::get(

            '/link/confirm',

            [EmailLoginController::class, 'showMagicLinkConfirmation']

        )->name('email-login.link.confirm');

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

    |

    | Alleen voor normaal inloggen / registreren via Google.

    | Gmail API gebruikt verderop een aparte OAuth-flow.

    |

    */

    Route::get(

        '/auth/google',

        [GoogleAuthController::class, 'redirect']

    )->name('google.redirect');

    Route::get(

        '/auth/google/callback',

        [GoogleAuthController::class, 'callback']

    )->name('google.callback');

    /*

    |--------------------------------------------------------------------------

    | GitHub OAuth

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/auth/github',

        [GitHubAuthController::class, 'redirect']

    )->name('github.redirect');

    Route::get(

        '/auth/github/callback',

        [GitHubAuthController::class, 'callback']

    )->name('github.callback');

    /*

    |--------------------------------------------------------------------------

    | Facebook OAuth

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/auth/facebook',

        [FacebookAuthController::class, 'redirect']

    )->name('facebook.redirect');

    Route::get(

        '/auth/facebook/callback',

        [FacebookAuthController::class, 'callback']

    )->name('facebook.callback');

    /*

    |--------------------------------------------------------------------------

    | LinkedIn OpenID Connect

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/auth/linkedin',

        [LinkedInAuthController::class, 'redirect']

    )->name('linkedin.redirect');

    Route::get(

        '/auth/linkedin/callback',

        [LinkedInAuthController::class, 'callback']

    )->name('linkedin.callback');

    /*

    |--------------------------------------------------------------------------

    | Microsoft / Hotmail OAuth

    |--------------------------------------------------------------------------

    |

    | Ondersteunt Entra-accounts en persoonlijke Microsoft-accounts

    | zoals Outlook, Hotmail en Live via de tenant "common".

    |

    */

    Route::get(

        '/auth/microsoft',

        [MicrosoftAuthController::class, 'redirect']

    )

        ->middleware('throttle:20,1')

        ->name('auth.microsoft.redirect');

    Route::get(

        '/auth/microsoft/callback',

        [MicrosoftAuthController::class, 'callback']

    )

        ->middleware('throttle:30,1')

        ->name('auth.microsoft.callback');

    /*

    |--------------------------------------------------------------------------

    | Telegram Login Widget

    |--------------------------------------------------------------------------

    |

    | De klassieke Telegram Login Widget stuurt de geverifieerde

    | Telegram-profielgegevens naar de callback hieronder.

    |

    | Telegram levert hierbij geen e-mailadres. Nieuwe gebruikers

    | ronden daarom hun registratie af via /auth/telegram/complete.

    |

    */

    Route::get(

        '/auth/telegram/callback',

        [TelegramAuthController::class, 'callback']

    )

        ->middleware('throttle:30,1')

        ->name('auth.telegram.callback');

    Route::get(

        '/auth/telegram/complete',

        [TelegramAuthController::class, 'showCompleteRegistration']

    )

        ->middleware('throttle:30,1')

        ->name('auth.telegram.complete');

    Route::post(

        '/auth/telegram/complete',

        [TelegramAuthController::class, 'completeRegistration']

    )

        ->middleware('throttle:10,1')

        ->name('auth.telegram.complete.submit');

    /*

    |--------------------------------------------------------------------------

    | TikTok OAuth

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/auth/tiktok',

        [TikTokAuthController::class, 'redirect']

    )->name('tiktok.redirect');

    Route::get(

        '/auth/tiktok/callback',

        [TikTokAuthController::class, 'callback']

    )->name('tiktok.callback');

    Route::get(

        '/auth/tiktok/complete',

        [TikTokAuthController::class, 'showCompleteRegistration']

    )->name('tiktok.complete');

    Route::post(

        '/auth/tiktok/complete',

        [TikTokAuthController::class, 'completeRegistration']

    )

        ->middleware('throttle:10,1')

        ->name('tiktok.complete.submit');

    /*

    |--------------------------------------------------------------------------

    | X OAuth voor gasten

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/auth/x',

        [XAuthController::class, 'redirect']

    )

        ->middleware('throttle:20,1')

        ->name('x.redirect');

    Route::get(

        '/auth/x/register',

        [XAuthController::class, 'registration']

    )

        ->middleware('throttle:20,1')

        ->name('x.registration');

    Route::post(

        '/auth/x/register',

        [XAuthController::class, 'completeRegistration']

    )

        ->middleware('throttle:10,1')

        ->name('x.registration.complete');

    /*

    |--------------------------------------------------------------------------

    | E-mailadres vergeten / Account Recovery

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/forgot-email',

        [ForgotEmailController::class, 'show']

    )->name('email.forgot');

    Route::post(

        '/forgot-email',

        [ForgotEmailController::class, 'identify']

    )

        ->middleware('throttle:5,1')

        ->name('email.forgot.identify');

    Route::get(

        '/forgot-email/verify',

        [ForgotEmailController::class, 'verifyForm']

    )->name('email.forgot.verify');

    Route::post(

        '/forgot-email/verify',

        [ForgotEmailController::class, 'verify']

    )

        ->middleware('throttle:10,1')

        ->name('email.forgot.verify.submit');

    Route::post(

        '/forgot-email/resend',

        [ForgotEmailController::class, 'resend']

    )

        ->middleware('throttle:5,1')

        ->name('email.forgot.resend');

    Route::get(

        '/forgot-email/result',

        [ForgotEmailController::class, 'result']

    )->name('email.forgot.result');

    /*

    |--------------------------------------------------------------------------

    | Wachtwoord vergeten

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/forgot-password',

        [UserController::class, 'forgotPassword']

    )->name('password.request');

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

    )->name('password.reset');

    Route::post(

        '/reset-password/{token}',

        [UserController::class, 'resetPassword']

    )

        ->middleware('throttle:10,1')

        ->name('password.update');

});

/*

|--------------------------------------------------------------------------

| Two-Factor Login Challenge

|--------------------------------------------------------------------------

*/

Route::get(

    '/two-factor-challenge',

    [TwoFactorChallengeController::class, 'show']

)

    ->middleware('throttle:30,1')

    ->name('two-factor.challenge');

Route::post(

    '/two-factor-challenge',

    [TwoFactorChallengeController::class, 'verify']

)

    ->middleware('throttle:10,1')

    ->name('two-factor.challenge.verify');

Route::post(

    '/two-factor-challenge/recovery',

    [TwoFactorChallengeController::class, 'verifyRecoveryCode']

)

    ->middleware('throttle:10,1')

    ->name('two-factor.challenge.recovery');

Route::post(

    '/two-factor-challenge/cancel',

    [TwoFactorChallengeController::class, 'cancel']

)

    ->middleware('throttle:10,1')

    ->name('two-factor.challenge.cancel');

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

*/

Route::get(

    '/verify',

    [UserController::class, 'verifyNotice']

)->name('verification.notice');

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
| TikTok Live View Counter
|--------------------------------------------------------------------------
|
| Publieke TikTok-video counter. De lookup-route verwerkt een ingevoerde
| TikTok-link, de show-route toont de teller en de stats-route levert
| actuele statistieken voor de automatische refresh in de frontend.
|
*/

Route::get(
    '/tools/live',
    [TikTokCounterController::class, 'liveCountsIndex']
)->name('live-counts.index');

Route::get(
    '/tools/tiktok-counter',
    [TikTokCounterController::class, 'index']
)->name('tiktok-counter.index');

Route::post(
    '/tools/tiktok-counter',
    [TikTokCounterController::class, 'lookup']
)
    ->middleware('throttle:20,1')
    ->name('tiktok-counter.lookup');

Route::get(
    '/tools/tiktok-counter/{videoId}',
    [TikTokCounterController::class, 'show']
)
    ->whereNumber('videoId')
    ->name('tiktok-counter.show');

Route::get(
    '/api/tools/tiktok-counter/{videoId}',
    [TikTokCounterController::class, 'stats']
)
    ->whereNumber('videoId')
    ->middleware('throttle:60,1')
    ->name('tiktok-counter.stats');

Route::get(
    '/api/tools/tiktok-counter/{videoId}/supplemental',
    [TikTokCounterController::class, 'supplemental']
)
    ->whereNumber('videoId')
    ->middleware('throttle:30,1')
    ->name('tiktok-counter.supplemental');

Route::get(
    '/api/tools/tiktok-counter/{videoId}/livecounts-cards',
    [TikTokCounterController::class, 'livecountsCards']
)
    ->whereNumber('videoId')
    ->middleware('throttle:30,1')
    ->name('tiktok-counter.livecounts-cards');


/*
|--------------------------------------------------------------------------
| TikTok Live Follower Counter
|--------------------------------------------------------------------------
*/

Route::get(
    '/tools/tiktok-follower-counter',
    [TikTokCounterController::class, 'followerIndex']
)->name('tiktok-follower-counter.index');

Route::post(
    '/tools/tiktok-follower-counter',
    [TikTokCounterController::class, 'followerLookup']
)
    ->middleware('throttle:20,1')
    ->name('tiktok-follower-counter.lookup');

Route::get(
    '/tools/tiktok-follower-counter/{username}',
    [TikTokCounterController::class, 'followerShow']
)
    ->where('username', '[A-Za-z0-9._]{1,24}')
    ->name('tiktok-follower-counter.show');

Route::get(
    '/api/tools/tiktok-follower-counter/search',
    [TikTokCounterController::class, 'followerSearch']
)
    ->middleware('throttle:60,1')
    ->name('tiktok-follower-counter.search');

Route::get(
    '/api/tools/tiktok-follower-counter/{username}/livecounts-cards',
    [TikTokCounterController::class, 'followerCards']
)
    ->where('username', '[A-Za-z0-9._]{1,24}')
    ->middleware('throttle:30,1')
    ->name('tiktok-follower-counter.livecounts-cards');

/*

|--------------------------------------------------------------------------

| Catalogus

|--------------------------------------------------------------------------

*/

Route::get(

    '/catalog',

    [UserController::class, 'catalog']

)->name('catalog');

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

)->name('cart');

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

    | Device Login Approvals

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/account/login-approval/pending',

        [LoginApprovalController::class, 'pending']

    )

        ->middleware('throttle:60,1')

        ->name('login-approval.pending');

    Route::post(

        '/account/login-approval/{challenge}/respond',

        [LoginApprovalController::class, 'respond']

    )

        ->whereUuid('challenge')

        ->middleware('throttle:20,1')

        ->name('login-approval.respond');

    /*

    |--------------------------------------------------------------------------

    | Afbeeldingen

    |--------------------------------------------------------------------------

    */

    Route::prefix('images')->group(function () {

        Route::get(

            '/claim',

            [ImageUploadController::class, 'claim']

        )->name('images.claim');

        Route::get(

            '/',

            [ImageEditorController::class, 'index']

        )->name('images.index');

        Route::get(

            '/{image}/edit',

            [ImageEditorController::class, 'edit']

        )

            ->whereNumber('image')

            ->name('images.editor');

        Route::get(

            '/{image}/file',

            [ImageEditorController::class, 'file']

        )

            ->whereNumber('image')

            ->name('images.file');

        Route::get(

            '/{image}/download',

            [ImageEditorController::class, 'downloadOriginal']

        )

            ->whereNumber('image')

            ->name('images.download');

        Route::post(

            '/{image}/process',

            [ImageEditorController::class, 'process']

        )

            ->whereNumber('image')

            ->middleware('throttle:60,1')

            ->name('images.process');

        Route::delete(

            '/{image}',

            [ImageEditorController::class, 'destroy']

        )

            ->whereNumber('image')

            ->name('images.destroy');

        Route::prefix('{image}/versions')

            ->whereNumber('image')

            ->group(function () {

                Route::get(

                    '/{version}/file',

                    [ImageEditorController::class, 'versionFile']

                )

                    ->whereNumber('version')

                    ->name('images.versions.file');

                Route::get(

                    '/{version}/download',

                    [ImageEditorController::class, 'downloadVersion']

                )

                    ->whereNumber('version')

                    ->name('images.versions.download');

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

    */

    Route::get(

        '/ai-chat',

        [AiChatController::class, 'index']

    )->name('ai.chat');

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

    )->name('favorites.index');

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

    )->name('security.index');

    Route::delete(

        '/account/security/history',

        [SecurityController::class, 'destroyHistory']

    )->name('security.history.destroy');

    /*

    |--------------------------------------------------------------------------

    | Authenticator / Two-Factor Authentication

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/account/security/authenticator',

        [TwoFactorAuthenticationController::class, 'show']

    )->name('two-factor.show');

    Route::post(

        '/account/security/authenticator/enable',

        [TwoFactorAuthenticationController::class, 'enable']

    )

        ->middleware('throttle:10,1')

        ->name('two-factor.enable');

    Route::post(

        '/account/security/authenticator/confirm',

        [TwoFactorAuthenticationController::class, 'confirm']

    )

        ->middleware('throttle:10,1')

        ->name('two-factor.confirm');

    Route::post(

        '/account/security/authenticator/cancel',

        [TwoFactorAuthenticationController::class, 'cancel']

    )

        ->middleware('throttle:10,1')

        ->name('two-factor.cancel');

    Route::post(

        '/account/security/authenticator/recovery-codes',

        [TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes']

    )

        ->middleware('throttle:5,1')

        ->name('two-factor.recovery-codes');

    Route::delete(

        '/account/security/authenticator',

        [TwoFactorAuthenticationController::class, 'disable']

    )

        ->middleware('throttle:5,1')

        ->name('two-factor.disable');

    /*

    |--------------------------------------------------------------------------

    | X-account koppelen

    |--------------------------------------------------------------------------

    */

    Route::post(

        '/account/x/link',

        [XAuthController::class, 'link']

    )

        ->middleware('throttle:10,1')

        ->name('x.link');

    /*

    |--------------------------------------------------------------------------

    | Gmail

    |--------------------------------------------------------------------------

    |

    | Gmail is uitsluitend beschikbaar voor ingelogde gebruikers.

    |

    | GmailController controleert daarnaast:

    |

    | - of het Mashal-account een @gmail.com adres gebruikt;

    | - of het gekoppelde Google-account hetzelfde e-mailadres heeft;

    | - of de gebruiker uitsluitend zijn eigen Gmail-data gebruikt.

    |

    | De normale Google-login gebruikt:

    |

    | /auth/google/callback

    |

    | De Gmail API gebruikt bewust:

    |

    | /gmail/callback

    |

    */

    /*

    |--------------------------------------------------------------------------

    | Gmail koppelen

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/gmail/connect',

        [GmailController::class, 'redirectToGoogle']

    )

        ->middleware('throttle:20,1')

        ->name('gmail.connect');

    /*

    |--------------------------------------------------------------------------

    | Gmail OAuth Callback

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/gmail/callback',

        [GmailController::class, 'handleGoogleCallback']

    )

        ->middleware('throttle:30,1')

        ->name('gmail.callback');

    /*

    |--------------------------------------------------------------------------

    | Gmail Inbox

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/mail',

        [GmailController::class, 'inbox']

    )

        ->middleware('throttle:60,1')

        ->name('gmail.inbox');

    /*

    |--------------------------------------------------------------------------

    | Gmail Bericht Openen

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/mail/message/{id}',

        [GmailController::class, 'show']

    )

        ->middleware('throttle:60,1')

        ->name('gmail.show');

    /*

    |--------------------------------------------------------------------------

    | Gmail Bericht Versturen

    |--------------------------------------------------------------------------

    */

    Route::post(

        '/mail/send',

        [GmailController::class, 'send']

    )

        ->middleware('throttle:20,1')

        ->name('gmail.send');

    /*

    |--------------------------------------------------------------------------

    | Gmail Ontkoppelen

    |--------------------------------------------------------------------------

    */

    Route::post(

        '/gmail/disconnect',

        [GmailController::class, 'disconnect']

    )

        ->middleware('throttle:10,1')

        ->name('gmail.disconnect');

    /*

    |--------------------------------------------------------------------------

    | Mijn account

    |--------------------------------------------------------------------------

    */

    Route::get(

        '/account',

        [UserController::class, 'account']

    )->name('account');

    Route::put(

        '/account',

        [UserController::class, 'updateAccount']

    )->name('account.update');

    /*

    |--------------------------------------------------------------------------

    | Herstel-e-mailadres

    |--------------------------------------------------------------------------

    */

    Route::post(

        '/account/recovery-email',

        [RecoveryEmailController::class, 'send']

    )

        ->middleware('throttle:5,1')

        ->name('account.recovery-email.send');

    Route::get(

        '/account/recovery-email/verify',

        [RecoveryEmailController::class, 'verifyForm']

    )->name('account.recovery-email.verify');

    Route::post(

        '/account/recovery-email/verify',

        [RecoveryEmailController::class, 'verify']

    )

        ->middleware('throttle:10,1')

        ->name('account.recovery-email.verify.submit');

    Route::post(

        '/account/recovery-email/resend',

        [RecoveryEmailController::class, 'resend']

    )

        ->middleware('throttle:5,1')

        ->name('account.recovery-email.resend');

    Route::delete(

        '/account/recovery-email',

        [RecoveryEmailController::class, 'destroy']

    )

        ->middleware('throttle:5,1')

        ->name('account.recovery-email.destroy');

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

    )->name('checkout');

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

    | UserController moet daarnaast zelf controleren of de gebruiker

    | administratorrechten heeft.

    |

    */

    Route::prefix('admin')->group(function () {

        Route::get(

            '/',

            [UserController::class, 'admin']

        )->name('admin.dashboard');

        Route::get(

            '/users',

            [UserController::class, 'index']

        )->name('users.index');

        Route::get(

            '/users/create',

            [UserController::class, 'create']

        )->name('users.create');

        Route::post(

            '/users',

            [UserController::class, 'store']

        )->name('users.store');

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

        Route::delete(

            '/users/{user}',

            [UserController::class, 'destroy']

        )

            ->whereNumber('user')

            ->name('users.destroy');

    });

});

/*

|--------------------------------------------------------------------------

| Mashal AI Workspace V5

|--------------------------------------------------------------------------

*/

require __DIR__ . '/ai-workspace.php';

/*

|--------------------------------------------------------------------------

| Mashal AI Studio V6

|--------------------------------------------------------------------------

*/

require __DIR__ . '/ai-studio.php';

/*

|--------------------------------------------------------------------------

| Passkeys

|--------------------------------------------------------------------------

*/

require __DIR__ . '/passkeys.php';

require __DIR__ . '/live-chat.php';
