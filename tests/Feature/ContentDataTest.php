<?php

use App\Enums\PhotoLicense;
use App\Models\PointOfInterestCategory;
use App\Models\Topic;
use Database\Seeders\PointOfInterestCategorySeeder;
use Database\Seeders\TopicSeeder;

/*
 * database/seeders/data/content.json: the places, destinations and routes the
 * app starts with. ContentSeeder reads it on every server, so a mistake in it
 * reaches production the next time somebody seeds.
 */

/**
 * The places as the content file describes them.
 *
 * @return list<array<string, mixed>>
 */
function contentPlaces(): array
{
    return json_decode((string) file_get_contents(database_path('seeders/data/content.json')), true, flags: JSON_THROW_ON_ERROR)['points_of_interest'];
}

it('gives every place a unique slug, all three languages and a position in Romania', function () {
    $places = contentPlaces();
    $slugs = array_column($places, 'slug');

    expect($slugs)->toBe(array_values(array_unique($slugs)));

    foreach ($places as $place) {
        foreach (['name', 'type', 'locality', 'address', 'description'] as $field) {
            foreach (['ro', 'en', 'ar'] as $locale) {
                expect(trim($place[$field][$locale] ?? ''))->not->toBe('', "{$place['slug']}: {$field} in {$locale}");
            }
        }

        // Romania's bounding box: a swapped latitude and longitude lands far outside it.
        expect($place['latitude'])->toBeGreaterThan(43.6)->toBeLessThan(48.3, $place['slug'])
            ->and($place['longitude'])->toBeGreaterThan(20.2)->toBeLessThan(29.8, $place['slug']);
    }
});

it('files every place under a category and themes that exist', function () {
    $this->seed([PointOfInterestCategorySeeder::class, TopicSeeder::class]);

    $categories = PointOfInterestCategory::query()->pluck('slug')->all();
    $topics = Topic::query()->pluck('slug')->all();

    foreach (contentPlaces() as $place) {
        expect($categories)->toContain($place['category'])
            ->and(array_diff($place['topics'], $topics))->toBe([], "{$place['slug']} has an unknown theme");
    }
});

it('shows only photos that need no credit, each one kept beside the content', function () {
    foreach (contentPlaces() as $place) {
        expect($place['photos'])->not->toBeEmpty("{$place['slug']} has no photo");

        foreach ($place['photos'] as $photo) {
            expect(PhotoLicense::tryFrom($photo['license']))->not->toBeNull("{$photo['file']} is under {$photo['license']}")
                ->and(database_path("seeders/data/photos/{$photo['file']}"))->toBeFile();
        }
    }
});
