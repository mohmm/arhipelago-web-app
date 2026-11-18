<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateLookupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $table = $this->getTableName();
        $id = $this->getModelId();

        $rules = [
            'name_en' => ['required', 'string', 'max:255'],
            'name_ro' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique($table, 'slug')->ignore($id)],
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['nullable', 'string', 'max:50'],
            'image_url' => ['nullable', 'string', 'max:2048', 'url:http,https'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];

        if ($table == 'topics') {
            $rules['name_en'][] = Rule::unique($table, 'name_en')->ignore($id);
            $rules['name_ro'][] = Rule::unique($table, 'name_ro')->ignore($id);
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->slug ?: Str::slug((string) $this->name_en),
            'is_active' => $this->boolean('is_active', true),
            'sort_order' => $this->sort_order ?? 0,
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => __('The slug may only contain lowercase letters, numbers, and hyphens.'),
            'color.regex' => __('The color must be a valid hex code, e.g. #3B82F6.'),
            'image_url.url' => __('The image URL must be a valid http or https link.'),
            'name_en.unique' => __('This English name is already in use.'),
            'name_ro.unique' => __('This Romanian name is already in use.'),
        ];
    }

    /**
     * Resolve the table name based on the current route.
     */
    private function getTableName(): string
    {
        $mapping = [
            'admin.lookups.categories.update' => 'point_of_interest_categories',
            'admin.lookups.topics.update' => 'topics',
        ];

        return $mapping[$this->route()->getName()] ?? 'point_of_interest_categories';
    }

    /**
     * Get the bound model id from the route parameters.
     */
    private function getModelId(): int
    {
        $model = $this->route('category') ?? $this->route('topic');

        return $model?->id ?? 0;
    }
}
