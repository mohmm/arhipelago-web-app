<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\Auth\PasswordChangedNotification;
use App\Services\Notifications\Notifier;
use Illuminate\Auth\Events\PasswordReset;

/**
 * Tells somebody their password changed, whoever changed it.
 *
 * The point is the case where it was not them: a reset completed from a hijacked
 * mailbox is silent otherwise, and silence is what turns a stolen password into
 * a stolen account.
 */
class SendPasswordChangedNotice
{
    public function __construct(private Notifier $notifier) {}

    public function handle(PasswordReset $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->notifier->send($event->user, new PasswordChangedNotification(request()->ip()));
    }
}
