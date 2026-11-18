<?php

use App\Enums\ExperienceKind;
use App\Models\Destination;
use App\Models\Experience;
use App\Models\PointOfInterest;
use App\Models\PointOfInterestCategory;
use App\Services\Routing\OsrmClient;
use App\Services\Routing\RoutedPath;
use App\Support\Toast;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');
    $this->staff = staffMember('admin');
});

/**
 * @return array<string, mixed>
 */
function placePayload(array $overrides = []): array
{
    return array_merge([
        'category_id' => PointOfInterestCategory::factory()->create()->id,
        'name_ro' => 'Castelul Bran',
        'name_en' => 'Bran Castle',
        'type_ro' => 'Castel',
        'locality_ro' => 'Bran',
        'description_ro' => 'Castel medieval din secolul al XIV-lea.',
        'latitude' => 45.515,
        'longitude' => 25.3671,
        'is_published' => false,
    ], $overrides);
}

it('lists points of interest with their status', function () {
    PointOfInterest::factory()->create(['name_ro' => 'Castelul Bran']);

    actingAs($this->staff)->get('/admin/points-of-interest')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/points-of-interest/index')->has('places.data', 1));
});

it('creates a point of interest as a draft, with a slug made from its name', function () {
    actingAs($this->staff)->post('/admin/points-of-interest', placePayload())
        ->assertRedirect();

    $place = PointOfInterest::firstOrFail();

    expect($place->slug)->toBe('castelul-bran')
        ->and($place->is_published)->toBeFalse();
});

it('will not publish a point of interest without a photo', function () {
    actingAs($this->staff)->post('/admin/points-of-interest', placePayload(['is_published' => true]))
        ->assertSessionHasErrors('is_published');

    expect(PointOfInterest::count())->toBe(0);
});

it('publishes a point of interest once it has a photo', function () {
    $place = PointOfInterest::factory()->unpublished()->create();

    actingAs($this->staff)->post("/admin/points-of-interest/{$place->id}/photos", [
        'photo' => UploadedFile::fake()->image('bran.jpg', 1600, 1200),
        'license' => 'public-domain',
        'source_url' => 'https://commons.wikimedia.org/wiki/File:Bran.jpg',
    ])->assertRedirect();

    expect($place->fresh()->getFirstMedia(PointOfInterest::PHOTOS)->getCustomProperty('license'))->toBe('public-domain');

    actingAs($this->staff)->put("/admin/points-of-interest/{$place->id}", placePayload(['category_id' => $place->category_id, 'is_published' => true]))
        ->assertSessionHasNoErrors();

    expect($place->fresh()->is_published)->toBeTrue();
});

it('accepts only photos the app can show without a credit', function (?string $license) {
    $place = PointOfInterest::factory()->create();

    // The app prints no author under a photo, so a licence that asks for one cannot be used.
    actingAs($this->staff)->post("/admin/points-of-interest/{$place->id}/photos", ['photo' => UploadedFile::fake()->image('a.jpg'), 'license' => $license])
        ->assertSessionHasErrors('license');

    expect($place->fresh()->getMedia(PointOfInterest::PHOTOS))->toHaveCount(0);
})->with([null, 'CC BY-SA 4.0', 'CC BY 2.0']);

it('takes the team\'s own photos without asking for an author', function () {
    $place = PointOfInterest::factory()->create();

    actingAs($this->staff)->post("/admin/points-of-interest/{$place->id}/photos", ['photo' => UploadedFile::fake()->image('a.jpg'), 'license' => 'own'])
        ->assertSessionHasNoErrors();

    expect($place->fresh()->getMedia(PointOfInterest::PHOTOS))->toHaveCount(1);
});

it('keeps the last photo of a published place', function () {
    $place = PointOfInterest::factory()->create();
    $photo = $place->addMedia(UploadedFile::fake()->image('a.jpg'))->withCustomProperties(['license' => 'cc0'])->toMediaCollection(PointOfInterest::PHOTOS);

    actingAs($this->staff)->delete("/admin/points-of-interest/{$place->id}/photos/{$photo->id}")
        ->assertSessionHas(Toast::SESSION_KEY.'.type', Toast::ERROR);

    expect($place->fresh()->getMedia(PointOfInterest::PHOTOS))->toHaveCount(1);
});

it('refuses to delete a place an experience still stops at', function () {
    $place = PointOfInterest::factory()->create();
    Experience::factory()->through([$place, PointOfInterest::factory()->create()])->create();

    actingAs($this->staff)->delete("/admin/points-of-interest/{$place->id}")->assertSessionHas(Toast::SESSION_KEY.'.type', Toast::ERROR);

    expect(PointOfInterest::find($place->id))->not->toBeNull();
});

it('creates an experience and measures it on real roads', function () {
    [$first, $second] = PointOfInterest::factory()->count(2)->create();
    $this->mock(OsrmClient::class)->shouldReceive('route')->once()->andReturn(
        new RoutedPath(12500, 1500, 'encoded', [['distance_m' => 12500, 'duration_s' => 1500, 'polyline' => '']], []),
    );

    actingAs($this->staff)->post('/admin/experiences', [
        'destination_id' => Destination::factory()->create()->id,
        'kind' => 'classic',
        'travel_mode' => 'car',
        'description_ro' => 'O zi pe traseul clasic.',
        'stops' => [$first->id, $second->id],
        'is_published' => true,
    ])->assertSessionHas(Toast::SESSION_KEY.'.type', Toast::SUCCESS);

    $experience = Experience::with('stops')->firstOrFail();

    expect($experience->distance_m)->toBe(12500)
        ->and($experience->route_polyline)->toBe('encoded')
        ->and($experience->stops->pluck('distance_from_start_m')->all())->toBe([0, 12500])
        ->and($experience->is_published)->toBeTrue();
});

it('keeps an experience offline when its route cannot be measured', function () {
    $experience = Experience::factory()->through(PointOfInterest::factory()->count(2)->create())->create(['is_published' => true]);
    $newStops = PointOfInterest::factory()->count(2)->create()->pluck('id')->all();
    $this->mock(OsrmClient::class)->shouldReceive('route')->andThrow(new RuntimeException('Connection refused'));

    actingAs($this->staff)->put("/admin/experiences/{$experience->id}", [
        'destination_id' => $experience->destination_id,
        'kind' => 'classic',
        'travel_mode' => 'car',
        'description_ro' => 'O zi.',
        'stops' => $newStops,
        'is_published' => true,
    ])->assertSessionHas(Toast::SESSION_KEY.'.type', Toast::ERROR);

    expect($experience->fresh())
        ->is_published->toBeFalse()
        ->distance_m->toBe(0);
});

it('allows one experience of each kind per destination', function () {
    $existing = Experience::factory()->create(['kind' => ExperienceKind::Family, 'travel_mode' => 'car']);
    $stops = PointOfInterest::factory()->count(2)->create()->pluck('id')->all();

    actingAs($this->staff)->post('/admin/experiences', [
        'destination_id' => $existing->destination_id,
        'kind' => 'family',
        'travel_mode' => 'car',
        'description_ro' => 'Din nou.',
        'stops' => $stops,
    ])->assertSessionHasErrors('kind');
});

it('refuses an experience with fewer than two stops or an unpublished stop', function () {
    $draft = PointOfInterest::factory()->unpublished()->create();

    actingAs($this->staff)->post('/admin/experiences', [
        'destination_id' => Destination::factory()->create()->id,
        'kind' => 'classic',
        'travel_mode' => 'car',
        'description_ro' => 'O zi.',
        'stops' => [$draft->id],
    ])->assertSessionHasErrors(['stops', 'stops.0']);
});

it('refuses to delete a destination that still has experiences', function () {
    $experience = Experience::factory()->create();

    actingAs($this->staff)->delete("/admin/destinations/{$experience->destination_id}")->assertSessionHas(Toast::SESSION_KEY.'.type', Toast::ERROR);

    expect(Destination::find($experience->destination_id))->not->toBeNull();
});

it('keeps the content screens closed to travellers', function () {
    $traveller = staffMember('customer');

    actingAs($traveller)->get('/admin/points-of-interest')->assertForbidden();
    actingAs($traveller)->get('/admin/experiences')->assertForbidden();
    actingAs($traveller)->get('/admin/dashboard')->assertForbidden();
});
