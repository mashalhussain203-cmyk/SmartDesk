<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetSiteLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('site_locale', config('app.locale', 'nl'));

        // Never use arbitrary session values as locale identifiers.
        if (! in_array($locale, ['nl', 'ur'], true)) {
            $locale = 'nl';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
