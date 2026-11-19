<?php

namespace App\Models;

use App\Traits\Translatable;
use Database\Factories\LegalDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A published legal document — terms, privacy notice, cookie policy, AI notice.
 *
 * Stored rather than hard-coded because the text has to change without a
 * deploy, and because who changed it and when is part of what makes it
 * enforceable. `LogsActivity` keeps the superseded wording of every edit, so
 * the question "what did this say on the day they signed up?" has an answer;
 * `version` and `effective_date` are the public face of that same history.
 *
 * The set of documents is fixed by the routes and footer links that point at
 * them, so there is no create and no delete — a legal page that can vanish is
 * a footer link that can 404.
 */
class LegalDocument extends Model
{
    /** @use HasFactory<LegalDocumentFactory> */
    use HasFactory, LogsActivity, Translatable;

    public const TERMS = 'terms-of-service';

    public const PRIVACY = 'privacy-policy';

    public const COOKIES = 'cookie-policy';

    public const AI = 'ai-transparency-notice';

    /**
     * Every document the application publishes, in the order the footer lists them.
     *
     * @var list<string>
     */
    public const SLUGS = [self::TERMS, self::PRIVACY, self::COOKIES, self::AI];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title_ro',
        'title_en',
        'body_ro',
        'body_en',
        'version',
        'effective_date',
    ];

    /**
     * Read by the Translatable trait, which appends `translated_title` and
     * `translated_body` and falls back to English when a locale is empty.
     *
     * @var list<string>
     */
    protected array $translatable = ['title', 'body'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * The document body as HTML.
     *
     * Rendered server-side rather than in the browser so the admin preview and
     * the public page can never disagree about what the Markdown means. HTML in
     * the source is stripped: the body is edited in a plain textarea by someone
     * pasting out of a lawyer's document, which is not a trustworthy source of
     * markup.
     */
    public function renderedBody(?string $locale = null): string
    {
        $html = $this->renderMarkdown($locale);

        // Anchors, so the contents list beside the document can link into it.
        return (string) preg_replace_callback(
            '/<h2>(.*?)<\/h2>/s',
            fn (array $match): string => sprintf('<h2 id="%s">%s</h2>', $this->headingId($match[1]), $match[1]),
            $html,
        );
    }

    /**
     * The document's top-level sections, in the order they appear.
     *
     * Derived from the rendered HTML rather than the Markdown source so it can
     * never disagree with the headings actually on the page — one parser, one
     * answer.
     *
     * @return array<int, array{id: string, title: string}>
     */
    public function tableOfContents(?string $locale = null): array
    {
        preg_match_all('/<h2>(.*?)<\/h2>/s', $this->renderMarkdown($locale), $matches);

        return array_map(
            fn (string $heading): array => [
                'id' => $this->headingId($heading),
                'title' => $this->headingText($heading),
            ],
            $matches[1],
        );
    }

    /**
     * The document's Markdown, with this site's own details filled in.
     *
     * The text cites its own addresses ("the version in force is the one
     * published at …/terms"), the domain that sets the cookies and the session
     * cookie, which Laravel names after APP_NAME. They are stored as the
     * placeholders [APP_URL], [APP_HOST] and [SESSION_COOKIE] and filled in from
     * this site's configuration whenever the text is shown, so the address is
     * always APP_URL and a copied database never carries another site's.
     */
    public function markdown(?string $locale = null): string
    {
        $body = $locale == null
            ? $this->getTranslated('body')
            : $this->getAttribute("body_{$locale}");

        $url = rtrim((string) config('app.url'), '/');

        return str_replace(
            ['[APP_URL]', '[APP_HOST]', '[SESSION_COOKIE]'],
            [$url, (string) (parse_url($url, PHP_URL_HOST) ?: $url), (string) config('session.cookie')],
            (string) $body,
        );
    }

    private function renderMarkdown(?string $locale): string
    {
        return (string) Str::markdown($this->markdown($locale), [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * A stable anchor for a heading.
     *
     * Slugged from the heading's own text, not its position, so inserting a
     * section does not silently repoint an address somebody has bookmarked or
     * quoted in correspondence. `Str::slug` transliterates the Romanian
     * diacritics, so „Prelucrarea datelor” and its English counterpart both
     * yield an id that survives a URL.
     */
    private function headingId(string $heading): string
    {
        return Str::slug($this->headingText($heading)) ?: 'section';
    }

    private function headingText(string $heading): string
    {
        return trim(html_entity_decode(strip_tags($heading), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // The whole point: an edit is only auditable if the wording it
            // replaced was kept alongside the wording that replaced it.
            ->logOnly(['title_ro', 'title_en', 'body_ro', 'body_en', 'version', 'effective_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
