<?php

namespace Database\Seeders;

use App\Enums\ExperienceKind;
use App\Enums\TravelMode;
use App\Models\Destination;
use App\Models\Experience;
use App\Models\PointOfInterest;
use App\Models\PointOfInterestCategory;
use App\Models\Topic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The app's starting content: real places across Romania, with photos that
 * need no credit (public domain, CC0 or the team's own), and day routes
 * measured on real roads with OSRM.
 *
 * Reads database/seeders/data/content.json and the photos beside it, so it
 * needs no network and no router. It only adds what is missing (places by
 * slug, experiences by destination and kind), so running it again never
 * overwrites what editors changed in the admin panel.
 *
 * Run after the reference data: php artisan db:seed --class=ContentSeeder
 */
class ContentSeeder extends Seeder
{
    private const DATA = __DIR__.'/data/content.json';

    private const PHOTOS = __DIR__.'/data/photos';

    public function run(): void
    {
        $content = json_decode((string) file_get_contents(self::DATA), true, flags: JSON_THROW_ON_ERROR);

        $places = collect($content['points_of_interest'])->mapWithKeys(fn (array $place): array => [$place['slug'] => $this->place($place)]);
        $destinations = collect($content['destinations'])->mapWithKeys(fn (array $destination): array => [$destination['slug'] => $this->destination($destination)]);

        foreach ($content['experiences'] as $position => $experience) {
            $this->experience($experience, $destinations, $places);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function place(array $data): PointOfInterest
    {
        $existing = PointOfInterest::query()->where('slug', $data['slug'])->first();

        if ($existing != null) {
            return $existing;
        }

        $category = PointOfInterestCategory::query()->where('slug', $data['category'])->first()
            ?? throw new RuntimeException("The category [{$data['category']}] is missing. Run the PointOfInterestCategorySeeder first.");

        $place = DB::transaction(function () use ($data, $category): PointOfInterest {
            $place = PointOfInterest::create([
                'category_id' => $category->id,
                'slug' => $data['slug'],
                ...$this->translated('name', $data['name']),
                ...$this->translated('type', $data['type']),
                ...$this->translated('locality', $data['locality']),
                ...$this->translated('address', $data['address']),
                ...$this->translated('description', $data['description']),
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'is_historic_monument' => $data['is_historic_monument'],
                'website' => $data['website'],
                'is_published' => true,
            ]);

            $place->topics()->sync(Topic::query()->whereIn('slug', $data['topics'])->pluck('id'));

            return $place;
        });

        foreach ($data['photos'] as $photo) {
            $place->addMedia(self::PHOTOS.'/'.$photo['file'])
                ->preservingOriginal()
                ->usingName($place->name_ro)
                ->withCustomProperties([
                    'license' => $photo['license'],
                    'author' => $photo['author'],
                    'source_url' => $photo['source_url'],
                ])
                ->toMediaCollection(PointOfInterest::PHOTOS);
        }

        return $place;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function destination(array $data): Destination
    {
        return Destination::query()->firstOrCreate(
            ['slug' => $data['slug']],
            [...$this->translated('name', $data['name']), ...$this->translated('region', $data['region'])],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  Collection<string, Destination>  $destinations
     * @param  Collection<string, PointOfInterest>  $places
     */
    private function experience(array $data, $destinations, $places): void
    {
        $destination = $destinations->get($data['destination']) ?? throw new RuntimeException("Unknown destination [{$data['destination']}].");
        $kind = ExperienceKind::from($data['kind']);

        if ($destination->experiences()->where('kind', $kind)->exists()) {
            return;
        }

        DB::transaction(function () use ($data, $destination, $kind, $places): void {
            $experience = new Experience([
                'kind' => $kind,
                'travel_mode' => TravelMode::from($data['travel_mode']),
                ...$this->translated('description', $data['description']),
                'description_ai_generated' => $data['description_ai_generated'],
                'is_published' => true,
            ]);
            $experience->destination()->associate($destination);
            $experience->forceFill([
                'distance_m' => $data['distance_m'],
                'duration_s' => $data['duration_s'],
                'route_polyline' => $data['route_polyline'],
            ])->save();

            foreach ($data['stops'] as $position => $stop) {
                $experience->stops()->create([
                    'point_of_interest_id' => ($places->get($stop['point_of_interest']) ?? throw new RuntimeException("Unknown stop [{$stop['point_of_interest']}]."))->id,
                    'position' => $position,
                    'distance_from_start_m' => $stop['distance_from_start_m'],
                    'duration_from_start_s' => $stop['duration_from_start_s'],
                ]);
            }
        });
    }

    /**
     * ["ro" => …, "en" => …] as ["name_ro" => …, "name_en" => …].
     *
     * @param  array<string, string>  $values
     * @return array<string, string>
     */
    private function translated(string $attribute, array $values): array
    {
        return collect($values)->mapWithKeys(fn (string $value, string $locale): array => ["{$attribute}_{$locale}" => $value])->all();
    }
}
