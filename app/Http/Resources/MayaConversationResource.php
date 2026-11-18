<?php

namespace App\Http\Resources;

use App\Models\Experience;
use App\Models\MayaConversation;
use App\Models\PointOfInterest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A conversation with Maya: the place or experience it is about, if any, and
 * its messages when they are loaded.
 *
 * @mixin MayaConversation
 */
class MayaConversationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subject' => $this->whenLoaded('subject', fn () => match (true) {
                $this->subject instanceof PointOfInterest => [
                    'type' => 'point_of_interest',
                    'point_of_interest' => PointOfInterestCardResource::make($this->subject),
                ],
                $this->subject instanceof Experience => [
                    'type' => 'experience',
                    'experience' => ExperienceCardResource::make($this->subject),
                ],
                default => null,
            }),
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'messages' => MayaMessageResource::collection($this->whenLoaded('messages')),
        ];
    }
}
