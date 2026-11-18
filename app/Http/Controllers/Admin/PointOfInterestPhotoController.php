<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PointOfInterestPhotoRequest;
use App\Models\PointOfInterest;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * The photo gallery of a point of interest. The first photo is the cover the
 * app shows everywhere; each photo keeps its licence and source for the record.
 */
class PointOfInterestPhotoController extends Controller
{
    public function store(PointOfInterestPhotoRequest $request, PointOfInterest $pointOfInterest): RedirectResponse
    {
        $this->authorize('update', $pointOfInterest);

        $pointOfInterest->addMedia($request->file('photo'))
            ->usingName($pointOfInterest->name_ro)
            ->withCustomProperties($request->safe()->only(PointOfInterest::PHOTO_SOURCE_FIELDS))
            ->toMediaCollection(PointOfInterest::PHOTOS);

        Toast::success(__('Photo added.'));

        return back();
    }

    public function cover(PointOfInterest $pointOfInterest, Media $media): RedirectResponse
    {
        $this->authorize('update', $pointOfInterest);
        $this->ensureBelongs($pointOfInterest, $media);

        $order = $pointOfInterest->getMedia(PointOfInterest::PHOTOS)->pluck('id')->reject(fn (int $id): bool => $id == $media->id);

        Media::setNewOrder([$media->id, ...$order->all()]);

        Toast::success(__('Cover photo changed.'));

        return back();
    }

    public function destroy(PointOfInterest $pointOfInterest, Media $media): RedirectResponse
    {
        $this->authorize('update', $pointOfInterest);
        $this->ensureBelongs($pointOfInterest, $media);

        if ($pointOfInterest->is_published && $pointOfInterest->getMedia(PointOfInterest::PHOTOS)->count() == 1) {
            Toast::error(__('A published place needs a photo. Add another photo or unpublish the place first.'));

            return back();
        }

        $media->delete();

        Toast::success(__('Photo deleted.'));

        return back();
    }

    private function ensureBelongs(PointOfInterest $pointOfInterest, Media $media): void
    {
        abort_unless(
            $media->model_type == $pointOfInterest->getMorphClass()
                && $media->model_id == $pointOfInterest->id
                && $media->collection_name == PointOfInterest::PHOTOS,
            404,
        );
    }
}
