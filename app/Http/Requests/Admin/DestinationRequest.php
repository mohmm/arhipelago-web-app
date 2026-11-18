<?php

namespace App\Http\Requests\Admin;

use App\Models\Destination;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DestinationRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique(Destination::class, 'slug')->ignore($this->route('destination'))],
            'name_ro' => ['required', 'string', 'max:120'],
            'name_en' => ['nullable', 'string', 'max:120'],
            'name_ar' => ['nullable', 'string', 'max:120'],
            'region_ro' => ['required', 'string', 'max:255'],
            'region_en' => ['nullable', 'string', 'max:255'],
            'region_ar' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug') ? Str::slug((string) $this->input('slug')) : Str::slug((string) $this->input('name_ro')),
            'sort_order' => (int) $this->input('sort_order', 0),
        ]);
    }
}
