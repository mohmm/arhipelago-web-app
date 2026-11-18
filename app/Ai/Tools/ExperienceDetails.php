<?php

namespace App\Ai\Tools;

use App\Ai\Facts;
use App\Models\Experience;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Lets Maya read everything the app knows about one experience.
 */
class ExperienceDetails implements Tool
{
    public function description(): string
    {
        return 'Everything the app knows about one experience: its description, every stop in order with how far it is '
            .'from the start, the travel mode, and the other kinds of experience in the same area.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The id of the experience.')->required(),
        ];
    }

    public function handle(Request $request): string
    {
        $experience = Experience::query()
            ->published()
            ->with([
                'stops.pointOfInterest',
                'destination.experiences' => fn ($siblings) => $siblings->published()->with('destination'),
            ])
            ->find((int) $request['id']);

        if ($experience == null) {
            return json_encode(['error' => 'No published experience has this id.']);
        }

        return json_encode(Facts::experience($experience, detailed: true), JSON_UNESCAPED_UNICODE);
    }
}
