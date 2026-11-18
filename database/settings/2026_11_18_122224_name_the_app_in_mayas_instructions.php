<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Maya introduces herself with the app's name. Her instructions were first
 * written for Smartsense, so that name becomes this app's own, APP_NAME.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->update('maya.instructions', fn (string $instructions): string => str_replace('Smartsense', config('app.name'), $instructions));
    }
};
