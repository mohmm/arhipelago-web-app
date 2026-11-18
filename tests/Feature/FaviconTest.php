<?php

use function Pest\Laravel\get;

/*
 * The browser icons are drawn from the brand symbol, the same artwork as
 * public/images/logo-mark.png. They once shipped as Laravel's default red "L"
 * for a long time without anyone noticing, because a wrong favicon never fails
 * loudly — it just quietly brands every tab, bookmark and home screen as
 * somebody else's product. A framework upgrade or a fresh starter-kit install
 * restores those defaults, so the identity is asserted rather than assumed.
 */

/** Laravel's own red, FF2D20, rounded the way inkColours() rounds. */
const LARAVEL_RED = 'f22';

/**
 * How often each colour of an image's visible ink occurs, most common first.
 * Colours are rounded to 16 steps a channel, so neighbouring shades count as
 * one; white and transparent pixels are not ink.
 *
 * @return array<string, int>
 */
function inkColours(string $png): array
{
    $image = imagecreatefromstring($png);
    $colours = [];

    for ($x = 0; $x < imagesx($image); $x++) {
        for ($y = 0; $y < imagesy($image); $y++) {
            $pixel = imagecolorsforindex($image, imagecolorat($image, $x, $y));

            if ($pixel['alpha'] < 64 && min($pixel['red'], $pixel['green'], $pixel['blue']) < 200) {
                $colour = sprintf('%x%x%x', $pixel['red'] >> 4, $pixel['green'] >> 4, $pixel['blue'] >> 4);
                $colours[$colour] = ($colours[$colour] ?? 0) + 1;
            }
        }
    }

    arsort($colours);

    return $colours;
}

/**
 * The images inside favicon.ico, keyed by their width.
 *
 * @return array<int, string>
 */
function faviconImages(): array
{
    $ico = file_get_contents(public_path('favicon.ico'));
    $images = [];

    for ($i = 0; $i < unpack('v', substr($ico, 4, 2))[1]; $i++) {
        $entry = unpack('Cwidth/x7/Vsize/Voffset', substr($ico, 6 + ($i * 16), 16));
        $images[$entry['width']] = substr($ico, $entry['offset'], $entry['size']);
    }

    return $images;
}

it('ships both browser icons', function (string $file) {
    expect(public_path($file))->toBeFile()
        ->and(filesize(public_path($file)))->toBeGreaterThan(0);
})->with(['favicon.ico', 'apple-touch-icon.png']);

it('draws the browser icons from the brand symbol, not Laravel\'s mark', function (string $icon) {
    $png = $icon === 'favicon.ico' ? faviconImages()[32] : file_get_contents(public_path($icon));
    $colours = inkColours($png);

    expect(inkColours(file_get_contents(public_path('images/logo-mark.png'))))->toHaveKey(array_key_first($colours))
        ->and($colours)->not->toHaveKey(LARAVEL_RED);
})->with(['favicon.ico', 'apple-touch-icon.png']);

/*
 * Browsers pick an entry out of the .ico by size, so the file has to offer the
 * three the platforms actually ask for: 16 for a standard-density tab, 32 for a
 * retina tab and for Windows, 48 for the taskbar and large list views. A
 * single-entry .ico gets downscaled on the fly and looks like mush.
 */
it('offers the icon at every size a browser asks for', function () {
    expect(array_keys(faviconImages()))->toEqualCanonicalizing([16, 32, 48]);
});

/*
 * iOS paints a transparent pixel black and then applies its own corner radius,
 * so a touch icon with transparent corners gets four black wedges on the home
 * screen. It is flattened onto white instead, which means colour type 2 (RGB)
 * rather than 6 (RGBA) in the PNG header.
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
        ->assertSee('<link rel="apple-touch-icon" href="/apple-touch-icon.png">', false)
        ->assertDontSee('favicon.svg', false);
});
