<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('user')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user')?->id)],
            'password' => ['nullable', 'string', Password::defaults(), 'confirmed'],
            'role' => ['required', 'string', Rule::in($this->selectableRoleNames())],
        ];
    }

    /**
     * The roles this request may set on the target user.
     *
     * Nobody may change their own role, whatever permissions they hold —
     * otherwise `user.update` quietly becomes a self-promotion primitive. Your
     * own row is therefore pinned to the role you already have.
     *
     * @return array<int, string>
     */
    private function selectableRoleNames(): array
    {
        $actor = $this->user();
        $target = $this->route('user');

        if (! $actor) {
            return [];
        }

        if ($target && $actor->id == $target->id) {
            return $actor->getRoleNames()->unique()->values()->all();
        }

        return $actor->assignableRoleNames();
    }
}
