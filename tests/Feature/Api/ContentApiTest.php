<?php

use App\Enums\ExperienceKind;
use App\Models\Destination;
use App\Models\Experience;
use App\Models\PointOfInterest;
use App\Models\PointOfInterestCategory;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

// The app always says which language it wants; Romanian unless a test asks otherwise.
beforeEach(fn () => $this->withHeader('Accept-Language', 'ro'));

function castleCategory(): PointOfInterestCategory
{
    return PointOfInterestCategory::factory()->create([
        'slug' => 'castles-and-fortresses',
        'name_ro' => 'Castele și cetăți',
        'name_en' => 'Castles and fortresses',
        'name_ar' => 'قلاع وحصون',
    ]);
}

it('lists only published places, in the language the app asks for', function () {
    $castles = castleCategory();
    PointOfInterest::factory()->for($castles, 'category')->create(['name_ro' => 'Castelul Bran', 'name_en' => 'Bran Castle', 'name_ar' => 'قلعة بران']);
    PointOfInterest::factory()->unpublished()->create(['name_ro' => 'Ciornă']);

    $this->getJson('/api/points-of-interest', ['Accept-Language' => 'en'])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Bran Castle')
        ->assertJsonPath('data.0.category.name', 'Castles and fortresses')
        ->assertJsonPath('meta.total', 1);

    $this->getJson('/api/points-of-interest', ['Accept-Language' => 'ar'])
        ->assertJsonPath('data.0.name', 'قلعة بران');
});

it('falls back to Romanian when a translation is missing', function () {
    PointOfInterest::factory()->create(['name_ro' => 'Biserica Neagră', 'name_ar' => null]);

    $this->getJson('/api/points-of-interest', ['Accept-Language' => 'ar'])
        ->assertJsonPath('data.0.name', 'Biserica Neagră');
});

it('searches by name in any language and filters by category', function () {
    $castles = castleCategory();
    PointOfInterest::factory()->for($castles, 'category')->create(['name_ro' => 'Castelul Bran', 'name_en' => 'Bran Castle']);
    PointOfInterest::factory()->create(['name_ro' => 'Biserica Neagră', 'name_en' => 'Black Church']);

    $this->getJson('/api/points-of-interest?search=Black')->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Biserica Neagră');
    $this->getJson('/api/points-of-interest?category=castles-and-fortresses')->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Castelul Bran');
});

it('shows a place with the published experiences that stop there', function () {
    $bran = PointOfInterest::factory()->create(['name_ro' => 'Castelul Bran']);
    $other = PointOfInterest::factory()->create();
    Experience::factory()->through([$bran, $other])->create();
    Experience::factory()->unpublished()->through([$bran])->create();

    $this->getJson("/api/points-of-interest/{$bran->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'Castelul Bran')
        ->assertJsonPath('data.experiences_count', 1)
        ->assertJsonCount(1, 'data.experiences')
        ->assertJsonPath('data.liked_by_viewer', false)
        ->assertJsonPath('data.reviews.count', 0);
});

it('hides unpublished places and experiences', function () {
    $draft = PointOfInterest::factory()->unpublished()->create();
    $experience = Experience::factory()->unpublished()->create();

    $this->getJson("/api/points-of-interest/{$draft->id}")->assertNotFound();
    $this->getJson("/api/experiences/{$experience->id}")->assertNotFound();
});

it('lists experiences by destination and in the order of their kinds', function () {
    $bran = Destination::factory()->create(['name_ro' => 'Bran', 'sort_order' => 1]);
    Experience::factory()->for($bran)->kind(ExperienceKind::Winter)->create();
    Experience::factory()->for($bran)->kind(ExperienceKind::Classic)->create();
    Experience::factory()->for($bran)->kind(ExperienceKind::CarFree)->create();

    $this->getJson('/api/experiences', ['Accept-Language' => 'ro'])
        ->assertOk()
        ->assertJsonPath('data.*.kind', ['classic', 'car_free', 'winter'])
        ->assertJsonPath('data.0.full_title', 'Traseul clasic · Bran')
        ->assertJsonPath('data.1.travel_mode', 'foot');

    $this->getJson('/api/experiences?kind=car_free')->assertJsonCount(1, 'data');
});

it('shows an experience with its stops in order and the other kinds of the same destination', function () {
    $bran = Destination::factory()->create(['name_ro' => 'Bran']);
    $castle = PointOfInterest::factory()->create(['name_ro' => 'Castelul Bran']);
    $village = PointOfInterest::factory()->create(['name_ro' => 'Muzeul Satului Brănean']);
    $classic = Experience::factory()->for($bran)->through([$castle, $village])->create();
    $family = Experience::factory()->for($bran)->kind(ExperienceKind::Family)->create();

    $this->getJson("/api/experiences/{$classic->id}")
        ->assertOk()
        ->assertJsonPath('data.stops.0.position', 1)
        ->assertJsonPath('data.stops.0.point_of_interest.name', 'Castelul Bran')
        ->assertJsonPath('data.stops.1.distance_from_start_m', 1000)
        ->assertJsonPath('data.siblings.*.id', [$classic->id, $family->id])
        ->assertJsonPath('data.description_ai_generated', true);
});

it('draws every published place and experience on the map in one request', function () {
    $place = PointOfInterest::factory()->create();
    PointOfInterest::factory()->unpublished()->create();
    Experience::factory()->through([$place])->create();

    $this->getJson('/api/map')
        ->assertOk()
        ->assertJsonCount(1, 'data.points_of_interest')
        ->assertJsonCount(1, 'data.experiences')
        ->assertJsonPath('data.points_of_interest.0.experiences_count', 1);
});

it('lists the active categories in their order', function () {
    PointOfInterestCategory::factory()->create(['slug' => 'nature', 'sort_order' => 2]);
    PointOfInterestCategory::factory()->create(['slug' => 'museums', 'sort_order' => 1]);
    PointOfInterestCategory::factory()->create(['slug' => 'old', 'is_active' => false]);

    $this->getJson('/api/categories')->assertJsonPath('data.*.slug', ['museums', 'nature']);
});

it('likes a place and takes the like back', function () {
    Sanctum::actingAs(User::factory()->create());
    $place = PointOfInterest::factory()->create();

    $this->postJson("/api/points-of-interest/{$place->id}/like")->assertJsonPath('data', ['liked' => true, 'likes_count' => 1]);
    $this->getJson("/api/points-of-interest/{$place->id}")->assertJsonPath('data.liked_by_viewer', true);
    $this->postJson("/api/points-of-interest/{$place->id}/like")->assertJsonPath('data', ['liked' => false, 'likes_count' => 0]);
});

it('keeps one review per traveller and updates it', function () {
    Sanctum::actingAs(User::factory()->create());
    $place = PointOfInterest::factory()->create();

    $this->postJson("/api/points-of-interest/{$place->id}/reviews", ['rating' => 4, 'body' => 'Frumos.'])->assertCreated();
    $this->postJson("/api/points-of-interest/{$place->id}/reviews", ['rating' => 5, 'body' => 'Superb.'])->assertOk();

    $this->getJson("/api/points-of-interest/{$place->id}")
        ->assertJsonPath('data.reviews', ['count' => 1, 'average' => 5]);

    $this->postJson("/api/points-of-interest/{$place->id}/reviews", ['rating' => 6])->assertJsonValidationErrors('rating');
});

it('gives the app the legal page addresses instead of hardcoding them', function () {
    $this->getJson('/api/config')
        ->assertOk()
        ->assertJsonPath('data.legal.terms_url', fn (string $url): bool => str_ends_with($url, '/terms'))
        ->assertJsonPath('data.website_url', fn (string $url): bool => str_starts_with($url, 'http'))
        ->assertJsonPath('data.languages', ['ro', 'en', 'ar']);
});
