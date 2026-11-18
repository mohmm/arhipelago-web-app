import { router, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { MailWarning } from 'lucide-react';
import { useState } from 'react';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { useLocalizedUrl } from '@/lib/locale';
import type { SharedData } from '@/types';

/**
 * A nudge to confirm the address, never a wall.
 *
 * Nothing in the product is gated on verification — putting a mailbox round
 * trip between choosing a plan and paying for it costs sales that a banner
 * costs nothing. What it protects is the invoice: an address nobody confirmed
 * is an address receipts quietly fail to reach.
 */
export function VerifyEmailBanner() {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const { auth } = usePage<SharedData>().props;
    const [sending, setSending] = useState(false);
    const [sent, setSent] = useState(false);

    if (!auth.user || auth.user.email_verified_at) {
        return null;
    }

    function resend() {
        setSending(true);

        router.post(
            url('/email/verification-notification'),
            {},
            {
                preserveScroll: true,
                // Kept on screen rather than reloading: the banner is the only
                // confirmation that anything happened, so it has to survive the
                // request that makes it true.
                onSuccess: () => setSent(true),
                onFinish: () => setSending(false),
            },
        );
    }

    return (
        <Alert className="border-amber-500/40 bg-amber-500/5">
            <MailWarning className="h-4 w-4 text-amber-600" />
            <AlertTitle>{t('Confirm your email address')}</AlertTitle>
            <AlertDescription className="flex flex-wrap items-center justify-between gap-3">
                <span>{t('We sent a link to :email. Your invoices and payment notices go to this address.', { email: auth.user.email })}</span>
                {sent ? (
                    <span className="text-sm font-medium text-emerald-600">{t('Sent — check your inbox.')}</span>
                ) : (
                    <Button variant="outline" size="sm" onClick={resend} disabled={sending}>
                        {sending ? t('Sending…') : t('Send it again')}
                    </Button>
                )}
            </AlertDescription>
        </Alert>
    );
}
