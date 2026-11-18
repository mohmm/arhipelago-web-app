<?php

namespace App\Notifications\Auth;

use App\Notifications\BrandedNotification;

/**
 * The one email nobody reads twice and everybody needs to work first time.
 */
class ResetPasswordNotification extends BrandedNotification
{
    public function __construct(#[\SensitiveParameter] private string $token) {}

    protected function subject(object $notifiable): string
    {
        return __('Reset your password');
    }

    protected function view(): string
    {
        return 'mail.auth.reset-password';
    }

    /**
     * @return array<string, mixed>
     */
    protected function data(object $notifiable): array
    {
        return [
            'name' => $notifiable->first_name,
            'url' => route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]),
            'minutes' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
        ];
    }
}
