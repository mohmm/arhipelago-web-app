<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessAdminPanel
{
    /**
     * Handle an incoming request.
     *
     * Opens the back-office to anyone holding the `admin.view` section
     * permission. What they can actually reach inside it is decided per route
     * by the policies, so granting a new role access is a permission change
     * rather than a code change.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->can('admin.view')) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}
