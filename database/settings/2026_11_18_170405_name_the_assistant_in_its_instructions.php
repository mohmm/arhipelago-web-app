<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * The assistant introduces itself by its name. Its instructions were first
 * written for Maya, Smartsense's assistant, so that name becomes this app's
 * own, config('maya.name'). The admin panel can still edit them afterwards.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->update('maya.instructions', fn (string $instructions): string => str_replace('Maya', config('maya.name'), $instructions));
    }
};
