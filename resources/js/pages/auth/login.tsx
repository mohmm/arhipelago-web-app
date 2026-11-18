import { Form, Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Eye, EyeOff, LoaderCircle } from 'lucide-react';
import { useState } from 'react';
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { useLocalizedUrl } from '@/lib/locale';
import { request } from '@/routes/password';

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

/**
 * Signing in to the admin panel. Accounts are created by an administrator;
 * travellers sign up in the mobile app.
 */
export default function Login({ status, canResetPassword }: LoginProps) {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const submit = AuthenticatedSessionController.store.form();
    const [showPassword, setShowPassword] = useState(false);

    return (
        <AuthLayout title={t('Sign in')} description={t('The Smartsense admin panel.')}>
            <Head title={t('Sign in')} />

            {status && <div className="mb-4 rounded-lg bg-success-subtle px-4 py-3 text-sm font-medium text-success">{status}</div>}

            <Form {...submit} action={url(submit.action)} resetOnSuccess={['password']} className="grid gap-5">
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-2">
                            <Label htmlFor="email">{t('Email address')}</Label>
                            <Input id="email" type="email" name="email" required autoFocus autoComplete="email" placeholder={t('name@example.com')} className="h-11" />
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password">{t('Password')}</Label>
                            <div className="relative">
                                <Input
                                    id="password"
                                    type={showPassword ? 'text' : 'password'}
                                    name="password"
                                    required
                                    autoComplete="current-password"
                                    placeholder={t('Your password')}
                                    className="h-11 pe-11"
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword((shown) => !shown)}
                                    className="absolute inset-y-0 end-0 flex w-11 items-center justify-center text-muted-foreground hover:text-foreground"
                                    aria-label={showPassword ? t('Hide password') : t('Show password')}
                                >
                                    {showPassword ? <EyeOff className="size-5" /> : <Eye className="size-5" />}
                                </button>
                            </div>
                            <InputError message={errors.password} />
                            {canResetPassword && (
                                <TextLink href={url(request().url)} className="justify-self-end text-sm font-medium text-primary no-underline hover:underline">
                                    {t('Forgot your password?')}
                                </TextLink>
                            )}
                        </div>

                        <div className="flex items-center gap-3">
                            <Checkbox id="remember" name="remember" />
                            <Label htmlFor="remember" className="font-normal">
                                {t('Remember me')}
                            </Label>
                        </div>

                        <Button type="submit" size="lg" className="h-12 w-full text-base" disabled={processing}>
                            {processing && <LoaderCircle className="size-4 animate-spin" />}
                            {t('Sign in')}
                        </Button>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
