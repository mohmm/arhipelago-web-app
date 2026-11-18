<?php

use function Pest\Laravel\get;

/*
 * The browser icons are generated from the brand mark in
 * public/images/smartsense-mark.png. They shipped as Laravel's default red "L"
 * for a long time without anyone noticing, because a wrong favicon never fails
 * loudly — it just quietly brands every tab, bookmark and home screen as
 * somebody else's product. A framework upgrade or a fresh starter-kit install
 * restores those defaults, so the identity is asserted rather than assumed.
 */

/** Laravel's own mark, the colour that was shipping here before. */
const LARAVEL_RED = 'FF2D20';

/** The Smartsense mark, sampled from the artwork. */
const BRAND_RED = 'E24334';

it('ships all three browser icons', function (string $file) {
    expect(public_path($file))->toBeFile()
        ->and(filesize(public_path($file)))->toBeGreaterThan(0);
})->with(['favicon.ico', 'favicon.svg', 'apple-touch-icon.png']);

it('carries the Smartsense mark in the scalable icon, not Laravel\'s', function () {
    $svg = file_get_contents(public_path('favicon.svg'));

    expect($svg)->toContain(BRAND_RED)
        ->and(strtoupper($svg))->not->toContain(LARAVEL_RED);
});

/*
 * Browsers pick an entry out of the .ico by size, so the file has to offer the
 * three the platforms actually ask for: 16 for a standard-density tab, 32 for a
 * retina tab and for Windows, 48 for the taskbar and large list views. A
 * single-entry .ico gets downscaled on the fly and looks like mush.
 */
it('offers the icon at every size a browser asks for', function () {
    $ico = file_get_contents(public_path('favicon.ico'));
    $count = unpack('vcount', substr($ico, 4, 2))['count'];

    $sizes = [];
    for ($i = 0; $i < $count; $i++) {
        $entry = substr($ico, 6 + ($i * 16), 16);
        $sizes[] = ord($entry[0]);
    }

    expect($sizes)->toEqualCanonicalizing([16, 32, 48]);
});

/*
 * iOS paints a transparent pixel black and then applies its own corner radius,
 * so a touch icon with the mark's rounded corners left transparent gets four
 * black wedges on the home screen. It is flattened onto the brand red instead,
 * which means colour type 2 (RGB) rather than 6 (RGBA) in the PNG header.
 */
it('keeps the home screen icon opaque', function () {
    $png = file_get_contents(public_path('apple-touch-icon.png'));
    $header = unpack('Nwidth/Nheight/Cdepth/Ccolour', substr($png, 16, 10));

    expect($header['colour'])->toBe(2)
        ->and($header['width'])->toBe(180)
        ->and($header['height'])->toBe(180);
});

it('links every icon from the page head', function () {
    get('/')
        ->assertSuccessful()
        ->assertSee('<link rel="icon" href="/favicon.ico" sizes="any">', false)
        ->assertSee('<link rel="icon" href="/favicon.svg" type="image/svg+xml">', false)
        ->assertSee('<link rel="apple-touch-icon" href="/apple-touch-icon.png">', false);
});
