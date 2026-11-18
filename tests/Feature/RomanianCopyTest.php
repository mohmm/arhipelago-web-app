<?php

use Illuminate\Support\Arr;
use Symfony\Component\Finder\Finder;

/*
 * How the Romanian copy speaks, held still.
 *
 * Three decisions that were each applied by hand across thousands of strings, and
 * that nothing else in the build would notice being undone: the product addresses
 * its reader as "tu", it calls its own directory a catalogue and a page that has
 * been taken over "preluată" rather than "revendicată", and a count agrees with
 * the noun after it — 1 zi, 19 zile, 20 de zile, and 119 zile again.
 */

/**
 * Every Romanian string the application can print: the JSON table and the grouped
 * PHP files beside it, flattened to "file: key" => value.
 *
 * @return array<string, string>
 */
function romanianStrings(): array
{
    $strings = [];

    foreach (copyTable('ro') as $key => $value) {
        $strings["ro.json: {$key}"] = $value;
    }

    foreach (glob(lang_path('ro/*.php')) as $path) {
        foreach (Arr::dot(require $path) as $key => $value) {
            $strings[basename($path).": {$key}"] = (string) $value;
        }
    }

    return $strings;
}

/**
 * One of the two JSON tables, as key => value.
 *
 * @return array<string, string>
 */
function copyTable(string $locale): array
{
    return json_decode((string) file_get_contents(lang_path("{$locale}.json")), true, flags: JSON_THROW_ON_ERROR);
}

/**
 * Source files that hand a literal to a translator, the widget aside: it ships its
 * own language table, because it runs on somebody else's website.
 *
 * @return array<string, string> path => contents
 */
function translatedSources(): array
{
    $files = Finder::create()
        ->files()
        ->in([resource_path('js'), app_path(), resource_path('views')])
        ->name(['*.ts', '*.tsx', '*.php'])
        ->exclude('widget');

    $sources = [];

    foreach ($files as $file) {
        $sources[str_replace(base_path().'/', '', $file->getPathname())] = $file->getContents();
    }

    return $sources;
}

it('speaks to the reader as „tu" everywhere', function () {
    /*
     * The markers that only the formal address uses. Plural verb endings are not
     * among them: „ce stabiliți rămâne între voi" is the visitor and the business
     * together, and „să fie contactați" is a participle.
     */
    $formal = '/dumneavoastr|(?<!\p{L})(dvs|vă|veți|ați|aveți|sunteți|puteți|doriți|vreți|știți|faceți|spuneți|încercați|alegeți|introduceți|accesați|apăsați|selectați|descărcați)(?!\p{L})|(?<!\p{L})v-(a|am|ai|au|ați|aș|ar|o)(?!\p{L})|(?<!\p{L})vi(?=[\s-])/iu';

    /*
     * Somebody else talking, so somebody else's register. An owner asks the
     * salesperson where Smartsense got their details, and the example question is
     * what a guest types to a hotel's assistant.
     */
    $otherVoices = [
        'ro.json: Where did you get my details',
        'ro.json: e.g. What offers do you have for September?',
    ];

    // Guards the pattern itself: a regex that matched nothing would pass forever.
    expect(preg_match($formal, 'Vă rugăm să încercați din nou.'))->toBe(1)
        ->and(preg_match($formal, 'Pagina dumneavoastră este gata.'))->toBe(1)
        ->and(preg_match($formal, 'Încearcă din nou. Ce stabiliți rămâne între voi.'))->toBe(0);

    $formalStrings = [];

    foreach (romanianStrings() as $where => $value) {
        if (! in_array($where, $otherVoices, true) && preg_match($formal, $value)) {
            $formalStrings[] = "{$where} => {$value}";
        }
    }

    expect($formalStrings)->toBe([]);
});

it('never uses the words the product retired', function () {
    // „Revendicare" became „preluare", the visitor's days out became routes, and
    // the directory is the Smartsense catalogue — the ministry keeps its registers.
    $retired = '/revendic|zile de vizit|registrul smartsense/iu';

    $found = [];

    foreach (romanianStrings() as $where => $value) {
        if (preg_match($retired, $value)) {
            $found[] = "{$where} => {$value}";
        }
    }

    expect($found)->toBe([]);
});

it('never calls the voice assistant a telephone line', function () {
    /*
     * The assistant speaks in the browser, on the business's own page. There is
     * no number to ring, so anything said about its voice is a conversation —
     * never a call and never the telephone, which is what the Romanian once said
     * under an English line that read "by voice".
     */
    $english = copyTable('en');
    $telephony = [];

    foreach (copyTable('ro') as $key => $value) {
        if (preg_match('/voice/i', $english[$key] ?? $key) && preg_match('/telefon|(?<!\p{L})apel/iu', $value)) {
            $telephony[] = "{$key} => {$value}";
        }
    }

    expect($telephony)->toBe([]);
});

it('gives every Romanian plural its three forms and leaves the choosing to the language', function () {
    /*
     * Laravel and laravel-react-i18n both apply Romanian's own rule when a string
     * carries no conditions of its own: the second form for 2–19, the third for
     * 20–100, and the second again for 101–119. Explicit ranges like [20,*] defeat
     * that and print „105 de zile", so none are allowed.
     */
    $romanian = copyTable('ro');
    $english = copyTable('en');
    $condition = '/^\s*[\[{][^\]}]*[\]}]/';
    $malformed = [];

    foreach ($romanian as $key => $value) {
        if (! str_contains($key, '|') && ! str_contains($value, '|')) {
            continue;
        }

        $forms = explode('|', $value);
        $englishForms = explode('|', $english[$key] ?? '');
        $conditioned = array_filter([...$forms, ...$englishForms], fn (string $form): bool => (bool) preg_match($condition, $form));

        if (count($forms) != 3 || count($englishForms) != 2 || $conditioned != []) {
            $malformed[] = $key;
        }
    }

    expect($malformed)->toBe([]);
});

it('agrees a Romanian noun with any count', function (int $count, string $expected) {
    app()->setLocale('ro');

    expect(trans_choice(':count user|:count users', $count))->toBe($expected);
})->with([
    [0, '0 utilizatori'],
    [1, '1 utilizator'],
    [2, '2 utilizatori'],
    [19, '19 utilizatori'],
    [20, '20 de utilizatori'],
    [100, '100 de utilizatori'],
    [101, '101 utilizatori'],
    [119, '119 utilizatori'],
    [120, '120 de utilizatori'],
]);

it('never prints a plural string through the plain translator', function () {
    // `t()` and `__()` would print all three forms joined by bars, and `tChoice()`
    // over a string with one form agrees nothing.
    $literal = '([\'"])((?:\\\\.|(?!\1).)*)\1';
    $misused = [];
    $choicesSeen = 0;

    foreach (translatedSources() as $path => $source) {
        preg_match_all('/(?<![\w$>:])(?:t|__)\(\s*'.$literal.'/s', $source, $plain, PREG_SET_ORDER);

        foreach ($plain as $match) {
            if (str_contains($match[2], '|')) {
                $misused[] = "{$path}: {$match[2]}";
            }
        }

        preg_match_all('/\b(?:tChoice|trans_choice)\(\s*'.$literal.'/s', $source, $choices, PREG_SET_ORDER);

        $choicesSeen += count($choices);

        foreach ($choices as $match) {
            if (! str_contains($match[2], '|')) {
                $misused[] = "{$path}: {$match[2]}";
            }
        }
    }

    // The scan has to be finding the calls for its silence to mean anything.
    expect($choicesSeen)->toBeGreaterThan(3)
        ->and($misused)->toBe([]);
});
