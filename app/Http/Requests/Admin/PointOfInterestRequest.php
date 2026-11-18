<?php

namespace App\Http\Requests\Admin;

use App\Models\PointOfInterest;
use App\Models\PointOfInterestCategory;
use App\Models\Topic;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Creating or editing a point of interest. Romanian is required; English and
 * Arabic fall back to it in the app until they are written.
 */
class PointOfInterestRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $place = $this->route('pointOfInterest');

        return [
            'category_id' => ['required', 'integer', Rule::exists(PointOfInterestCategory::class, 'id')],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique(PointOfInterest::class, 'slug')->ignore($place)],
            'name_ro' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'type_ro' => ['required', 'string', 'max:60'],
            'type_en' => ['nullable', 'string', 'max:60'],
            'type_ar' => ['nullable', 'string', 'max:60'],
            'locality_ro' => ['required', 'string', 'max:120'],
            'locality_en' => ['nullable', 'string', 'max:120'],
            'locality_ar' => ['nullable', 'string', 'max:120'],
            'address_ro' => ['nullable', 'string', 'max:255'],
            'address_en' => ['nullable', 'string', 'max:255'],
            'address_ar' => ['nullable', 'string', 'max:255'],
            'description_ro' => ['required', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'description_ar' => ['nullable', 'string', 'max:5000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_historic_monument' => ['boolean'],
            'website' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'is_published' => ['boolean'],
            'topics' => ['array'],
            'topics.*' => ['integer', 'distinct', Rule::exists(Topic::class, 'id')],
        ];
    }

    /**
     * A place goes live only with a photo: every screen of the app shows one.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $place = $this->route('pointOfInterest');

            if ($this->boolean('is_published') && ! ($place instanceof PointOfInterest && $place->hasMedia(PointOfInterest::PHOTOS))) {
                $validator->errors()->add('is_published', __('Add a photo before publishing this place.'));
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug') ? Str::slug((string) $this->input('slug')) : Str::slug((string) $this->input('name_ro')),
            'is_historic_monument' => $this->boolean('is_historic_monument'),
            'is_published' => $this->boolean('is_published'),
            'topics' => $this->input('topics', []),
        ]);
    }
}
