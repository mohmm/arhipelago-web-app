<?php

namespace App\Ai\Tools;

use App\Ai\Facts;
use App\Models\PointOfInterest;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Lets Maya read everything the app knows about one point of interest.
 */
class PointOfInterestDetails implements Tool
{
    public function description(): string
    {
        return 'Everything the app knows about one point of interest: description, address, website, coordinates '
            .'and the experiences that stop there. Opening hours and prices are not in the app.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The id of the point of interest.')->required(),
        ];
    }

    public function handle(Request $request): string
    {
        $place = PointOfInterest::query()
            ->published()
            ->with([
                'category',
                'experiences' => fn ($experiences) => $experiences->published()->with('destination'),
            ])
            ->find((int) $request['id']);

        if ($place == null) {
            return json_encode(['error' => 'No published point of interest has this id.']);
        }

        return json_encode(Facts::pointOfInterest($place, detailed: true), JSON_UNESCAPED_UNICODE);
    }
}
