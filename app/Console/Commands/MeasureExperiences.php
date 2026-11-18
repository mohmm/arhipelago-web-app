<?php

namespace App\Console\Commands;

use App\Models\Experience;
use App\Services\Experiences\MeasureExperience;
use Illuminate\Console\Command;
use Throwable;

class MeasureExperiences extends Command
{
    /**
     * @var string
     */
    protected $signature = 'experiences:measure {id?* : Only these experiences; all of them when left out}';

    /**
     * @var string
     */
    protected $description = 'Measure experiences on real roads with OSRM and store their routes, distances and durations';

    public function handle(MeasureExperience $measure): int
    {
        $experiences = Experience::query()
            ->with('destination')
            ->when($this->argument('id') != [], fn ($query) => $query->whereKey($this->argument('id')))
            ->get();

        $failed = 0;

        foreach ($experiences as $experience) {
            try {
                $measure->handle($experience);
                $this->components->info(sprintf('%s: %.1f km, %d min', $experience->fullTitle('ro'), $experience->distance_m / 1000, round($experience->duration_s / 60)));
            } catch (Throwable $exception) {
                $failed++;
                $this->components->error("{$experience->fullTitle('ro')}: {$exception->getMessage()}");
            }
        }

        return $failed == 0 ? self::SUCCESS : self::FAILURE;
    }
}
