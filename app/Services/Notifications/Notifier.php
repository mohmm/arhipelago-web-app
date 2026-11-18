<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Notifications\BrandedNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends a branded notification without letting a mail failure break the request.
 *
 * A password reset or a verification link that cannot be delivered must not
 * turn the screen that asked for it into an error page: the failure is logged
 * with enough detail to act on, and the caller learns the outcome.
 */
class Notifier
{
    public function send(User $user, BrandedNotification $notification): bool
    {
        try {
            $user->notify($notification);

            return true;
        } catch (Throwable $exception) {
            Log::error('A notification could not be sent.', [
                'type' => $notification::class,
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
