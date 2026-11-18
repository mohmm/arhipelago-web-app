<?php

namespace App\Ai\Tools;

use App\Ai\Facts;
use App\Models\PointOfInterest;
use App\Models\PointOfInterestCategory;
use App\Models\Topic;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Lets Maya search the published points of interest.
 */
class FindPointsOfInterest implements Tool
{
    private const LIMIT = 30;

    public function description(): string
    {
        return 'Search the points of interest in the app: castles, fortresses, museums, churches and places in nature. '
            .'Returns each match with its id, name, type, category, locality and a short summary. '
            .'Call it without arguments to list them all.';
    }

    public function schema(JsonSchema $schema): array
    {
        $category = $schema->string()->description('Only places in this category.');
        $categories = PointOfInterestCategory::query()->active()->pluck('slug')->all();

        $topic = $schema->string()->description('Only places tagged with this theme.');
        $topics = Topic::query()->active()->pluck('slug')->all();

        return [
            'text' => $schema->string()->description('Words to look for in the name or the locality, such as "Bran".'),
            'category' => $categories == [] ? $category : $category->enum($categories),
            'topic' => $topics == [] ? $topic : $topic->enum($topics),
        ];
    }

    public function handle(Request $request): string
    {
        $places = PointOfInterest::query()
            ->published()
            ->with('category')
            ->when($request['text'] ?? null, fn (Builder $query, string $text) => $query->search($text))
            ->when($request['category'] ?? null, fn (Builder $query, string $slug) => $query->whereRelation('category', 'slug', $slug))
            ->when($request['topic'] ?? null, fn (Builder $query, string $slug) => $query->whereRelation('topics', 'slug', $slug))
            ->orderBy('name_ro')
            ->limit(self::LIMIT)
            ->get();

        return json_encode([
            'points_of_interest' => $places->map(fn (PointOfInterest $place): array => Facts::pointOfInterest($place))->all(),
        ], JSON_UNESCAPED_UNICODE);
    }
}
