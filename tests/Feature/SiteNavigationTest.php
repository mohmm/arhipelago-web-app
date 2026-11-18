<?php

use App\Models\User;
use Database\Seeders\LegalDocumentSeeder;

use function Pest\Laravel\get;

/*
 * The website's header and footer, and the promise that nothing in them is a
 * dead end.
 *
 * The hrefs are read out of the components rather than listed here. A list
 * written twice is a list that stops matching, and the point is to catch a link
 * somebody adds without adding a route.
 */

/**
 * Every internal path a navigation component passes through `url()`.
 *
 * @return list<string>
 */
function navigationPaths(string $component): array
{
    $source = (string) file_get_contents(resource_path("js/components/{$component}"));

    preg_match_all("~\burl\('(/[^']*)'\)~", $source, $matches);

    return array_values(array_unique($matches[1]));
}

it('points every header link at a page that answers', function () {
    $paths = navigationPaths('site-header.tsx');

    // Guards the pattern: an empty list would pass and prove nothing.
    expect($paths)->toContain('/')
        ->and($paths)->toContain('/login')
        ->and($paths)->toContain('/admin/dashboard');

    // The header shows the sign-in link to visitors and the admin link to
    // staff, so each is followed as the person who sees it.
    [$staffPaths, $visitorPaths] = collect($paths)->partition(fn (string $path): bool => str_starts_with($path, '/admin'));

    foreach ($visitorPaths as $path) {
        get($path)->assertOk("the header links to {$path}, which does not answer");
    }

    $this->actingAs(staffMember('admin'));

    foreach ($staffPaths as $path) {
        get($path)->assertOk("the header links to {$path}, which does not answer");
    }
});

it('sends a signed-out visitor to the sign-in page instead of the admin panel', function () {
    $this->seed(LegalDocumentSeeder::class);

    get('/admin/dashboard')->assertRedirect();
    get('/login')->assertOk();
});

it('points every footer link at a page that answers', function () {
    /*
     * The legal links are served from `legal_documents`, so without the seeder
     * every one of them is a 404 — which is also true of a deploy that forgets
     * to run it.
     */
    $this->seed(LegalDocumentSeeder::class);

    $paths = navigationPaths('site-footer.tsx');

    expect($paths)->toBe(['/terms', '/privacy', '/cookies', '/ai']);

    foreach ($paths as $path) {
        get($path)->assertOk("the footer links to {$path}, which does not answer");
    }
});

it('keeps the website closed to travellers signing in with their app account', function () {
    // The admin panel is for staff. A traveller who finds the sign-in page is
    // told to use the app rather than let into an empty panel.
    seedRoles();
    $traveller = User::factory()->create(['password' => 'password']);
    $traveller->assignRole('customer');

    $this->post('/login', ['email' => $traveller->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
