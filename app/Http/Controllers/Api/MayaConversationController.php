<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMayaConversationRequest;
use App\Http\Resources\MayaConversationResource;
use App\Models\Experience;
use App\Models\MayaConversation;
use App\Models\PointOfInterest;
use App\Services\Maya\MayaCards;
use App\Services\Maya\MayaReplier;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * A traveller's conversations with Maya. Each one is private to its traveller.
 */
class MayaConversationController extends Controller
{
    public function __construct(
        private MayaReplier $maya,
        private MayaCards $cards,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $conversations = $request->user()->mayaConversations()
            ->with(['subject' => fn (MorphTo $subject) => $this->subjectRelations($subject)])
            ->latest('last_message_at')
            ->latest('id')
            ->paginate(20);

        return MayaConversationResource::collection($conversations);
    }

    /**
     * Starts a conversation, optionally about one place or experience, and
     * answers its first message when there is one.
     */
    public function store(StoreMayaConversationRequest $request): JsonResponse
    {
        $conversation = new MayaConversation;

        if ($request->filled('point_of_interest_id')) {
            $conversation->subject()->associate(PointOfInterest::query()->findOrFail($request->validated('point_of_interest_id')));
        } elseif ($request->filled('experience_id')) {
            $conversation->subject()->associate(Experience::query()->findOrFail($request->validated('experience_id')));
        }

        if ($request->filled('message')) {
            $this->maya->reply($conversation, $request->user(), $request->validated('message'));
        } else {
            $conversation->user()->associate($request->user());
            $conversation->save();
        }

        return $this->conversation($conversation)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, MayaConversation $conversation): MayaConversationResource
    {
        $this->ensureOwned($request, $conversation);

        return $this->conversation($conversation);
    }

    public function destroy(Request $request, MayaConversation $conversation): Response
    {
        $this->ensureOwned($request, $conversation);

        $conversation->delete();

        return response()->noContent();
    }

    private function conversation(MayaConversation $conversation): MayaConversationResource
    {
        $conversation->load([
            'subject' => fn (MorphTo $subject) => $this->subjectRelations($subject),
            'messages',
        ]);

        $this->cards->attach($conversation->messages);

        return MayaConversationResource::make($conversation);
    }

    /**
     * What the subject's card needs, whichever kind of subject it is.
     */
    private function subjectRelations(MorphTo $subject): MorphTo
    {
        return $subject
            ->morphWith([
                PointOfInterest::class => ['category', 'media'],
                Experience::class => ['destination', 'stops.pointOfInterest'],
            ])
            ->morphWithCount([
                PointOfInterest::class => ['experiences'],
            ]);
    }

    private function ensureOwned(Request $request, MayaConversation $conversation): void
    {
        // Someone else's conversation is as good as missing.
        abort_unless($conversation->user_id == $request->user()->id, 404);
    }
}
