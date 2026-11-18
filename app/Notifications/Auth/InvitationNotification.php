<?php

namespace App\Notifications\Auth;

use App\Notifications\BrandedNotification;

/**
 * Invites somebody the back office just created an account for.
 *
 * Before this, staff typed a password on the new person's behalf and then had
 * to tell them what it was — over chat, usually, where it stayed. A link they
 * set themselves means nobody else ever knows it.
 *
 * The link is an ordinary password-reset token and expires like one. Saying so
 * matters: an invitation opened the next morning is dead, and the recovery is
 * one click on the sign-in page as long as they know to look.
 */
class InvitationNotification extends BrandedNotification
{
    public function __construct(
        #[\SensitiveParameter] private string $token,
        private ?string $invitedBy = null,
    ) {}

    protected function subject(object $notifiable): string
    {
        return __('You have been added to :app', ['app' => config('app.name')]);
    }

    protected function view(): string
    {
        return 'mail.auth.invitation';
    }

    /**
     * @return array<string, mixed>
     */
    protected function data(object $notifiable): array
    {
        return [
            'name' => $notifiable->first_name,
            'invitedBy' => $this->invitedBy,
            'url' => route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]),
            'loginUrl' => route('login'),
            'minutes' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
        ];
    }
}
