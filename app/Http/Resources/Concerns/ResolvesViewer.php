<?php

namespace App\Http\Resources\Concerns;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Helpers shared by the API resources.
 */
trait ResolvesViewer
{
    /**
     * The signed-in traveller, also on public endpoints when the app sends its token.
     */
    protected function viewer(Request $request): ?User
    {
        $user = $request->user('sanctum');

        return $user instanceof User ? $user : null;
    }

    /**
     * A full address the app can load, built on the host it called.
     */
    protected function absoluteUrl(?string $url): ?string
    {
        if ($url == null || $url == '') {
            return null;
        }

        return Str::startsWith($url, ['http://', 'https://']) ? $url : url($url);
    }
}
