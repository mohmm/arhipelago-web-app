<?php

namespace App\Notifications\Auth;

use App\Notifications\BrandedNotification;
use Illuminate\Support\Facades\URL;

/**
 * Confirms the address invoices and receipts will be sent to.
 *
 * Nothing is gated on it. The link is worth following because a mistyped
 * address means a receipt nobody receives, not because the product stops
 * working until it is clicked.
 */
class VerifyEmailNotification extends BrandedNotification
{
    protected function subject(object $notifiable): string
    {
        return __('Confirm your email address');
    }

    protected function view(): string
    {
        return 'mail.auth.verify-email';
    }

    /**
     * @return array<string, mixed>
     */
    protected function data(object $notifiable): array
    {
        return [
            'name' => $notifiable->first_name,
            'url' => $this->verificationUrl($notifiable),
            'minutes' => (int) config('auth.verification.expire', 60),
        ];
    }

    /**
     * A signed link that stops working on its own.
     *
     * The signature is what makes the address proof of anything: without it,
     * anybody who guessed a user id could mark somebody else's email verified.
     */
    private function verificationUrl(object $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes((int) config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );
    }
}
