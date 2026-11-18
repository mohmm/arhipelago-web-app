<?php

namespace App\Notifications\Auth;

use App\Notifications\BrandedNotification;

/**
 * Tells somebody their password changed, so that if it was not them they find
 * out now rather than the next time they try to sign in.
 *
 * Carries no link to click. A security notice whose call to action is a link is
 * indistinguishable from the phishing mail it warns about.
 */
class PasswordChangedNotification extends BrandedNotification
{
    public function __construct(private ?string $ipAddress = null) {}

    protected function subject(object $notifiable): string
    {
        return __('Your password was changed');
    }

    protected function view(): string
    {
        return 'mail.auth.password-changed';
    }

    /**
     * @return array<string, mixed>
     */
    protected function data(object $notifiable): array
    {
        return [
            'name' => $notifiable->first_name,
            'ipAddress' => $this->ipAddress,
            'supportEmail' => config('company.email'),
        ];
    }
}
