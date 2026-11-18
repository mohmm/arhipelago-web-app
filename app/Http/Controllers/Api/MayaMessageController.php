<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMayaMessageRequest;
use App\Http\Resources\MayaMessageResource;
use App\Models\MayaConversation;
use App\Services\Maya\MayaCards;
use App\Services\Maya\MayaReplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Sending Maya a message, and getting her answer back in the same response.
 */
class MayaMessageController extends Controller
{
    public function __construct(
        private MayaReplier $maya,
        private MayaCards $cards,
    ) {}

    public function store(StoreMayaMessageRequest $request, MayaConversation $conversation): JsonResponse
    {
        abort_unless($conversation->user_id == $request->user()->id, 404);

        $exchange = $this->maya->reply($conversation, $request->user(), $request->validated('content'));

        $this->cards->attach($exchange);

        return MayaMessageResource::collection($exchange)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
