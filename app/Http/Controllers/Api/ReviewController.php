<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Experience;
use App\Models\PointOfInterest;
use App\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Star ratings with a few words, on places and on experiences. One per
 * traveller each: writing again replaces the earlier review.
 */
class ReviewController extends Controller
{
    public function forPointOfInterest(PointOfInterest $pointOfInterest): AnonymousResourceCollection
    {
        abort_unless($pointOfInterest->is_published, 404);

        return $this->list($pointOfInterest);
    }

    public function storeForPointOfInterest(StoreReviewRequest $request, PointOfInterest $pointOfInterest): JsonResponse
    {
        abort_unless($pointOfInterest->is_published, 404);

        return $this->write($request, $pointOfInterest->reviewBy($request->user(), $request->validated('rating'), $request->validated('body')));
    }

    public function forExperience(Experience $experience): AnonymousResourceCollection
    {
        abort_unless($experience->is_published, 404);

        return $this->list($experience);
    }

    public function storeForExperience(StoreReviewRequest $request, Experience $experience): JsonResponse
    {
        abort_unless($experience->is_published, 404);

        return $this->write($request, $experience->reviewBy($request->user(), $request->validated('rating'), $request->validated('body')));
    }

    public function destroy(Request $request, Review $review): Response
    {
        $this->authorize('delete', $review);

        $review->delete();

        return response()->noContent();
    }

    /**
     * @param  PointOfInterest|Experience  $reviewable
     */
    private function list(Model $reviewable): AnonymousResourceCollection
    {
        return ReviewResource::collection($reviewable->reviews()->with('user')->latest()->latest('id')->paginate(20));
    }

    private function write(StoreReviewRequest $request, Review $review): JsonResponse
    {
        return ReviewResource::make($review->load('user'))
            ->response()
            ->setStatusCode($review->wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK);
    }
}
