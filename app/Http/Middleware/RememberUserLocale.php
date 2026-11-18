<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps a person's stored language in step with the one they are reading in.
 *
 * The locale lives in the URL, which is fine until something has to write to
 * them from outside a request — a webhook, a queued job, the nightly reconcile.
 * None of those have a URL to read, so without a stored preference every one of
 * their emails would go out in the application default. Recording the choice as
 * it is made means the language switcher in the header quietly doubles as an
 * email setting, with no second control to keep in step.
 */
class RememberUserLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $locale = app()->getLocale();

        // Written only when it actually changes: this runs on every request,
        // and an UPDATE per page view to store the value already there is a
        // write nobody asked for.
        if ($user != null && $locale != '' && $user->locale != $locale) {
            $user->forceFill(['locale' => $locale])->saveQuietly();
        }

        return $next($request);
    }
}
