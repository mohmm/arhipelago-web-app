<?php

namespace App\Ai\Tools;

use App\Enums\TravelMode;
use App\Models\PointOfInterest;
use App\Services\Routing\OsrmClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Throwable;

/**
 * Lets Maya say how far apart two places are, measured on real roads or
 * paths. When the router cannot be reached she gets the straight-line
 * distance, marked as such and with no travel time, rather than a guess.
 */
class TravelBetween implements Tool
{
    public function __construct(private OsrmClient $router) {}

    public function description(): string
    {
        return 'The distance and travel time between two points of interest, on foot or by car, measured on real roads and paths.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'from_point_of_interest_id' => $schema->integer()->description('Where the traveller starts.')->required(),
            'to_point_of_interest_id' => $schema->integer()->description('Where the traveller goes.')->required(),
            'mode' => $schema->string()->enum([TravelMode::Foot->value, TravelMode::Car->value])->description('On foot or by car.')->required(),
        ];
    }

    public function handle(Request $request): string
    {
        $from = PointOfInterest::query()->published()->find((int) $request['from_point_of_interest_id']);
        $to = PointOfInterest::query()->published()->find((int) $request['to_point_of_interest_id']);

        if ($from == null || $to == null) {
            return json_encode(['error' => 'Both ids must be published points of interest.']);
        }

        $mode = TravelMode::tryFrom((string) $request['mode']) ?? TravelMode::Foot;
        $between = ['from' => $from->translated_name, 'to' => $to->translated_name, 'mode' => $mode->value];

        try {
            $path = $this->router->route(
                [[$from->latitude, $from->longitude], [$to->latitude, $to->longitude]],
                $mode->routingProfile(),
            );

            return json_encode([
                ...$between,
                'distance_m' => $path->distanceMetres,
                'duration_minutes' => max(1, (int) round($path->durationSeconds / 60)),
                'measured' => 'along real roads and paths',
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $exception) {
            report($exception);

            return json_encode([
                ...$between,
                'straight_line_distance_m' => $this->straightLineMetres($from, $to),
                'note' => 'The route could not be measured right now. This is the distance in a straight line, not along roads, and there is no travel time: say exactly that.',
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    private function straightLineMetres(PointOfInterest $from, PointOfInterest $to): int
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($to->latitude - $from->latitude);
        $dLng = deg2rad($to->longitude - $from->longitude);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($from->latitude)) * cos(deg2rad($to->latitude)) * sin($dLng / 2) ** 2;

        return (int) round($earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
