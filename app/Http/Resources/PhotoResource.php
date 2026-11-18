<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesViewer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A photo of a point of interest, with the credit its licence requires.
 *
 * @mixin Media
 */
class PhotoResource extends JsonResource
{
    use ResolvesViewer;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'thumb_url' => $this->absoluteUrl($this->getUrl('thumb')),
            'url' => $this->absoluteUrl($this->getUrl('large')),
            'author' => $this->getCustomProperty('author'),
            'license' => $this->getCustomProperty('license'),
            'source_url' => $this->getCustomProperty('source_url'),
        ];
    }
}
