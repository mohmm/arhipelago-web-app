<?php

use App\Services\Maya\MayaUnavailable;

use function Pest\Laravel\get;

/*
 * Travellers know the assistant by the name in config/maya.php. "Maya" is only
 * the feature's name in the code, so no text may name her.
 */
it('shares the assistant\'s name with every page', function () {
    get('/')->assertOk()->assertInertia(fn ($page) => $page->where('assistant', config('maya.name')));
});

it('names the assistant when it cannot answer', function () {
    app()->setLocale('en');

    expect((new MayaUnavailable)->render()->getData()->message)
        ->toBe(config('maya.name').' cannot answer right now. Please try again in a moment.');
});

it('never names Maya in the texts', function (string $language) {
    $texts = json_decode(file_get_contents(lang_path("{$language}.json")), true);

    foreach ($texts as $key => $text) {
        expect($key.' '.$text)->not->toContain('Maya');
    }
})->with(['en', 'ro', 'ar']);
