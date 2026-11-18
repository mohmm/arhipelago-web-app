<?php

/*
 * The home page shows the app in pairs of phone screens, side by side, as the
 * app's design does. The pairs are only right while both phones are the same
 * size, which is easy to break and does not fail loudly in the browser.
 */

/**
 * The source of the home page.
 */
function welcomePageSource(): string
{
    return (string) file_get_contents(resource_path('js/pages/welcome.tsx'));
}

it('shows every app screen at the size it was made', function () {
    preg_match_all("~'/images/app/([a-z-]+\.webp)'~", welcomePageSource(), $screens);
    preg_match('~width=\{(\d+)\} height=\{(\d+)\}~', welcomePageSource(), $declared);

    // Guards the patterns: empty matches would pass and prove nothing.
    expect($screens[1])->toHaveCount(4)
        ->and($declared)->toHaveCount(3);

    foreach ($screens[1] as $file) {
        [$width, $height] = getimagesize(public_path("images/app/{$file}"));

        expect([$width, $height])->toBe([(int) $declared[1], (int) $declared[2]], "{$file} is not the size the page declares");
    }
});

it('keeps the two phones of a pair level and the same height', function () {
    // A flex row stretches its items to the tallest one. With the second phone
    // pushed 40px down, the first was drawn 40px taller than the second.
    expect(welcomePageSource())->toContain('flex items-start justify-center')
        ->and(welcomePageSource())->not->toMatch('~mt-\d+ w-1/2~');
});
