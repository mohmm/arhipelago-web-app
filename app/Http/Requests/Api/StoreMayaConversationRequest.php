<?php

namespace App\Http\Requests\Api;

use App\Models\Experience;
use App\Models\PointOfInterest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMayaConversationRequest extends FormRequest
{
    /**
     * A conversation may be about one place or one experience, never both, and
     * may start with the traveller's first message.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'point_of_interest_id' => ['nullable', 'integer', 'prohibits:experience_id', Rule::exists(PointOfInterest::class, 'id')->where('is_published', true)],
            'experience_id' => ['nullable', 'integer', Rule::exists(Experience::class, 'id')->where('is_published', true)],
            'message' => ['nullable', 'string', 'max:'.config('maya.max_message_length')],
        ];
    }
}
