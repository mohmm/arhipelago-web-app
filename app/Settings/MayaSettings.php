<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * What Maya is told about who she is and how she behaves, edited in the admin
 * panel. The technical rules (her tools and the shape of her replies) stay in
 * code, so editing this text can change her tone but never break the app.
 */
class MayaSettings extends Settings
{
    public string $instructions;

    public static function group(): string
    {
        return 'maya';
    }
}
