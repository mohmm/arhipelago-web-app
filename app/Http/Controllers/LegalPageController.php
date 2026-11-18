<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The published legal documents, one route each.
 *
 * A route per document rather than a single `/legal/{slug}` because these URLs
 * are quoted inside the documents and linked from the app's sign-in screen —
 * they are addresses that have to stay put, not a lookup.
 */
class LegalPageController extends Controller
{
    /**
     * Which named route publishes each document.
     *
     * The slug and the URL deliberately differ (`terms-of-service` lives at
     * `/terms`), so the mapping lives here, next to the routes it names.
     *
     * @var array<string, string>
     */
    private const ROUTES = [
        LegalDocument::TERMS => 'terms',
        LegalDocument::PRIVACY => 'privacy',
        LegalDocument::COOKIES => 'cookies',
        LegalDocument::AI => 'ai',
    ];

    public function terms(): Response
    {
        return $this->show(LegalDocument::TERMS);
    }

    public function privacy(): Response
    {
        return $this->show(LegalDocument::PRIVACY);
    }

    public function cookies(): Response
    {
        return $this->show(LegalDocument::COOKIES);
    }

    public function ai(): Response
    {
        return $this->show(LegalDocument::AI);
    }

    private function show(string $slug): Response
    {
        $document = LegalDocument::query()->where('slug', $slug)->firstOrFail();

        return Inertia::render('legal', [
            'document' => [
                'slug' => $document->slug,
                'title' => $document->translated_title,
                // Rendered server-side: the browser never sees the Markdown, so
                // there is no second renderer that could disagree with the one
                // the admin previews with.
                'content_html' => $document->renderedBody(),
                'sections' => $document->tableOfContents(),
                'version' => $document->version,
                'effective_date' => $document->effective_date?->toDateString(),
                'updated_at' => $document->updated_at?->toIso8601String(),
            ],
            'documents' => $this->published(),
        ]);
    }

    /**
     * Every published document, for the switcher at the top of the page.
     *
     * Somebody reading the Terms is usually about to read the Privacy Policy
     * too, so each page links to the others.
     *
     * @return array<int, array{slug: string, title: string, url: string}>
     */
    private function published(): array
    {
        return LegalDocument::query()
            ->whereIn('slug', LegalDocument::SLUGS)
            ->get()
            // Ordered the way the documents are meant to be read, which is the
            // order they are declared in, not alphabetically by slug.
            ->sortBy(fn (LegalDocument $document): int => (int) array_search($document->slug, LegalDocument::SLUGS, true))
            ->map(fn (LegalDocument $document): array => [
                'slug' => $document->slug,
                'title' => $document->translated_title,
                'url' => route(self::ROUTES[$document->slug]),
            ])
            ->values()
            ->all();
    }
}
