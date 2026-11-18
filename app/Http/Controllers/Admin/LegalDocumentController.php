<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLegalDocumentRequest;
use App\Models\LegalDocument;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LegalDocumentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', LegalDocument::class);

        $documents = LegalDocument::query()
            ->with('updatedBy:id,first_name,last_name')
            ->orderByRaw($this->slugOrdering())
            ->get()
            ->map(fn (LegalDocument $document): array => $this->summary($document))
            ->all();

        return Inertia::render('admin/legal-documents/index', [
            'documents' => $documents,
        ]);
    }

    public function edit(LegalDocument $legalDocument): Response
    {
        $this->authorize('update', $legalDocument);

        $legalDocument->load('updatedBy:id,first_name,last_name');

        return Inertia::render('admin/legal-documents/edit', [
            'document' => [
                ...$this->summary($legalDocument),
                'body_ro' => $legalDocument->body_ro,
                'body_en' => $legalDocument->body_en,
                // Rendered by the same code the public page uses, so the
                // preview cannot disagree with what a visitor will read.
                'preview_ro' => $legalDocument->renderedBody('ro'),
                'preview_en' => $legalDocument->renderedBody('en'),
            ],
        ]);
    }

    public function update(UpdateLegalDocumentRequest $request, LegalDocument $legalDocument): RedirectResponse
    {
        $this->authorize('update', $legalDocument);

        $legalDocument->fill($request->validated());
        $legalDocument->updated_by_user_id = $request->user()->id;
        $legalDocument->save();

        Toast::success(__('Legal document saved.'));

        return to_route('admin.legal-documents.edit', $legalDocument);
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(LegalDocument $document): array
    {
        return [
            'slug' => $document->slug,
            'title_ro' => $document->title_ro,
            'title_en' => $document->title_en,
            'title' => $document->translated_title,
            'version' => $document->version,
            'effective_date' => $document->effective_date?->toDateString(),
            'updated_at' => $document->updated_at?->toIso8601String(),
            'updated_by' => $document->updatedBy?->full_name,
            'public_url' => self::publicUrl($document->slug),
        ];
    }

    /**
     * Where a visitor reads this document, or null for one with no page yet.
     */
    public static function publicUrl(string $slug): ?string
    {
        $route = match ($slug) {
            LegalDocument::TERMS => 'terms',
            LegalDocument::PRIVACY => 'privacy',
            LegalDocument::COOKIES => 'cookies',
            default => null,
        };

        return $route == null ? null : route($route, [], false);
    }

    /**
     * List the documents in the order the footer does rather than alphabetically.
     */
    private function slugOrdering(): string
    {
        $cases = collect(LegalDocument::SLUGS)
            ->map(fn (string $slug, int $index): string => sprintf("WHEN '%s' THEN %d", $slug, $index))
            ->implode(' ');

        return "CASE slug {$cases} ELSE 99 END";
    }
}
