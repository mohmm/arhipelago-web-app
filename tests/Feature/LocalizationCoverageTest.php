<?php

use Symfony\Component\Finder\Finder;

/**
 * Romanian is the primary language of this site, so an English string reaching
 * a Romanian visitor is a defect rather than a cosmetic slip. Nothing in the
 * build catches one: `t()` falls back to its own key, so an untranslated string
 * renders as fluent English and no error is ever raised.
 *
 * These tests read the components and check that the fallback is never needed.
 */

/**
 * The public surface: what a traveller sees without signing in to the admin
 * panel — the website, the legal pages and the screens of the account emails'
 * links (reset password, verify email).
 *
 * Named one by one rather than scanned, so a renamed or deleted file fails
 * loudly instead of quietly shrinking what is checked. The whole application is
 * covered further down; this list is the part that has to keep working.
 *
 * @return list<string>
 */
function publicSurfaceFiles(): array
{
    $paths = [
        resource_path('js/pages/welcome.tsx'),
        resource_path('js/pages/legal.tsx'),
        resource_path('js/layouts/website-layout.tsx'),
        resource_path('js/components/site-header.tsx'),
        resource_path('js/components/site-footer.tsx'),
        // The funding acknowledgements. On every page, and the copy a grant
        // auditor reads as closely as a traveller does.
        resource_path('js/components/structural-funds.tsx'),
        resource_path('js/components/language-switcher.tsx'),
        resource_path('js/components/appearance-dropdown.tsx'),
        resource_path('js/layouts/auth/auth-brand-layout.tsx'),
        resource_path('js/pages/auth/login.tsx'),
        resource_path('js/pages/auth/forgot-password.tsx'),
        resource_path('js/pages/auth/reset-password.tsx'),
        resource_path('js/pages/auth/verify-email.tsx'),
    ];

    foreach ($paths as $path) {
        // A renamed file would otherwise silently shrink the test's reach.
        expect(is_file($path))->toBeTrue("missing from the public surface: {$path}");
    }

    return $paths;
}

/**
 * Every literal handed to `t()` or `tChoice()` in the given files.
 *
 * Only literals: `t(item.title)` is resolved at runtime from data, so it cannot
 * be read out of the source and is covered by whichever page owns that data.
 *
 * @param  list<string>  $paths
 * @return list<string>
 */
function translationKeysIn(array $paths): array
{
    $keys = [];

    foreach ($paths as $path) {
        preg_match_all(
            '/\b(?:t|tChoice)\(\s*([\'"])((?:\\\\.|(?!\1).)*)\1/s',
            (string) file_get_contents($path),
            $matches,
            PREG_SET_ORDER,
        );

        foreach ($matches as $match) {
            $keys[] = str_replace(['\\\'', '\\"'], ["'", '"'], $match[2]);
        }
    }

    return array_values(array_unique($keys));
}

/**
 * Every component the React application renders.
 *
 * @return list<string>
 */
function applicationFiles(): array
{
    $files = Finder::create()
        ->files()
        ->in(resource_path('js'))
        ->name(['*.ts', '*.tsx']);

    return array_values(array_map(
        fn ($file): string => $file->getPathname(),
        iterator_to_array($files),
    ));
}

/**
 * @return array<string, string>
 */
function translationTable(string $locale): array
{
    return json_decode(
        (string) file_get_contents(lang_path("{$locale}.json")),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
}

it('translates every string the public site renders', function () {
    $romanian = translationTable('ro');

    $untranslated = array_values(array_filter(
        translationKeysIn(publicSurfaceFiles()),
        fn (string $key): bool => ! array_key_exists($key, $romanian),
    ));

    expect($untranslated)->toBe([]);
});

it('translates every string the back office renders', function () {
    /*
     * The wider net. The admin panel is read by a Romanian team, so it is
     * held to the same rule as the public pages.
     */
    $romanian = translationTable('ro');
    $untranslated = [];

    foreach (applicationFiles() as $path) {
        foreach (translationKeysIn([$path]) as $key) {
            if (! array_key_exists($key, $romanian)) {
                $untranslated[str_replace(base_path().'/', '', $path)][] = $key;
            }
        }
    }

    expect($untranslated)->toBe([]);
});

it('reads more than a handful of strings off the public site', function () {
    // Guards the regex above: if it stopped matching, every other assertion
    // here would pass against an empty list and prove nothing.
    expect(count(translationKeysIn(publicSurfaceFiles())))->toBeGreaterThan(50);
});

it('keeps the two language files carrying the same keys', function () {
    // They are read side by side to spot what still needs translating, which
    // only works while they describe the same set of strings.
    $english = array_keys(translationTable('en'));
    $romanian = array_keys(translationTable('ro'));

    expect(array_diff($english, $romanian))->toBe([])
        ->and(array_diff($romanian, $english))->toBe([]);
});

it('bundles the translations instead of fetching them after first paint', function () {
    // Lazily, laravel-react-i18n loads the locale and the fallback as two
    // concurrent dynamic imports from a `useEffect`, and re-renders on
    // whichever resolves first. The second resolution sets `loading` to the
    // value it already holds, so React bails out and never renders again: the
    // page keeps whichever language won the race. `en.json` is the smaller
    // file, so it won every time and Romanian pages rendered in English until a
    // navigation happened to force a re-render — and went back to English on
    // refresh. Passing `eager` fills the map during the first render instead,
    // which removes the race rather than shortening it.
    $entry = (string) file_get_contents(resource_path('js/app.tsx'));

    expect($entry)->toMatch('~import\.meta\.glob\(\s*[\'"]/lang/\*\.json[\'"]\s*,\s*\{[^}]*\beager\s*:\s*true\b~');
});

it('uses the placeholder syntax the translator actually understands', function () {
    // `{year}` is not a placeholder to laravel-react-i18n — it is six literal
    // characters, and the footer printed them for exactly that reason. Only
    // `:name` is substituted.
    foreach (['en', 'ro'] as $locale) {
        foreach (translationTable($locale) as $key => $value) {
            expect($value)->not->toMatch('/\{[a-z_]+\}/i', "{$locale}.json: {$key}");
        }
    }
});

it('never lets one placeholder swallow the start of another', function () {
    /*
     * Substitution is a plain string replace with no word boundary, so a
     * placeholder that begins another one eats it and leaves the tail behind as
     * literal text. `:count` beside `:county` printed "50 localități din 50y", and
     * `:count` beside `:counties` printed a number followed by "ies" before that —
     * the same mistake twice, in two different features, each found by reading the
     * rendered page rather than the code.
     *
     * Both files, because a translation is free to introduce the collision on its
     * own: the English may say ":total of :count" where the Romanian says
     * ":count din :total", and only one of those two is broken.
     */
    $collisions = [];

    foreach (['en', 'ro'] as $locale) {
        foreach (translationTable($locale) as $key => $value) {
            preg_match_all('/:([a-z][a-z_]*)/', $key.' '.$value, $matches);

            $names = array_values(array_unique($matches[1]));

            foreach ($names as $shorter) {
                foreach ($names as $longer) {
                    if ($shorter !== $longer && str_starts_with($longer, $shorter)) {
                        $collisions[] = "{$locale}.json: \":{$shorter}\" swallows \":{$longer}\" in \"{$key}\"";
                    }
                }
            }
        }
    }

    expect(array_values(array_unique($collisions)))->toBe([]);
});

it('translates every string the server sends', function () {
    /*
     * Flash messages, validation errors, API errors and e-mails all go through
     * `__()` on the server, and a missing key there falls back to English
     * exactly as silently as one in a component does.
     */
    $romanian = translationTable('ro');
    $files = Finder::create()->files()->in([app_path(), resource_path('views'), base_path('routes')])->name('*.php');
    $untranslated = [];

    foreach ($files as $file) {
        preg_match_all(
            '/(?<![\w>$:])(?:__|trans_choice|trans)\(\s*([\'"])((?:\\\\.|(?!\1).)*)\1/s',
            $file->getContents(),
            $matches,
            PREG_SET_ORDER,
        );

        foreach ($matches as $match) {
            $key = str_replace(['\\\'', '\\"'], ["'", '"'], $match[2]);
            $isGroupKey = (bool) preg_match('/^[a-z_]+\.[a-z_.]+$/', $key);

            if (! $isGroupKey && ! array_key_exists($key, $romanian)) {
                $untranslated[] = str_replace(base_path().'/', '', $file->getPathname()).": {$key}";
            }
        }
    }

    expect($untranslated)->toBe([]);
});

it('leaves no English sentence typed straight into a component', function () {
    /*
     * The coverage tests above only see what reaches `t()`. The sign-in page had
     * never been through it — "Remember me" was simply written between two tags —
     * and so it was the one public page that answered a Romanian visitor in
     * English while every check passed. Two words or more between a closing and
     * an opening tag, with nothing that looks like code, is copy that skipped the
     * translator. The shadcn primitives are left out: their only words are
     * screen-reader labels, translated where they are rendered.
     */
    $pattern = '/>([^<>{}]*)</';
    $looksLikeCode = '/[;=(){}]|=>|\bconst\b|\breturn\b|\?\s|:\s*$/';

    $typed = static function (string $source) use ($pattern, $looksLikeCode): array {
        $source = (string) preg_replace(['~/\*.*?\*/~s', '~^\s*//.*$~m'], '', $source);
        preg_match_all($pattern, $source, $matches);

        return array_values(array_filter(
            array_map('trim', $matches[1]),
            fn (string $text): bool => preg_match('/[A-Za-z]{2,}\s+[A-Za-z]{2,}/', $text) && ! preg_match($looksLikeCode, $text),
        ));
    };

    // Guards the pattern: it must see the kind of line that slipped through.
    expect($typed('<Label htmlFor="remember">Remember me</Label>'))->toBe(['Remember me'])
        ->and($typed("<Label htmlFor=\"remember\">{t('Remember me')}</Label>"))->toBe([]);

    $found = [];

    foreach (applicationFiles() as $path) {
        if (str_contains($path, '/components/ui/') || ! str_ends_with($path, '.tsx')) {
            continue;
        }

        foreach ($typed((string) file_get_contents($path)) as $text) {
            $found[] = str_replace(base_path().'/', '', $path).": {$text}";
        }
    }

    expect($found)->toBe([]);
});
