<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Experience;
use App\Models\LegalDocument;
use App\Models\PointOfInterest;
use App\Models\Post;
use App\Models\User;
use App\Policies\RolePolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Polymorphic columns store these short names rather than class names,
        // so renaming a class never orphans a row. See the matching migration.
        Relation::enforceMorphMap([
            'user' => User::class,
            'post' => Post::class,
            'point_of_interest' => PointOfInterest::class,
            'experience' => Experience::class,
            'comment' => Comment::class,
            'legal_document' => LegalDocument::class,
        ]);

        // Role lives in the Spatie namespace, so policy auto-discovery cannot find it.
        Gate::policy(Role::class, RolePolicy::class);

        // Outside production every email can be redirected to one inbox, so a
        // copied database full of real addresses never mails a real person.
        $alwaysTo = config('notifications.always_to');

        if ($alwaysTo != null && ! $this->app->environment('production')) {
            Mail::alwaysTo($alwaysTo);
        }

        $this->configureRateLimiting();
    }

    /**
     * The outer limits on the authentication endpoints, by address.
     *
     * Looser than the per-credential lockout inside LoginRequest, which is what
     * a person who mistypes a password meets; these only stop one host from
     * spraying many accounts or flooding inboxes with reset links.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('auth-register', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('auth-login', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('auth-password', function (Request $request) {
            return [
                Limit::perMinute(6)->by($request->ip()),
                Limit::perMinute(3)->by(strtolower((string) $request->input('email')).'|password-reset'),
            ];
        });

        // Every message to Maya is a paid model call.
        RateLimiter::for('maya', function (Request $request) {
            return Limit::perMinute((int) config('maya.messages_per_minute'))
                ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()));
        });

        // Posting, commenting, liking, reviewing and following: generous for a
        // person, tight enough that a script cannot flood the community.
        RateLimiter::for('community', function (Request $request) {
            return Limit::perMinute(30)->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()));
        });
    }
}
