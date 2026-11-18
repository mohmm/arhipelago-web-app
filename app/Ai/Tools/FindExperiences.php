<?php

namespace App\Ai\Tools;

use App\Ai\Facts;
use App\Enums\ExperienceKind;
use App\Models\Experience;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Lets Maya search the published experiences.
 */
class FindExperiences implements Tool
{
    private const LIMIT = 30;

    public function description(): string
    {
        return 'Search the experiences in the app: day routes with stops at points of interest, measured on real roads. '
            .'Kinds: classic (by car), family (by car, for families), car_free (on foot) and winter (by car, in winter). '
            .'Returns each match with its id, title, kind, distance, duration and stops. Call it without arguments to list them all.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'kind' => $schema->string()->enum(array_map(fn (ExperienceKind $kind): string => $kind->value, ExperienceKind::cases()))->description('Only experiences of this kind.'),
            'area' => $schema->string()->description('Only experiences in this area, such as "Bran".'),
            'point_of_interest_id' => $schema->integer()->description('Only experiences that stop at this point of interest.'),
        ];
    }

    public function handle(Request $request): string
    {
        $experiences = Experience::query()
            ->published()
            ->with(['destination', 'stops.pointOfInterest'])
            ->when($request['kind'] ?? null, fn (Builder $query, string $kind) => $query->where('kind', $kind))
            ->when($request['area'] ?? null, fn (Builder $query, string $area) => $query->whereHas('destination', fn (Builder $destinations) => $destinations
                ->where('name_ro', 'like', "%{$area}%")
                ->orWhere('name_en', 'like', "%{$area}%")
                ->orWhere('name_ar', 'like', "%{$area}%")))
            ->when($request['point_of_interest_id'] ?? null, fn (Builder $query, int $id) => $query->whereRelation('stops', 'point_of_interest_id', $id))
            ->limit(self::LIMIT)
            ->get();

        return json_encode([
            'experiences' => $experiences->map(fn (Experience $experience): array => Facts::experience($experience))->all(),
        ], JSON_UNESCAPED_UNICODE);
    }
}
