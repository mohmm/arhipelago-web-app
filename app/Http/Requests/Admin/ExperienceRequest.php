<?php

namespace App\Http\Requests\Admin;

use App\Enums\ExperienceKind;
use App\Enums\TravelMode;
use App\Models\Destination;
use App\Models\PointOfInterest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Creating or editing an experience: its destination and kind, how it is
 * travelled, its description and its stops in order.
 */
class ExperienceRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'destination_id' => ['required', 'integer', Rule::exists(Destination::class, 'id')],
            // One experience of each kind per destination: that is what the app switches between.
            'kind' => [
                'required',
                Rule::enum(ExperienceKind::class),
                Rule::unique('experiences', 'kind')
                    ->where('destination_id', $this->integer('destination_id'))
                    ->ignore($this->route('experience')),
            ],
            'travel_mode' => ['required', Rule::enum(TravelMode::class)],
            'description_ro' => ['required', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'description_ar' => ['nullable', 'string', 'max:5000'],
            'description_ai_generated' => ['boolean'],
            'is_published' => ['boolean'],
            'stops' => ['required', 'array', 'min:2', 'max:12'],
            'stops.*' => ['integer', 'distinct', Rule::exists(PointOfInterest::class, 'id')->where('is_published', true)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kind.unique' => __('This destination already has an experience of this kind.'),
            'stops.min' => __('An experience needs at least two stops.'),
            'stops.*.exists' => __('Every stop must be a published point of interest.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'description_ai_generated' => $this->boolean('description_ai_generated'),
            'is_published' => $this->boolean('is_published'),
        ]);
    }
}
