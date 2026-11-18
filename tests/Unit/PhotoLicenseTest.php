<?php

use App\Enums\PhotoLicense;

it('takes only the Commons licences that need no credit', function (string $commons, ?PhotoLicense $expected) {
    expect(PhotoLicense::fromCommons($commons))->toBe($expected);
})->with([
    ['CC0', PhotoLicense::Cc0],
    ['Public domain', PhotoLicense::PublicDomain],
    ['pd', PhotoLicense::PublicDomain],
    // These ask for the author's name under the photo, which the app never prints.
    ['CC BY 2.0', null],
    ['CC BY-SA 4.0', null],
    ['GFDL', null],
]);
