<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
use App\Services\Media\ImageStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * The community feed: travellers' photos and impressions.
 */
class PostController extends Controller
{
    /** Post photos are stored at most this many pixels on their longest side. */
    private const PHOTO_MAX_SIDE = 1600;

    /**
     * "Recente" is newest first; "Aproape de tine" is nearest first to the
     * coordinates the app sends. Also lists the posts about one place or route.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'feed' => ['nullable', Rule::in(['recent', 'nearby'])],
            'latitude' => ['nullable', 'required_if:feed,nearby', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_if:feed,nearby', 'numeric', 'between:-180,180'],
            'point_of_interest_id' => ['nullable', 'integer'],
            'experience_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'between:1,50'],
        ]);

        $posts = $this->feedQuery($request->user('sanctum'))
            ->when($filters['point_of_interest_id'] ?? null, fn (Builder $query, int $id) => $query->where('point_of_interest_id', $id))
            ->when($filters['experience_id'] ?? null, fn (Builder $query, int $id) => $query->where('experience_id', $id));

        if (($filters['feed'] ?? 'recent') == 'nearby') {
            $this->orderByDistance($posts, (float) $filters['latitude'], (float) $filters['longitude']);
        }

        // Newest first, by when each post was written; as the tie-break among equally near ones too.
        $posts->latest()->latest('id');

        return PostResource::collection($posts->paginate($filters['per_page'] ?? 15)->withQueryString());
    }

    public function store(StorePostRequest $request, ImageStore $images): JsonResponse
    {
        $post = new Post($request->safe()->except('photo'));
        $post->placeOnTheMap();
        $post->user()->associate($request->user());

        if ($request->hasFile('photo')) {
            $post->image_path = $images->storeScaled($request->file('photo'), Post::IMAGE_DIRECTORY, self::PHOTO_MAX_SIDE);
        }

        try {
            $post->save();
        } catch (Throwable $exception) {
            $images->delete($post->image_path);

            throw $exception;
        }

        return PostResource::make($this->feedQuery($request->user())->findOrFail($post->id))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, Post $post): PostResource
    {
        $viewer = $request->user('sanctum');

        // A hidden post is as good as missing, except to its author and moderators.
        abort_unless($post->is_approved || ($viewer?->can('view', $post) ?? false), 404);

        return PostResource::make($this->feedQuery($viewer, visibleOnly: false)->findOrFail($post->id));
    }

    public function destroy(Request $request, Post $post): Response
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->noContent();
    }

    /**
     * @return Builder<Post>
     */
    private function feedQuery(?User $viewer, bool $visibleOnly = true): Builder
    {
        return Post::query()
            ->when($visibleOnly, fn (Builder $query) => $query->visible())
            ->with([
                'user',
                'pointOfInterest',
                'experience.destination',
                'experience.stops:id,experience_id,point_of_interest_id,position',
            ])
            ->withLikedBy($viewer);
    }

    /**
     * Nearest first, by an equirectangular distance: plain arithmetic that
     * every database runs, and exact enough to rank posts within a country.
     *
     * @param  Builder<Post>  $query
     */
    private function orderByDistance(Builder $query, float $latitude, float $longitude): void
    {
        $longitudeScale = cos(deg2rad($latitude));

        $query->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByRaw(
                '((latitude - ?) * (latitude - ?)) + ((longitude - ?) * ? * (longitude - ?) * ?)',
                [$latitude, $latitude, $longitude, $longitudeScale, $longitude, $longitudeScale],
            );
    }
}
