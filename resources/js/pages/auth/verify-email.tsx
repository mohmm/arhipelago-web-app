// Components
import { Form, Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { LoaderCircle } from 'lucide-react';
import EmailVerificationNotificationController from '@/actions/App/Http/Controllers/Auth/EmailVerificationNotificationController';

import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/auth-layout';
import { useLocalizedUrl } from '@/lib/locale';
import { logout } from '@/routes';

export default function VerifyEmail({ status }: { status?: string }) {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const submit = EmailVerificationNotificationController.store.form();

    return (
        <AuthLayout title={t('Verify email')} description={t('Please verify your email address by clicking on the link we just emailed to you.')}>
            <Head title={t('Email verification')} />

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {t('A new verification link has been sent to the email address you provided during registration.')}
                </div>
            )}

            <Form {...submit} action={url(submit.action)} className="space-y-6 text-center">
                {({ processing }) => (
                    <>
                        <Button disabled={processing} variant="secondary">
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                            {t('Resend verification email')}
                        </Button>

                        <TextLink href={logout()} className="mx-auto block text-sm">
                            {t('Log out')}
                        </TextLink>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
