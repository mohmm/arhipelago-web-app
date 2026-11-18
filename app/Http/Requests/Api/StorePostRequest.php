<?php

namespace App\Http\Requests\Api;

use App\Models\Experience;
use App\Models\PointOfInterest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A new post. It takes no coordinates: a post sits where the place it is
 * about sits, so the app never stores where a traveller was standing.
 */
class StorePostRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:10240'],
            'point_of_interest_id' => ['nullable', 'integer', Rule::exists(PointOfInterest::class, 'id')->where('is_published', true)],
            'experience_id' => ['nullable', 'integer', Rule::exists(Experience::class, 'id')->where('is_published', true)],
        ];
    }
}
