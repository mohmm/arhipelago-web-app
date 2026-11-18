<?php

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

use function Pest\Laravel\get;

it('loads welcome page successfully', function () {
    get('/')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('welcome'));
});

/*
 * The footer carries two attributions that are obligations rather than
 * decoration: the EU funding acknowledgement the grant requires, and the ANPC
 * alternative-dispute-resolution pictogram Order 449/2022 requires of a
 * Romanian trader selling online. Both are easy to lose in a redesign and
 * neither fails loudly in the browser — a missing image is a broken icon, and a
 * missing translation renders the English key to a Romanian visitor.
 */
it('serves the artwork both mandatory footer attributions depend on', function (string $file) {
    expect(public_path("images/{$file}"))->toBeFile();
})->with([
    // The supplied emblem is white artwork; the second file is the dark-text
    // variant, without which the wording is invisible on a light background.
    'EN-Co-Funded-by-the-EU_POS.png',
    'eu-co-funded-dark.png',
    'anpc-sal.png',
]);

it('translates both mandatory footer attributions', function (string $key) {
    foreach (['ro', 'en'] as $locale) {
        $strings = json_decode(file_get_contents(lang_path("{$locale}.json")), true);

        expect($strings)->toHaveKey($key)
            ->and($strings[$key])->not->toBe('');
    }
})->with([
    'Co-funded by the European Union',
    'ANPC — Alternative dispute resolution',
    'Funded by the European Union. Views and opinions expressed are however those of the author(s) only and do not necessarily reflect those of the European Union or the European Health and Digital Executive Agency (HADEA). Neither the European Union nor the granting authority can be held responsible for them.',
]);

it('keeps the EU emblem in proportion when the footer stacks', function () {
    // The emblem sits in a flex container that runs as a column on a phone, and
    // flex stretches its children across the cross axis by default — the width,
    // here. With only `h-10 w-auto` the emblem was pulled to the full column
    // width at a fixed 40px height: 358x40 on a 390px screen, a ratio of 8.95
    // against the artwork's 4.77, so the flag rendered squashed flat. The EU
    // visual identity rules do not permit distorting the emblem, which makes
    // this a grant-compliance detail rather than a cosmetic one, and it is
    // invisible on a desktop viewport where the row direction hides it.
    $footer = (string) file_get_contents(resource_path('js/components/site-footer.tsx'));

    preg_match_all('/<img\b[^>]*?src="\/images\/(?<file>[^"]+)"[^>]*?className="(?<classes>[^"]*)"/s', $footer, $images, PREG_SET_ORDER);

    $emblems = array_values(array_filter($images, fn (array $image): bool => str_contains(strtolower($image['file']), 'co-funded')));

    // Both theme variants, or the regex stopped reaching what it claims to read.
    expect($emblems)->toHaveCount(2);

    // Collected rather than asserted one by one, so a failure names the file.
    $stretchable = array_column(
        array_filter($emblems, fn (array $emblem): bool => ! str_contains($emblem['classes'], 'self-start')),
        'file',
    );

    expect($stretchable)->toBe([]);
});

it('shows each theme the EU emblem it can be read on', function () {
    // The white-lettered emblem on a light page leaves the mandatory wording
    // invisible, which is how the footer shipped once.
    $footer = (string) file_get_contents(resource_path('js/components/site-footer.tsx'));

    preg_match_all('/<img\b[^>]*?src="\/images\/(?<file>[^"]+)"[^>]*?className="(?<classes>[^"]*)"/s', $footer, $images, PREG_SET_ORDER);

    $classesOf = collect($images)->mapWithKeys(fn (array $image): array => [$image['file'] => explode(' ', $image['classes'])]);

    expect($classesOf['eu-co-funded-dark.png'])->toContain('dark:hidden')
        ->and($classesOf['EN-Co-Funded-by-the-EU_POS.png'])->toContain('hidden')
        ->and($classesOf['EN-Co-Funded-by-the-EU_POS.png'])->toContain('dark:block');
});

it('shares the company details the footer prints', function () {
    get('/')->assertInertia(fn ($page) => $page
        ->where('company.legal_name', config('company.legal_name'))
        ->where('company.phone', config('company.phone'))
        ->where('company.address', config('company.address'))
        ->where('company.website', config('company.website'))
        ->where('company.share_capital', config('company.share_capital')));
});

it('labels the company ID and the VAT ID as two different numbers', function () {
    // The Romanian footer once printed "CUI" in front of both.
    $romanian = json_decode((string) file_get_contents(lang_path('ro.json')), true);

    expect($romanian['Company ID'])->toBe('CUI')
        ->and($romanian['VAT ID'])->toBe('Cod TVA');
});

it('lets a visitor change the language and the theme from the header and the footer', function (string $component) {
    $source = (string) file_get_contents(resource_path("js/components/{$component}"));

    expect($source)->toContain('<LanguageSwitcher')
        ->and($source)->toContain('<AppearanceToggleDropdown');
})->with(['site-header.tsx', 'site-footer.tsx']);

it('points the ANPC pictogram at the complaints portal', function () {
    // ANPC fixes the destination; a typo here is a compliance finding rather
    // than a broken link, so the exact URL is asserted.
    expect(file_get_contents(resource_path('js/lib/site.ts')))
        ->toContain("export const ANPC_SAL_URL = 'https://reclamatiisal.anpc.ro/';");
});

it('lands a visitor in Romanian rather than in whatever their browser asks for', function () {
    /*
     * These two settings are one decision and have to move together.
     *
     * Romanian carries no prefix, so the canonical address of every page —
     * including all 34.403 directory entries — is the bare path. Honouring
     * Accept-Language would take a browser announcing `en-US` off that path and
     * redirect it to `/en/…`; stock Chrome announces `en-US`, and so, typically,
     * does Googlebot. That would point most crawlers away from the canonical
     * pages, and give a Romanian hotel its first view of a Romanian-first
     * product in English.
     *
     * Asserted at config level because mcamara skips negotiation entirely under
     * `runningInConsole()`, which is always true here.
     */
    expect(config('laravellocalization.useAcceptLanguageHeader'))->toBeFalse()
        ->and(config('laravellocalization.hideDefaultLocaleInURL'))->toBeTrue()
        ->and(config('app.locale'))->toBe('ro');
});

it('uses Romanian as the primary locale and English as secondary', function () {
    // Locale-prefixed routing (e.g. /en, /ro) is served in the browser via
    // mcamara's URL prefix; the test harness resolves localization at the
    // application root, so the locale contract is asserted at the config level.
    expect(config('app.locale'))->toBe('ro');

    $supported = array_keys(LaravelLocalization::getSupportedLocales());

    expect($supported)->toContain('ro')
        ->and($supported)->toContain('en')
        ->and($supported)->not->toContain('es');
});
