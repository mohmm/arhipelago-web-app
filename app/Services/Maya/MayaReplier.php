<?php

namespace App\Services\Maya;

use App\Ai\Agents\Maya;
use App\Ai\Tools\ExperienceDetails;
use App\Ai\Tools\FindExperiences;
use App\Ai\Tools\FindPointsOfInterest;
use App\Ai\Tools\PointOfInterestDetails;
use App\Ai\Tools\TravelBetween;
use App\Models\Experience;
use App\Models\MayaConversation;
use App\Models\MayaMessage;
use App\Models\PointOfInterest;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\UserMessage;
use Throwable;

/**
 * Asks Maya to answer one message and stores the exchange.
 *
 * Nothing is written until she has answered: if the model fails, neither the
 * traveller's message nor a broken reply is kept, so resending is safe. Her
 * cards are kept only for ids that really are published places and
 * experiences, so a mistaken id can never become a card.
 */
class MayaReplier
{
    /** At most this many cards of each kind under one reply. */
    private const MAX_PLACES = 3;

    private const MAX_EXPERIENCES = 2;

    private const MAX_SUGGESTIONS = 3;

    public function __construct(private MayaInstructions $instructions) {}

    /**
     * @return array{0: MayaMessage, 1: MayaMessage} the traveller's message and Maya's reply
     *
     * @throws MayaUnavailable
     */
    public function reply(MayaConversation $conversation, User $traveller, string $content): array
    {
        $agent = new Maya(
            $this->instructions->for($conversation, $traveller, app()->getLocale()),
            $this->history($conversation),
            [
                app(FindPointsOfInterest::class),
                app(PointOfInterestDetails::class),
                app(FindExperiences::class),
                app(ExperienceDetails::class),
                app(TravelBetween::class),
            ],
        );

        try {
            $response = $agent->prompt(
                $content,
                provider: config('maya.provider'),
                model: config('maya.model'),
                timeout: config('maya.timeout'),
            );
        } catch (Throwable $exception) {
            report($exception);

            throw new MayaUnavailable(previous: $exception);
        }

        $reply = trim((string) ($response['reply'] ?? ''));

        if ($reply == '') {
            throw new MayaUnavailable('Maya returned an empty reply.');
        }

        return DB::transaction(function () use ($conversation, $traveller, $content, $reply, $response): array {
            if (! $conversation->exists) {
                $conversation->user()->associate($traveller);
            }

            $conversation->forceFill([
                'title' => $conversation->title ?? Str::limit($content, 80),
                'last_message_at' => now(),
            ])->save();

            $question = $conversation->messages()->create([
                'role' => MayaMessage::ROLE_USER,
                'content' => $content,
            ]);

            $answer = $conversation->messages()->create([
                'role' => MayaMessage::ROLE_ASSISTANT,
                'content' => $reply,
                'attachments' => $this->attachments($response['point_of_interest_ids'] ?? [], $response['experience_ids'] ?? []),
                'suggestions' => $this->suggestions($response['suggestions'] ?? []),
            ]);

            return [$question, $answer];
        });
    }

    /**
     * The latest messages of the conversation, oldest first.
     *
     * @return Collection<int, UserMessage|AssistantMessage>
     */
    private function history(MayaConversation $conversation): Collection
    {
        if (! $conversation->exists) {
            return collect();
        }

        return $conversation->messages()
            ->reorder()
            ->latest('id')
            ->limit(config('maya.history'))
            ->get()
            ->reverse()
            ->map(fn (MayaMessage $message) => $message->isFromMaya()
                ? new AssistantMessage($message->content)
                : new UserMessage($message->content))
            ->values();
    }

    /**
     * @param  mixed  $placeIds
     * @param  mixed  $experienceIds
     * @return list<array{type: string, id: int}>
     */
    private function attachments($placeIds, $experienceIds): array
    {
        $places = PointOfInterest::query()->published()->whereKey($this->ids($placeIds, self::MAX_PLACES))->pluck('id');
        $experiences = Experience::query()->published()->whereKey($this->ids($experienceIds, self::MAX_EXPERIENCES))->pluck('id');

        return [
            ...$this->ids($placeIds, self::MAX_PLACES)->filter(fn (int $id): bool => $places->contains($id))
                ->map(fn (int $id): array => ['type' => 'point_of_interest', 'id' => $id])->all(),
            ...$this->ids($experienceIds, self::MAX_EXPERIENCES)->filter(fn (int $id): bool => $experiences->contains($id))
                ->map(fn (int $id): array => ['type' => 'experience', 'id' => $id])->all(),
        ];
    }

    /**
     * @param  mixed  $ids
     * @return Collection<int, int>
     */
    private function ids($ids, int $limit): Collection
    {
        return collect(is_array($ids) ? $ids : [])
            ->filter(fn ($id): bool => is_int($id) || (is_string($id) && ctype_digit($id)))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->take($limit)
            ->values();
    }

    /**
     * @param  mixed  $suggestions
     * @return list<string>
     */
    private function suggestions($suggestions): array
    {
        return collect(is_array($suggestions) ? $suggestions : [])
            ->filter(fn ($suggestion): bool => is_string($suggestion))
            ->map(fn (string $suggestion): string => Str::limit(trim($suggestion), 60, ''))
            ->filter()
            ->take(self::MAX_SUGGESTIONS)
            ->values()
            ->all();
    }
}
