<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExperienceCardResource;
use App\Http\Resources\PointOfInterestCardResource;
use App\Models\Experience;
use App\Models\PointOfInterest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

/**
 * Everything the map draws in one request: every published place as a photo
 * marker and every published experience as a line with its stops. The app
 * counts what is in view itself, so panning never waits for the network.
 */
class MapController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $places = PointOfInterest::query()
            ->published()
            ->with(['category', 'media'])
            ->withCount(['experiences' => fn (Builder $experiences) => $experiences->where('is_published', true)])
            ->get();

        $experiences = Experience::query()
            ->published()
            ->with(['destination', 'stops.pointOfInterest'])
            ->get();

        return response()->json([
            'data' => [
                'points_of_interest' => PointOfInterestCardResource::collection($places),
                'experiences' => ExperienceCardResource::collection($experiences),
            ],
        ]);
    }
}
