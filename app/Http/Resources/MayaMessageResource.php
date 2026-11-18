<?php

namespace App\Http\Resources;

use App\Models\MayaMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One message with Maya. Her replies carry the cards she referred to, resolved
 * to current data by MayaCards, and the follow-up questions she suggested.
 *
 * @mixin MayaMessage
 */
class MayaMessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            // The AI Act's machine-readable mark on generated text (Article 50(2)).
            'ai_generated' => $this->role == MayaMessage::ROLE_ASSISTANT,
            'content' => $this->content,
            'created_at' => $this->created_at?->toIso8601String(),
            'points_of_interest' => PointOfInterestCardResource::collection($this->whenLoaded('cardPointsOfInterest', default: [])),
            'experiences' => ExperienceCardResource::collection($this->whenLoaded('cardExperiences', default: [])),
            'suggestions' => $this->suggestions ?? [],
        ];
    }
}
