<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Nobody signs up when the app has a fixed set of accounts (config/accounts.php).
     */
    public function authorize(): bool
    {
        return config('accounts.limit') === null;
    }

    protected function failedAuthorization(): never
    {
        throw new AuthorizationException(__('New accounts are given out by :app. Sign in with the one you received.', ['app' => config('app.name')]));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', Password::defaults()],
            'locale' => ['nullable', Rule::in(array_keys(config('translatable.languages')))],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
