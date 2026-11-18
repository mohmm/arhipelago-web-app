<?php

use Symfony\Component\Finder\Finder;

/**
 * Every request that changes something has to say which language it is in.
 *
 * Routes live behind a locale prefix. A `GET` that arrives without one is
 * redirected to the prefixed URL before it reaches a controller, so it sorts
 * itself out — but the redirect middleware deliberately ignores `POST`, `PUT`,
 * `PATCH` and `DELETE`, because a 302 would turn them into a `GET` and drop the
 * body. Those arrive with no prefix to read, and Laravel Localization then
 * falls back to the browser's `Accept-Language` header: somebody working in
 * Romanian presses Save and gets the validation errors back in English.
 *
 * Nothing fails when that happens. The request succeeds, the data is right, and
 * only the wording is wrong — which is why it went unnoticed across half the
 * back office, and why it is worth a test rather than a code review.
 */

/**
 * Every file that issues requests on behalf of a page.
 *
 * The widget is excluded: it is a separate bundle that talks to `/api`, which
 * is registered outside the locale group and negotiates language per request.
 * So is generated code — Wayfinder's output is rewritten by an Artisan command
 * and cannot carry a hand-written call.
 *
 * @return list<string>
 */
function requestingFiles(): array
{
    $files = Finder::create()
        ->files()
        ->in(resource_path('js'))
        ->name(['*.ts', '*.tsx'])
        ->exclude(['widget', 'actions', 'routes', 'wayfinder'])
        ->notPath('components/ui');

    return array_values(array_map(
        fn ($file): string => $file->getPathname(),
        iterator_to_array($files),
    ));
}

/**
 * A mutating call whose URL was written out rather than passed through the
 * prefixer — a path literal, a template literal holding one, a `…Url` constant
 * or a Wayfinder `.url`.
 *
 * A first argument that is none of those is not a URL: `set.delete(key)` is a
 * collection, and `post('analyze')` is a local helper that builds the URL
 * itself a line further down.
 */
const UNLOCALIZED_CALL = '/\b(?:router\.)?(?:post|put|patch|delete)\(\s*(?!url\()(?:[\'"]\/|`[^`]*\/|[\w$.()!]*\.url\b|\w*(?:Url|URL)\b)/';

/** The same calls, done properly. Counted so the pattern above cannot rot into matching nothing. */
const LOCALIZED_CALL = '/\b(?:router\.)?(?:post|put|patch|delete)\(\s*url\(/';

it('sends every mutating request under the locale of the page that asked', function () {
    $offenders = [];

    foreach (requestingFiles() as $path) {
        $source = (string) file_get_contents($path);

        if (preg_match_all(UNLOCALIZED_CALL, $source, $matches) > 0) {
            $offenders[str_replace(base_path().'/', '', $path)] = array_map(trim(...), $matches[0]);
        }
    }

    expect($offenders)->toBe([]);
});

it('hands a Wayfinder form the locale as well', function () {
    /*
     * `<Form {...Controller.store.form()}>` carries an action written at build
     * time by an Artisan command, which has no request to read a locale from —
     * so every one of them comes out unprefixed. The fix is to spread the form
     * and then override `action`, which is why a bare spread is the tell.
     */
    $offenders = [];

    foreach (requestingFiles() as $path) {
        $source = (string) file_get_contents($path);

        if (preg_match('/\{\.\.\.[\w.]*\.form\(\)\}/', $source) === 1) {
            $offenders[] = str_replace(base_path().'/', '', $path);
        }
    }

    expect($offenders)->toBe([]);
});

it('reads enough of the application for the two checks above to mean something', function () {
    // Both assertions pass trivially against a pattern that stopped matching,
    // or a file list that stopped resolving.
    $localized = 0;

    foreach (requestingFiles() as $path) {
        $localized += preg_match_all(LOCALIZED_CALL, (string) file_get_contents($path));
    }

    expect(count(requestingFiles()))->toBeGreaterThan(50)
        ->and($localized)->toBeGreaterThan(20);
});
