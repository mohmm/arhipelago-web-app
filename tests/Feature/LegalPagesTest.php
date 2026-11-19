<?php

use App\Models\LegalDocument;
use Database\Seeders\LegalDocumentSeeder;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\get;

/**
 * The published legal pages. They are linked from the app's sign-in screen and
 * the website's footer, and quoted inside the documents themselves, so a broken
 * one is not a cosmetic problem.
 */
it('publishes every legal document the footer links to', function (string $path, string $slug) {
    $this->seed(LegalDocumentSeeder::class);

    get($path)
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('legal')
            ->where('document.slug', $slug)
        );
})->with([
    ['/terms', LegalDocument::TERMS],
    ['/privacy', LegalDocument::PRIVACY],
    ['/cookies', LegalDocument::COOKIES],
    ['/ai', LegalDocument::AI],
]);

it('offers the other documents from whichever one is open', function () {
    $this->seed(LegalDocumentSeeder::class);

    get('/cookies')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('documents', count(LegalDocument::SLUGS))
            // Reading order, which is the order the slugs are declared in —
            // not alphabetical, which would open with the cookie policy.
            ->where('documents.0.slug', LegalDocument::TERMS)
            ->where('documents.1.slug', LegalDocument::PRIVACY)
            ->where('documents.2.slug', LegalDocument::COOKIES)
            ->where('documents.3.slug', LegalDocument::AI)
        );
});

it('points those links at addresses that actually resolve', function () {
    $this->seed(LegalDocumentSeeder::class);

    // The slug and the URL differ — `terms-of-service` is served at `/terms` —
    // so a hand-written map in the client would rot silently. These come from
    // the named routes; this proves the ones it produced are real.
    $documents = get('/terms')->viewData('page')['props']['documents'];

    expect($documents)->toHaveCount(count(LegalDocument::SLUGS));

    foreach ($documents as $document) {
        get($document['url'])
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('document.slug', $document['slug']));
    }
});

it('lists the sections of the document it is showing', function () {
    LegalDocument::factory()->create([
        'slug' => LegalDocument::TERMS,
        'body_ro' => "## 1. Obiect\n\nText.\n\n## 2. Prelucrarea datelor\n\nText.",
    ]);

    get('/terms')->assertInertia(fn ($page) => $page
        ->has('document.sections', 2)
        ->where('document.sections.0.title', '1. Obiect')
        // Slugged from the heading text, so the Romanian diacritics survive
        // being put in a URL and the anchor stays stable if a section moves.
        ->where('document.sections.1.id', '2-prelucrarea-datelor')
    );
});

it('anchors every section it lists', function () {
    $this->seed(LegalDocumentSeeder::class);

    // A contents entry pointing at an id that is not in the document is a link
    // that silently does nothing, which is worse than having no list at all.
    foreach (LegalDocument::SLUGS as $slug) {
        $document = LegalDocument::where('slug', $slug)->sole();

        foreach (['ro', 'en'] as $locale) {
            $html = $document->renderedBody($locale);
            $sections = $document->tableOfContents($locale);

            expect($sections)->not->toBeEmpty();

            $ids = array_column($sections, 'id');
            expect($ids)->toBe(array_unique($ids), "duplicate anchor in {$slug}.{$locale}");

            foreach ($ids as $id) {
                expect($html)->toContain('id="'.$id.'"');
            }
        }
    }
});

it('publishes the documents at the address this deployment actually answers on', function () {
    // Seeded on one address and shown on another, as when a database is copied
    // from a development computer to the server: the pages follow APP_URL.
    config()->set('app.url', 'http://localhost:8005');
    $this->seed(LegalDocumentSeeder::class);
    config()->set('app.url', 'https://app.smartsense.ro');

    $terms = LegalDocument::where('slug', LegalDocument::TERMS)->sole();

    // The Terms cite their own URL — "the version in force is the one published
    // at …" — so a literal domain sends a customer somewhere this application is
    // not served from the moment it moves.
    expect($terms->markdown('ro'))->toContain('https://app.smartsense.ro/terms')
        ->and($terms->markdown('en'))->toContain('https://app.smartsense.ro/terms')
        ->and($terms->renderedBody('en'))->toContain('https://app.smartsense.ro/terms')->not->toContain('localhost');

    // And the Cookie Policy names the domain that sets the cookies, and the
    // session cookie this site really sets.
    $cookies = LegalDocument::where('slug', LegalDocument::COOKIES)->sole();
    expect($cookies->markdown('en'))->toContain('app.smartsense.ro')
        ->and($cookies->markdown('en'))->not->toContain('https://app.smartsense.ro domain')
        ->and($cookies->markdown('en'))->toContain(config('session.cookie'));
});

it('stores the placeholders, not this site\'s address', function () {
    $this->seed(LegalDocumentSeeder::class);

    $terms = LegalDocument::where('slug', LegalDocument::TERMS)->sole();

    expect($terms->body_en)->toContain('[APP_URL]/terms')
        ->and($terms->body_en)->not->toContain((string) config('app.url'));
});

it('puts the placeholders back into documents seeded on a development computer', function () {
    $this->seed(LegalDocumentSeeder::class);
    $cookies = LegalDocument::where('slug', LegalDocument::COOKIES)->sole();
    $template = $cookies->body_en;
    $cookies->update(['body_en' => str_replace(['[APP_URL]', '[APP_HOST]', '[SESSION_COOKIE]'], ['http://localhost:8005', 'localhost', 'smartsense_session'], $template)]);

    (require database_path('migrations/2026_11_19_120000_restore_the_placeholders_in_the_legal_documents.php'))->up();

    expect($cookies->fresh()->body_en)->toBe($template);
});

it('never publishes a document with an unsubstituted placeholder', function () {
    $this->seed(LegalDocumentSeeder::class);

    // A leaked `[APP_URL]` is not a broken link, it is a visible defect in the
    // middle of a contract.
    foreach (LegalDocument::SLUGS as $slug) {
        $document = LegalDocument::where('slug', $slug)->sole();

        foreach (['ro', 'en'] as $locale) {
            expect($document->renderedBody($locale))->not->toMatch('/\[[A-Z_]{3,}\]/', "{$slug}.{$locale}");
        }
    }
});

it('states the retention the app actually applies to conversations with Maya', function () {
    $this->seed(LegalDocumentSeeder::class);

    $privacy = LegalDocument::where('slug', LegalDocument::PRIVACY)->sole();

    // MayaConversation is pruned nightly after this many days; the policy
    // promises twelve months. Changing one without the other is a false
    // statement to every traveller.
    expect(config('maya.keep_conversations_for_days'))->toBe(365)
        ->and($privacy->body_en)->toContain('at most 12 months after its last message')
        ->and($privacy->body_ro)->toContain('cel mult 12 luni de la ultimul mesaj');
});

it('names the cookies the website actually sets', function () {
    $this->seed(LegalDocumentSeeder::class);

    $cookies = LegalDocument::where('slug', LegalDocument::COOKIES)->sole();

    foreach ([config('session.cookie'), 'XSRF-TOKEN', 'appearance', 'sidebar_state'] as $name) {
        expect($cookies->markdown('en'))->toContain("`{$name}`")
            ->and($cookies->markdown('ro'))->toContain("`{$name}`");
    }
});

it('reads the legal pages to a signed-out visitor', function () {
    $this->seed(LegalDocumentSeeder::class);

    // Somebody deciding whether to sign up has not signed up yet, and a hotel's
    // lawyer never will.
    get('/terms')->assertOk();
});

it('serves the document in the visitor\'s language', function () {
    LegalDocument::factory()->create([
        'slug' => LegalDocument::TERMS,
        'title_ro' => 'Termeni și condiții',
        'title_en' => 'Terms of Service',
        'body_ro' => '## 1. Obiect',
        'body_en' => '## 1. Scope',
    ]);

    app()->setLocale('ro');
    get('/terms')->assertInertia(fn ($page) => $page->where('document.title', 'Termeni și condiții'));

    app()->setLocale('en');
    get('/terms')->assertInertia(fn ($page) => $page->where('document.title', 'Terms of Service'));
});

it('renders the document as HTML rather than shipping markdown to the browser', function () {
    LegalDocument::factory()->create(['slug' => LegalDocument::TERMS, 'body_ro' => "## 1. Obiect\n\nText."]);

    // The heading carries an anchor so the contents list beside the document
    // can link into it; everything else is what CommonMark produced.
    get('/terms')->assertInertia(fn ($page) => $page->where(
        'document.content_html',
        "<h2 id=\"1-obiect\">1. Obiect</h2>\n<p>Text.</p>\n",
    ));
});

it('strips HTML somebody pasted into the source', function () {
    LegalDocument::factory()->create([
        'slug' => LegalDocument::TERMS,
        'body_ro' => "## 1. Obiect\n\n<script>alert(1)</script>Text.",
    ]);

    // The body is edited in a plain textarea by someone pasting out of a
    // lawyer's document, and the result is injected into the page.
    get('/terms')->assertInertia(fn ($page) => expect($page->toArray()['props']['document']['content_html'])
        ->not->toContain('<script'));
});

it('shows which version is in force', function () {
    LegalDocument::factory()->create([
        'slug' => LegalDocument::PRIVACY,
        'version' => '2026-08-01',
        'effective_date' => '2026-08-01',
    ]);

    get('/privacy')->assertInertia(fn ($page) => $page
        ->where('document.version', '2026-08-01')
        ->where('document.effective_date', '2026-08-01')
    );
});

it('404s rather than rendering an empty page when a document is missing', function () {
    // Better a visible failure than a legal page that silently says nothing.
    get('/terms')->assertNotFound();
});

it('ships every document in both languages', function () {
    $this->seed(LegalDocumentSeeder::class);

    foreach (LegalDocument::SLUGS as $slug) {
        $document = LegalDocument::where('slug', $slug)->sole();

        expect($document->renderedBody('ro'))->toContain('<h2 id=')
            ->and($document->renderedBody('en'))->toContain('<h2 id=')
            ->and($document->title_ro)->not->toBe('')
            ->and($document->title_en)->not->toBe('');
    }
});

it('states in every seeded body the version its page header shows', function () {
    $this->seed(LegalDocumentSeeder::class);

    foreach (LegalDocument::SLUGS as $slug) {
        $document = LegalDocument::where('slug', $slug)->sole();

        $version = preg_quote($document->version, '/');

        expect($document->body_en)->toMatch("/\\bversion {$version}\\b/i")
            ->and($document->body_ro)->toMatch("/\\bversiunea {$version}\\b/iu");
    }
});

it('replaces the business service\'s documents with version 2.0 and keeps their wording in the history', function () {
    $old = LegalDocument::factory()->create([
        'slug' => LegalDocument::TERMS,
        'version' => '1.1',
        'body_en' => '## 1. Scope\n\nThe concierge widget for hotels.',
    ]);
    $current = LegalDocument::factory()->create(['slug' => LegalDocument::PRIVACY, 'version' => '2.1', 'body_en' => 'Edited by a lawyer.']);

    (require database_path('migrations/2026_11_03_001000_publish_the_traveller_app_legal_documents.php'))->up();

    expect($old->fresh())
        ->version->toBe(LegalDocumentSeeder::VERSIONS[LegalDocument::TERMS])
        ->body_en->toContain('Smartsense is a free mobile app')
        ->and($current->fresh()->body_en)->toBe('Edited by a lawyer.')
        ->and(Activity::query()->where('subject_id', $old->id)->latest('id')->first()->properties['old']['body_en'])
        ->toBe('## 1. Scope\n\nThe concierge widget for hotels.');
});
