import { Head, useForm, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import type { FormEvent } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { useLocalizedUrl } from '@/lib/locale';
import type { SharedData } from '@/types';

/**
 * Maya's instructions: who she is and how she speaks to travellers. The rules
 * that keep her to the app's own data are built in and cannot be edited here.
 */
export default function MayaSettings({ instructions, model }: { instructions: string; model: string }) {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const { assistant } = usePage<SharedData>().props;
    const form = useForm({ instructions });

    const save = (event: FormEvent) => {
        event.preventDefault();
        form.put(url('/admin/maya'), { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={[{ title: assistant, href: url('/admin/maya') }]}>
            <Head title={assistant} />

            <form onSubmit={save} className="flex max-w-4xl flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex items-start gap-4">
                    <img src="/images/maya.jpg" alt="" className="size-14 rounded-full object-cover" />
                    <Heading
                        title={t(':assistant, the travel assistant', { assistant })}
                        description={t(
                            'One assistant for every traveller, who recommends points of interest and experiences from the app and answers questions about them.',
                        )}
                    />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('Instructions')}</CardTitle>
                        <CardDescription>
                            {t(
                                'Who :assistant is and how :assistant speaks. Whatever you write, :assistant only uses the app’s own data, says so when something is not known, and answers in the traveller’s language.',
                                { assistant },
                            )}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-3">
                        <Textarea
                            rows={16}
                            value={form.data.instructions}
                            onChange={(event) => form.setData('instructions', event.target.value)}
                            className="font-mono text-sm"
                        />
                        <InputError message={form.errors.instructions} />
                        <p className="text-xs text-muted-foreground">{t('Model: :model', { model })}</p>
                    </CardContent>
                </Card>

                <Button type="submit" disabled={form.processing || !form.isDirty} className="justify-self-start">
                    {t('Save instructions')}
                </Button>
            </form>
        </AppLayout>
    );
}
