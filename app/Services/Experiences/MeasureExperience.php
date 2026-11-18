<?php

namespace App\Services\Experiences;

use App\Models\Experience;
use App\Models\ExperienceStop;
use App\Services\Routing\OsrmClient;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Measures an experience on real roads, through its stops in order, and
 * stores the line, the total distance and duration, and how far along the
 * route each stop is ("la 510 m de start · 1 min").
 */
class MeasureExperience
{
    public function __construct(private OsrmClient $router) {}

    /**
     * @throws RuntimeException when the router cannot be reached or cannot route the stops
     */
    public function handle(Experience $experience): void
    {
        $stops = $experience->stops()->with('pointOfInterest')->get();

        if ($stops->count() < 2) {
            DB::transaction(function () use ($experience, $stops): void {
                $stops->each(fn (ExperienceStop $stop) => $stop->update(['distance_from_start_m' => 0, 'duration_from_start_s' => 0]));
                $experience->forceFill(['distance_m' => 0, 'duration_s' => 0, 'route_polyline' => null])->save();
            });

            return;
        }

        $path = $this->router->route(
            $stops->map(fn (ExperienceStop $stop): array => [$stop->pointOfInterest->latitude, $stop->pointOfInterest->longitude])->all(),
            $experience->travel_mode->routingProfile(),
        );

        DB::transaction(function () use ($experience, $stops, $path): void {
            $metres = 0;
            $seconds = 0;

            foreach ($stops->values() as $index => $stop) {
                if ($index > 0) {
                    $metres += $path->legs[$index - 1]['distance_m'];
                    $seconds += $path->legs[$index - 1]['duration_s'];
                }

                $stop->update(['distance_from_start_m' => $metres, 'duration_from_start_s' => $seconds]);
            }

            $experience->forceFill([
                'distance_m' => $path->distanceMetres,
                'duration_s' => $path->durationSeconds,
                'route_polyline' => $path->polyline,
            ])->save();
        });
    }
}
