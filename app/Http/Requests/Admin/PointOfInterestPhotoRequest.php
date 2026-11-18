<?php

namespace App\Http\Requests\Admin;

use App\Enums\PhotoLicense;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A photo for a point of interest. The app shows it without a credit, so its
 * licence must ask for none; where it came from is kept for the record.
 */
class PointOfInterestPhotoRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:10240'],
            'license' => ['required', Rule::enum(PhotoLicense::class)],
            'author' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'string', 'max:255', 'url:http,https'],
        ];
    }
}
