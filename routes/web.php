<?php



use App\Http\Controllers\AiChatController;

use App\Http\Controllers\EmailLoginController;

use App\Http\Controllers\FacebookAuthController;

use App\Http\Controllers\FavoriteController;

use App\Http\Controllers\ForgotEmailController;

use App\Http\Controllers\GitHubAuthController;

use App\Http\Controllers\GoogleAuthController;

use App\Http\Controllers\ImageEditorController;

use App\Http\Controllers\ImageUploadController;

use App\Http\Controllers\LinkedInAuthController;

use App\Http\Controllers\LoginApprovalController;

use App\Http\Controllers\RecoveryEmailController;

use App\Http\Controllers\SecurityController;

use App\Http\Controllers\TikTokAuthController;

use App\Http\Controllers\TwoFactorAuthenticationController;

use App\Http\Controllers\TwoFactorChallengeController;

use App\Http\Controllers\UserController;

use App\Http\Controllers\XAuthController;

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



| Publieke informatiepagina's



|--------------------------------------------------------------------------



|



| Deze pagina's zijn voor iedere bezoeker toegankelijk en geven duidelijke



| informatie over Mashal Studio, privacy, contact en gebruiksvoorwaarden.



|



*/





Route::view(



    '/privacy',



    'site.privacy'



)->name('privacy');





Route::view(



    '/contact',



    'site.contact'



)->name('contact');





Route::view(



    '/over-ons',



    'site.about'



)->name('about');





Route::view(



    '/voorwaarden',



    'site.terms'



)->name('terms');





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
| X OAuth callback
|--------------------------------------------------------------------------
|
| Deze callback staat bewust buiten de guest- en auth-groepen.
| Dezelfde callback verwerkt login, registratie en accountkoppeling.
| XAuthController controleert zelf state, PKCE, sessie en gebruiker.
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



    | Device login approval



    |--------------------------------------------------------------------------



    |



    | De nieuwe browser blijft gast totdat een reeds ingelogd apparaat het



    | juiste nummer heeft gekozen.



    |



    */





    Route::get(



        '/login/approval',



        [LoginApprovalController::class, 'show']



    )



        ->name('login.approval');





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



        [LinkedInAuthController::class, 'redirect']



    )



        ->name('linkedin.redirect');





    Route::get(



        '/auth/linkedin/callback',



        [LinkedInAuthController::class, 'callback']



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
    | X OAuth voor gasten
    |--------------------------------------------------------------------------
    |
    | Start X OAuth voor login of eerste registratie.
    | Nieuwe X-gebruikers kunnen daarna hun Mashal-account afmaken.
    |
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



    | E-mailadres vergeten / Account recovery



    |--------------------------------------------------------------------------



    |



    | Veilige Gmail-achtige recovery flow:



    |



    | 1. naam + herstel-e-mailadres controleren;



    | 2. een 6-cijferige recoverycode versturen;



    | 3. recoverycode controleren;



    | 4. pas na succesvolle verificatie het gekoppelde account tonen.



    |



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



| Two-Factor Login Challenge



|--------------------------------------------------------------------------



|



| Deze routes staan bewust buiten de auth-groep.



| Tijdens deze stap is de gebruiker nog niet definitief ingelogd.



|



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



    | Device login approvals op reeds ingelogde apparaten



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



    | Authenticator / Two-Factor Authentication



    |--------------------------------------------------------------------------



    |



    | Hiermee kan een ingelogde gebruiker:



    |



    | - Authenticator 2FA instellen;



    | - de QR-code bekijken;



    | - de eerste 6-cijferige code bevestigen;



    | - een lopende setup annuleren;



    | - nieuwe recovery codes genereren;



    | - Authenticator 2FA uitschakelen.



    |



    */





    Route::get(



        '/account/security/authenticator',



        [TwoFactorAuthenticationController::class, 'show']



    )



        ->name('two-factor.show');





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

    |

    | Alleen ingelogde gebruikers mogen een X-account aan hun bestaande

    | Mashal-account koppelen.

    |

    */



    Route::post(

        '/account/x/link',

        [XAuthController::class, 'link']

    )

        ->middleware('throttle:10,1')

        ->name('x.link');





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



    | Herstel-e-mailadres



    |--------------------------------------------------------------------------



    |



    | Een nieuw hersteladres wordt pas actief nadat de gebruiker de



    | 6-cijferige code heeft bevestigd.



    |



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



    )



        ->name('account.recovery-email.verify');





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
