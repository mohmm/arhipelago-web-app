import { router } from '@inertiajs/react';
import { toast as sonnerToast } from 'sonner';

export type ToastType = 'success' | 'error' | 'info';

type ToastMessage = { type: ToastType; message: string };

/**
 * The one way the admin panel and the website show a notification.
 *
 * Every toast goes through here, so they all look and behave the same: at the
 * top of the screen (`components/ui/sonner.tsx`), one per message, with errors
 * kept on screen a little longer. Most of them come from the server, which
 * sends them with `App\Support\Toast`; `listenForServerToasts` shows those.
 */
function show(type: ToastType, message: string): void {
    // The same message twice at once (a double click, say) shows once.
    sonnerToast[type](message, { id: `${type}:${message}`, duration: type == 'error' ? 7000 : 4000 });
}

export const toast = {
    success: (message: string) => show('success', message),
    error: (message: string) => show('error', message),
    info: (message: string) => show('info', message),
};

/** The first validation message, for actions that have no field to show it beside. */
export function firstError(errors: Record<string, string>): string | undefined {
    return Object.values(errors)[0];
}

function isToastMessage(value: unknown): value is ToastMessage {
    if (typeof value != 'object' || value == null) {
        return false;
    }

    const { type, message } = value as Record<string, unknown>;

    return (type == 'success' || type == 'error' || type == 'info') && typeof message == 'string' && message != '';
}

/**
 * Shows the toasts the server flashes with `App\Support\Toast`. Called once,
 * when the app starts.
 *
 * Inertia fires `flash` once per response that carries flash data, including
 * the first page load, so a message is never missed and never replayed when
 * the visitor goes back in their history.
 */
export function listenForServerToasts(): void {
    router.on('flash', (event) => {
        const flashed: unknown = (event.detail.flash as Record<string, unknown>).toast;

        if (isToastMessage(flashed)) {
            show(flashed.type, flashed.message);
        }
    });
}
