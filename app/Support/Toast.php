<?php

namespace App\Support;

use Inertia\Inertia;
use Inertia\Support\SessionKey;

/**
 * The one way the admin panel and the website tell somebody what happened.
 *
 * Sent as Inertia flash data rather than a shared prop: it reaches the next
 * page once, is never replayed from the browser's history, and shows again
 * when the same action is repeated. `resources/js/lib/toast.ts` draws it,
 * always at the top of the screen.
 *
 * Form mistakes are not toasts: they stay beside the field they concern.
 */
final class Toast
{
    public const SUCCESS = 'success';

    public const ERROR = 'error';

    public const INFO = 'info';

    /** Where the toast waits in the session for the next page, for tests to read. */
    public const SESSION_KEY = SessionKey::FLASH_DATA.'.toast';

    public static function success(string $message): void
    {
        self::send(self::SUCCESS, $message);
    }

    public static function error(string $message): void
    {
        self::send(self::ERROR, $message);
    }

    public static function info(string $message): void
    {
        self::send(self::INFO, $message);
    }

    private static function send(string $type, string $message): void
    {
        Inertia::flash('toast', ['type' => $type, 'message' => $message]);
    }
}
