<?php

use App\Settings\MayaSettings;

/*
 * The instructions were first written for Smartsense and its assistant, Maya.
 * The assistant introduces itself with its own name instead, config('maya.name'),
 * and with this app's name, APP_NAME.
 */
it('names the assistant and this app in the instructions', function () {
    expect(app(MayaSettings::class)->instructions)
        ->toContain('You are '.config('maya.name').', the travel assistant of '.config('app.name').',');
});
