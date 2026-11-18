<?php

namespace App\Services\Maya;

use App\Models\Experience;
use App\Models\MayaMessage;
use App\Models\PointOfInterest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Turns the ids stored on Maya's replies into the cards the app shows, with
 * today's names and photos. Places or experiences unpublished since she
 * mentioned them simply drop out.
 */
class MayaCards
{
    /**
     * @param  iterable<MayaMessage>  $messages
     */
    public function attach(iterable $messages): void
    {
        $messages = collect($messages);
        $attachments = $messages->flatMap(fn (MayaMessage $message): array => $message->attachments ?? []);

        $places = PointOfInterest::query()
            ->published()
            ->with(['category', 'media'])
            ->withCount(['experiences' => fn (Builder $experiences) => $experiences->where('is_published', true)])
            ->findMany($attachments->where('type', 'point_of_interest')->pluck('id')->unique())
            ->keyBy('id');

        $experiences = Experience::query()
            ->published()
            ->with(['destination', 'stops.pointOfInterest'])
            ->findMany($attachments->where('type', 'experience')->pluck('id')->unique())
            ->keyBy('id');

        $messages->each(function (MayaMessage $message) use ($places, $experiences): void {
            $own = collect($message->attachments ?? []);

            $message->setRelation('cardPointsOfInterest', $this->resolve($own, 'point_of_interest', $places));
            $message->setRelation('cardExperiences', $this->resolve($own, 'experience', $experiences));
        });
    }

    /**
     * @param  Collection<int, array{type: string, id: int}>  $attachments
     * @param  Collection<int, PointOfInterest|Experience>  $models
     * @return Collection<int, PointOfInterest|Experience>
     */
    private function resolve(Collection $attachments, string $type, Collection $models): Collection
    {
        return $attachments->where('type', $type)
            ->map(fn (array $attachment) => $models->get($attachment['id']))
            ->filter()
            ->values();
    }
}
