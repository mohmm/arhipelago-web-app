<?php

namespace Database\Seeders;

use App\Models\LegalDocument;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Installs the starting text of each published legal document.
 *
 * `firstOrCreate`, never `updateOrCreate`: the documents are edited in the back
 * office, and a seeder that ran on deploy would otherwise throw away the
 * wording somebody's lawyer had just approved.
 *
 * The bodies live beside this file as Markdown rather than inside it, because
 * they are long, they are diffed and reviewed as prose, and the language pair
 * has to stay comparable line by line.
 */
class LegalDocumentSeeder extends Seeder
{
    /**
     * The version each body states in its closing paragraph, so the version in
     * the page header and the one in the text agree. 2.x are the traveller
     * app's; 1.x were the business service's.
     *
     * @var array<string, string>
     */
    public const VERSIONS = [
        LegalDocument::TERMS => '2.1',
        LegalDocument::PRIVACY => '2.0',
        LegalDocument::COOKIES => '2.0',
        LegalDocument::AI => '2.0',
    ];

    /** The date the same closing paragraphs give as the start of these versions. */
    public const EFFECTIVE_DATE = '2026-11-03';

    /**
     * @var array<string, array{ro: string, en: string}>
     */
    private const TITLES = [
        LegalDocument::TERMS => ['ro' => 'Termeni și condiții', 'en' => 'Terms of Service'],
        LegalDocument::PRIVACY => ['ro' => 'Politica de confidențialitate', 'en' => 'Privacy Policy'],
        LegalDocument::COOKIES => ['ro' => 'Politica de cookie-uri', 'en' => 'Cookie Policy'],
        LegalDocument::AI => [
            'ro' => 'Notă de transparență privind inteligența artificială',
            'en' => 'AI Transparency Notice',
        ],
    ];

    public function run(): void
    {
        foreach ($this->documents() as $slug => $attributes) {
            LegalDocument::query()->firstOrCreate(['slug' => $slug], $attributes);
        }

        $this->command?->info('Legal documents seeded: '.count(LegalDocument::SLUGS).' checked.');
    }

    /**
     * Publishes the text of every document whose stored version is older than
     * the one written here. Saved through the model, so the activity log keeps
     * the wording each one replaces. A document already at this version or a
     * later one, edited in the admin panel, is left alone.
     */
    public function publishNewerVersions(): void
    {
        foreach ($this->documents() as $slug => $attributes) {
            $document = LegalDocument::query()->where('slug', $slug)->first();

            if ($document != null && version_compare($document->version, $attributes['version'], '<')) {
                $document->update($attributes);
            }
        }
    }

    /**
     * The current text of every document, ready to save.
     *
     * @return array<string, array{title_ro: string, title_en: string, body_ro: string, body_en: string, version: string, effective_date: string}>
     */
    public function documents(): array
    {
        $documents = [];

        foreach (LegalDocument::SLUGS as $slug) {
            $documents[$slug] = [
                'title_ro' => self::TITLES[$slug]['ro'],
                'title_en' => self::TITLES[$slug]['en'],
                'body_ro' => $this->body($slug, 'ro'),
                'body_en' => $this->body($slug, 'en'),
                'version' => self::VERSIONS[$slug],
                'effective_date' => self::EFFECTIVE_DATE,
            ];
        }

        return $documents;
    }

    private function body(string $slug, string $locale): string
    {
        $path = __DIR__."/legal/{$slug}.{$locale}.md";

        if (! is_file($path)) {
            // Loud rather than silent: an empty legal page is worse than a
            // failed deploy, because nobody notices it until it matters.
            throw new RuntimeException("Missing legal document source: {$path}");
        }

        return $this->resolveAddresses(trim((string) file_get_contents($path)));
    }

    /**
     * Point the documents at wherever this deployment actually lives.
     *
     * The text cites its own addresses ("the version in force is the one
     * published at …/terms") and states which domain sets the cookies, so it
     * is written with a placeholder that becomes this deployment's address.
     *
     * Resolved at seed time rather than at render time because the result is
     * contract text: once a document is published it must not silently change
     * wording because an environment variable moved. Editing it afterwards goes
     * through the back office, where the change is versioned and audited.
     */
    private function resolveAddresses(string $body): string
    {
        $url = rtrim((string) config('app.url'), '/');
        $host = (string) (parse_url($url, PHP_URL_HOST) ?: $url);

        return str_replace(['[APP_URL]', '[APP_HOST]'], [$url, $host], $body);
    }
}
