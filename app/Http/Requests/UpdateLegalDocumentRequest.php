<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLegalDocumentRequest extends FormRequest
{
    /**
     * Authorization is enforced by the controller's policy check.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The slug is absent on purpose: it is what the routes and footer links
     * point at, so it is fixed rather than editable.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title_ro' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            // Generous, but not unbounded: a full set of terms runs to tens of
            // thousands of characters and the column is a longText.
            'body_ro' => ['required', 'string', 'max:200000'],
            'body_en' => ['required', 'string', 'max:200000'],
            'version' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._\- ]+$/'],
            'effective_date' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'version.regex' => __('The version may only contain letters, numbers, spaces, dots, underscores and hyphens.'),
        ];
    }
}
