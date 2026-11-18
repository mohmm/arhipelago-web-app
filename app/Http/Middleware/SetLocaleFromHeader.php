<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Answers the app in the language it is set to.
 *
 * The app sends its language in `Accept-Language` on every request, so names,
 * descriptions, validation messages and Maya's replies come back in Romanian,
 * English or Arabic. Anything else falls back to Romanian.
 */
class SetLocaleFromHeader
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('translatable.languages'));

        // Symfony answers with the first supported language when none matches,
        // which is why Romanian comes first in config/translatable.php.
        app()->setLocale($request->getPreferredLanguage($supported) ?? config('translatable.fallback'));

        return $next($request);
    }
}
