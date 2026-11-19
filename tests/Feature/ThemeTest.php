<?php

use function Pest\Laravel\get;

/*
 * The theme lives in resources/css/app.css. These checks keep the rest of the
 * product in step with it: the page loads the fonts it names, the emails use
 * its main colour, and text on its colours stays readable (WCAG AA, 4.5:1).
 */

/** A custom property's value in one block of the theme, such as ':root'. */
function themeValue(string $block, string $name): string
{
    $css = file_get_contents(resource_path('css/app.css'));

    preg_match('/^'.preg_quote($block, '/').' \{\n(.*?)^\}/ms', $css, $body);
    preg_match('/^\s*--'.preg_quote($name, '/').':\s*([^;]+);/m', $body[1] ?? '', $value);

    return strtolower(trim($value[1] ?? ''));
}

/** The WCAG contrast ratio of two #rrggbb colours. */
function contrastRatio(string $first, string $second): float
{
    $luminance = function (string $hex): float {
        $channels = array_map(function (string $pair): float {
            $value = hexdec($pair) / 255;

            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, str_split(ltrim($hex, '#'), 2));

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    };

    $lighter = max($luminance($first), $luminance($second));
    $darker = min($luminance($first), $luminance($second));

    return ($lighter + 0.05) / ($darker + 0.05);
}

it('loads the fonts the theme names', function () {
    $families = collect(['font-sans', 'font-display'])->map(
        fn (string $token): string => str(themeValue('@theme', $token))->before(',')->trim("' ")->lower()->replace(' ', '-')->toString(),
    );

    expect($families->filter()->all())->toHaveCount(2);

    $page = get('/')->assertSuccessful()->assertSee('https://fonts.bunny.net/css?family=', false);

    foreach ($families as $family) {
        $page->assertSee("{$family}:", false);
    }
});

it('gives the emails the theme\'s main colour', function () {
    $mail = file_get_contents(resource_path('views/vendor/mail/html/themes/default.css'));
    $primary = themeValue(':root', 'primary');

    preg_match('/\.button-primary \{(.*?)\}/s', $mail, $button);
    preg_match('/\.panel \{(.*?)\}/s', $mail, $panel);

    expect($primary)->toMatch('/^#[0-9a-f]{6}$/')
        ->and($button[1])->toContain("background-color: {$primary};")
        ->and($panel[1])->toContain("border-left: {$primary} solid 4px;");
});

it('keeps text on the theme\'s colours readable', function (string $block, string $text, string $background) {
    expect(contrastRatio(themeValue($block, $text), themeValue($block, $background)))->toBeGreaterThanOrEqual(4.5);
})->with([
    'buttons' => [':root', 'primary-foreground', 'primary'],
    'branded buttons' => [':root', 'brand-foreground', 'brand'],
    'highlighted items' => [':root', 'accent-foreground', 'accent'],
    'links' => [':root', 'primary', 'background'],
    'links in the dark' => ['.dark', 'primary', 'background'],
    'highlighted items in the dark' => ['.dark', 'accent-foreground', 'accent'],
]);
