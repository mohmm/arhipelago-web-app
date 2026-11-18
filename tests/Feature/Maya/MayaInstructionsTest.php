<?php

use App\Settings\MayaSettings;

/*
 * Maya's instructions were first written for Smartsense. She introduces herself
 * with this app's own name instead, which is APP_NAME.
 */
it('names this app in Maya\'s instructions', function () {
    expect(app(MayaSettings::class)->instructions)
        ->toContain('You are Maya, the travel assistant of '.config('app.name').',');
});
