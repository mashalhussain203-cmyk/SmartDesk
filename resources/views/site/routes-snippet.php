/*
|--------------------------------------------------------------------------
| Publieke informatiepagina's
|--------------------------------------------------------------------------
*/

Route::view('/privacy', 'site.privacy')
    ->name('privacy');

Route::view('/contact', 'site.contact')
    ->name('contact');

Route::view('/over-ons', 'site.about')
    ->name('about');

Route::view('/voorwaarden', 'site.terms')
    ->name('terms');
