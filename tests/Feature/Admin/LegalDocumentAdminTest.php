<?php

use App\Models\LegalDocument;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function legalStaff(string $role = 'admin'): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function legalPayload(array $overrides = []): array
{
    return array_merge([
        'title_ro' => 'Termeni și condiții',
        'title_en' => 'Terms of Service',
        'body_ro' => "## 1. Obiect\n\nPrezentul document reglementează serviciul.",
        'body_en' => "## 1. Scope\n\nThis document governs the service.",
        'version' => '2026-08-01',
        'effective_date' => '2026-08-01',
    ], $overrides);
}

it('lists every legal document in the order the footer links them', function () {
    LegalDocument::factory()->create(['slug' => LegalDocument::AI]);
    LegalDocument::factory()->create(['slug' => LegalDocument::TERMS]);

    actingAs(legalStaff())
        ->get('/admin/legal-documents')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/legal-documents/index')
            ->has('documents', 2)
            // Terms was created second but is listed first, because that is the
            // order a reader meets them in.
            ->where('documents.0.slug', LegalDocument::TERMS)
            ->where('documents.1.slug', LegalDocument::AI)
        );
});

it('tells the admin where each document is published', function () {
    LegalDocument::factory()->create(['slug' => LegalDocument::PRIVACY]);

    actingAs(legalStaff())
        ->get('/admin/legal-documents')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('documents.0.public_url', '/privacy'));
});

it('shows the rendered document beside the markdown being edited', function () {
    $document = LegalDocument::factory()->create([
        'slug' => LegalDocument::TERMS,
        'body_ro' => '## 1. Obiect',
    ]);

    actingAs(legalStaff())
        ->get("/admin/legal-documents/{$document->slug}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/legal-documents/edit')
            ->where('document.body_ro', '## 1. Obiect')
            // Rendered by the same code the public page uses, so an admin can
            // never approve a preview a visitor will not see.
            ->where('document.preview_ro', "<h2 id=\"1-obiect\">1. Obiect</h2>\n")
        );
});

it('publishes an edit immediately', function () {
    $document = LegalDocument::factory()->create(['slug' => LegalDocument::TERMS]);

    actingAs(legalStaff())
        ->put("/admin/legal-documents/{$document->slug}", legalPayload(['version' => '2.0']))
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    assertDatabaseHas('legal_documents', ['slug' => LegalDocument::TERMS, 'version' => '2.0']);
});

it('records who changed a legal document', function () {
    $document = LegalDocument::factory()->create(['slug' => LegalDocument::TERMS]);
    $editor = legalStaff();

    actingAs($editor)->put("/admin/legal-documents/{$document->slug}", legalPayload());

    // Who signed off on the current wording is part of what makes it
    // enforceable, and it is the first thing anyone asks after a dispute.
    expect($document->fresh()->updated_by_user_id)->toBe($editor->id);
});

it('keeps the wording an edit replaced', function () {
    $document = LegalDocument::factory()->create([
        'slug' => LegalDocument::TERMS,
        'body_ro' => '## 1. Vechiul text',
    ]);

    actingAs(legalStaff())->put("/admin/legal-documents/{$document->slug}", legalPayload());

    $activity = Activity::query()->where('subject_type', (new LegalDocument)->getMorphClass())->latest('id')->first();

    // Without the superseded text, "what did this say when they signed up?"
    // has no answer — which is the whole reason the documents are versioned.
    expect($activity)->not->toBeNull()
        ->and($activity->properties['old']['body_ro'])->toBe('## 1. Vechiul text')
        ->and($activity->properties['attributes']['body_ro'])->toContain('Prezentul document');
});

it('refuses to publish a document with an empty language', function () {
    $document = LegalDocument::factory()->create([
        'slug' => LegalDocument::TERMS,
        'body_ro' => '## 1. Textul curent',
    ]);

    actingAs(legalStaff())
        ->put("/admin/legal-documents/{$document->slug}", legalPayload(['body_ro' => '']))
        ->assertSessionHasErrors('body_ro');

    // A half-saved document would publish a blank page in the primary locale.
    expect($document->fresh()->body_ro)->toBe('## 1. Textul curent');
});

it('leaves the slug alone even when a form tries to change it', function () {
    $document = LegalDocument::factory()->create(['slug' => LegalDocument::TERMS]);

    actingAs(legalStaff())
        ->put("/admin/legal-documents/{$document->slug}", legalPayload(['slug' => 'something-else']));

    // The slug is what the public route and the footer link point at. Letting
    // it move would 404 a page quoted inside the terms themselves.
    expect($document->fresh()->slug)->toBe(LegalDocument::TERMS);
});

it('lets both platform roles manage the documents', function (string $role) {
    LegalDocument::factory()->create(['slug' => LegalDocument::TERMS]);

    actingAs(legalStaff($role))->get('/admin/legal-documents')->assertOk();
})->with(['super_admin', 'admin']);

it('keeps a customer out of the legal documents', function () {
    $document = LegalDocument::factory()->create(['slug' => LegalDocument::TERMS]);
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    // A customer rewriting the terms they are bound by is the contract undone.
    actingAs($customer)->get('/admin/legal-documents')->assertForbidden();
    actingAs($customer)->put("/admin/legal-documents/{$document->slug}", legalPayload())->assertForbidden();

    expect($document->fresh()->version)->toBe($document->version);
});

it('redirects guests away from the legal document admin', function () {
    $document = LegalDocument::factory()->create(['slug' => LegalDocument::TERMS]);

    $this->get('/admin/legal-documents')->assertRedirect();
    $this->put("/admin/legal-documents/{$document->slug}", legalPayload())->assertRedirect();

    assertDatabaseHas('legal_documents', ['slug' => LegalDocument::TERMS, 'version' => $document->version]);
});

it('offers no way to create or delete a legal document', function () {
    $document = LegalDocument::factory()->create(['slug' => LegalDocument::TERMS]);

    // The set is fixed by the public routes and footer links. A legal page that
    // can be deleted is a footer link that can 404. The routes are simply not
    // registered, so the verbs come back as unsupported rather than forbidden.
    actingAs(legalStaff())->post('/admin/legal-documents', legalPayload())->assertStatus(405);
    actingAs(legalStaff())->delete("/admin/legal-documents/{$document->slug}")->assertStatus(405);

    expect(LegalDocument::count())->toBe(1);
});
