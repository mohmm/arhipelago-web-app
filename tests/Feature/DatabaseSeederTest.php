<?php

use App\Models\LegalDocument;
use App\Models\PointOfInterestCategory;
use App\Models\Topic;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Eloquent\Model;

/**
 * `db:seed` installs the roles, the categories, the themes and the legal
 * documents on a fresh deployment, which is why it must not also create an
 * administrator whose password is written in the repository.
 */
it('installs the reference data without creating a demo administrator in production', function () {
    app()['env'] = 'production';

    // Run directly: the artisan command asks for confirmation in production.
    Model::unguarded(fn () => (new DatabaseSeeder)->setContainer(app())->run());

    expect(User::where('email', 'test@example.com')->exists())->toBeFalse()
        ->and(User::role('super_admin')->exists())->toBeFalse()
        ->and(PointOfInterestCategory::count())->toBe(5)
        ->and(Topic::count())->toBe(8)
        // The app links to these from its sign-in and sign-up screens.
        ->and(LegalDocument::count())->toBe(count(LegalDocument::SLUGS));
});

it('creates the demo administrator everywhere else', function () {
    $this->seed(DatabaseSeeder::class);

    $user = User::where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasRole('super_admin'))->toBeTrue();
});

it('can run twice without duplicating anything', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(PointOfInterestCategory::count())->toBe(5)
        ->and(Topic::count())->toBe(8)
        ->and(User::where('email', 'test@example.com')->count())->toBe(1);
});
