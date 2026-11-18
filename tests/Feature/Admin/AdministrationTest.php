<?php

use App\Models\PointOfInterestCategory;
use App\Models\Topic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertSoftDeleted;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function adminUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

function superAdminUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    return $user;
}

it('forbids non-admins from every admin page', function (string $path) {
    actingAs(User::factory()->create())->get($path)->assertForbidden();
})->with([
    '/admin/users',
    '/admin/lookups',
    '/admin/lookups/categories',
    '/admin/lookups/topics',
]);

it('lets admins open the admin index pages', function (string $path, string $component) {
    actingAs(adminUser())->get($path)->assertOk()->assertInertia(fn ($page) => $page->component($component));
})->with([
    ['/admin/users', 'admin/users/index'],
    ['/admin/lookups', 'admin/lookups/index'],
    ['/admin/lookups/categories', 'admin/lookups/categories/index'],
    ['/admin/lookups/topics', 'admin/lookups/topics/index'],
]);

it('creates a category and auto-generates the slug from the English name', function () {
    actingAs(adminUser())
        ->post('/admin/lookups/categories', ['name_en' => 'Art Museums', 'name_ro' => 'Muzee de artă'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    assertDatabaseHas('point_of_interest_categories', [
        'name_en' => 'Art Museums',
        'name_ro' => 'Muzee de artă',
        'slug' => 'art-museums',
        'is_active' => true,
    ]);
});

it('creates a topic without an Arabic name once name_ar is nullable', function () {
    actingAs(adminUser())
        ->post('/admin/lookups/topics', ['name_en' => 'Street Food', 'name_ro' => 'Mâncare stradală'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $topic = Topic::firstWhere('slug', 'street-food');

    expect($topic)->not->toBeNull()
        ->and($topic->name_ar)->toBeNull()
        ->and($topic->name_en)->toBe('Street Food');
});

it('validates the slug format', function () {
    actingAs(adminUser())
        ->post('/admin/lookups/categories', ['name_en' => 'X', 'name_ro' => 'X', 'slug' => 'Not A Slug!'])
        ->assertSessionHasErrors('slug');
});

it('updates a category', function () {
    $category = PointOfInterestCategory::create(['name_en' => 'Parks', 'name_ro' => 'Parcuri', 'slug' => 'parks']);

    actingAs(adminUser())
        ->put("/admin/lookups/categories/{$category->id}", [
            'name_en' => 'City Parks',
            'name_ro' => 'Parcuri urbane',
            'slug' => 'city-parks',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($category->fresh()->name_en)->toBe('City Parks')
        ->and($category->fresh()->slug)->toBe('city-parks');
});

it('soft deletes a category', function () {
    $category = PointOfInterestCategory::create(['name_en' => 'Temp', 'name_ro' => 'Temp', 'slug' => 'temp']);

    actingAs(adminUser())->delete("/admin/lookups/categories/{$category->id}")->assertRedirect();

    assertSoftDeleted('point_of_interest_categories', ['id' => $category->id]);
});

it('lets admins create users and assigns the role for both guards', function () {
    actingAs(adminUser())
        ->post('/admin/users', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $user = User::firstWhere('email', 'jane@example.com');

    expect($user)->not->toBeNull()
        ->and($user->hasRole('admin'))->toBeTrue()
        ->and($user->roles()->count())->toBe(2);
});

it('prevents admins from deleting users but allows super admins', function () {
    $target = User::factory()->create();

    actingAs(adminUser())->delete("/admin/users/{$target->id}")->assertForbidden();
    assertDatabaseHas('users', ['id' => $target->id]);

    actingAs(superAdminUser())->delete("/admin/users/{$target->id}")->assertRedirect();
    assertDatabaseMissing('users', ['id' => $target->id]);
});

it('prevents a user from deleting themselves', function () {
    $superAdmin = superAdminUser();

    actingAs($superAdmin)->delete("/admin/users/{$superAdmin->id}")->assertForbidden();
    assertDatabaseHas('users', ['id' => $superAdmin->id]);
});

it('prevents an admin from creating a super admin', function () {
    actingAs(adminUser())
        ->post('/admin/users', [
            'first_name' => 'Mallory',
            'last_name' => 'Root',
            'email' => 'mallory@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'super_admin',
        ])
        ->assertSessionHasErrors('role');

    assertDatabaseMissing('users', ['email' => 'mallory@example.com']);
});

it('prevents an admin from promoting themselves to super admin', function () {
    $admin = adminUser();

    actingAs($admin)
        ->put("/admin/users/{$admin->id}", [
            'first_name' => $admin->first_name,
            'last_name' => $admin->last_name,
            'email' => $admin->email,
            'role' => 'super_admin',
        ])
        ->assertSessionHasErrors('role');

    expect($admin->fresh()->isSuperAdmin())->toBeFalse();
});

it('lets a super admin create another super admin', function () {
    actingAs(superAdminUser())
        ->post('/admin/users', [
            'first_name' => 'Ada',
            'last_name' => 'Admin',
            'email' => 'ada@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'super_admin',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(User::firstWhere('email', 'ada@example.com')->isSuperAdmin())->toBeTrue();
});

it('forbids an admin from viewing or editing a super admin', function () {
    $admin = adminUser();
    $superAdmin = superAdminUser();

    actingAs($admin)->get("/admin/users/{$superAdmin->id}")->assertForbidden();
    actingAs($admin)->get("/admin/users/{$superAdmin->id}/edit")->assertForbidden();

    actingAs($admin)
        ->put("/admin/users/{$superAdmin->id}", [
            'first_name' => 'Hacked',
            'last_name' => 'Name',
            'email' => 'taken@example.com',
            'role' => 'admin',
        ])
        ->assertForbidden();
});

it('rejects a duplicate topic name with a validation error instead of a 500', function () {
    Topic::create(['name_en' => 'Food', 'name_ro' => 'Mâncare', 'slug' => 'food']);

    actingAs(adminUser())
        ->post('/admin/lookups/topics', ['name_en' => 'Food', 'name_ro' => 'Băuturi', 'slug' => 'food-2'])
        ->assertSessionHasErrors('name_en');
});

it('rejects reusing a soft-deleted category slug with a validation error', function () {
    $category = PointOfInterestCategory::create(['name_en' => 'Temp', 'name_ro' => 'Temp', 'slug' => 'reuse-slug']);
    $category->delete();

    actingAs(adminUser())
        ->post('/admin/lookups/categories', ['name_en' => 'Temp Two', 'name_ro' => 'Temp Doi', 'slug' => 'reuse-slug'])
        ->assertSessionHasErrors('slug');
});

it('rejects a non-http image url', function () {
    actingAs(adminUser())
        ->post('/admin/lookups/categories', [
            'name_en' => 'Xss',
            'name_ro' => 'Xss',
            'slug' => 'xss-attempt',
            'image_url' => 'javascript:alert(1)',
        ])
        ->assertSessionHasErrors('image_url');

    assertDatabaseMissing('point_of_interest_categories', ['slug' => 'xss-attempt']);
});

it('accepts a valid https image url', function () {
    actingAs(adminUser())
        ->post('/admin/lookups/categories', [
            'name_en' => 'With Image',
            'name_ro' => 'Cu Imagine',
            'slug' => 'with-image',
            'image_url' => 'https://example.com/photo.jpg',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    assertDatabaseHas('point_of_interest_categories', [
        'slug' => 'with-image',
        'image_url' => 'https://example.com/photo.jpg',
    ]);
});

it('resolves translated_name from the active locale', function () {
    $category = PointOfInterestCategory::create(['name_en' => 'Beaches', 'name_ro' => 'Plaje', 'slug' => 'beaches']);

    app()->setLocale('en');
    expect($category->fresh()->translated_name)->toBe('Beaches');

    app()->setLocale('ro');
    expect($category->fresh()->translated_name)->toBe('Plaje');
});
