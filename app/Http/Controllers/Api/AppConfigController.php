<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * What the app needs to know about this server that is not content: where the
 * legal documents are published, in the app's language where one exists, and
 * where the map tiles come from.
 */
class AppConfigController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $locale = in_array(app()->getLocale(), LaravelLocalization::getSupportedLanguagesKeys())
            ? app()->getLocale()
            : config('app.fallback_locale');

        $page = fn (string $path): string => LaravelLocalization::getLocalizedURL($locale, url($path), [], true);

        return response()->json(['data' => [
            'legal' => [
                'terms_url' => $page('/terms'),
                'privacy_url' => $page('/privacy'),
                'cookies_url' => $page('/cookies'),
                'ai_url' => $page('/ai'),
            ],
            // The website's front page, which the app shares alongside a place or a route.
            'website_url' => $page('/'),
            'languages' => array_keys(config('translatable.languages')),
            'map_tiles' => config('services.map_tiles'),
            // Whether the app offers to create an account: not when it has a
            // fixed set of accounts (config/accounts.php).
            'sign_up' => config('accounts.limit') === null,
        ]]);
    }
}
