import { Head, router, useForm } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import ConfirmButton from '@/components/admin/confirm-button';
import TranslatedField from '@/components/admin/translated-field';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { useLocalizedUrl } from '@/lib/locale';

type Destination = {
    id: number;
    slug: string;
    name_ro: string;
    name_en: string | null;
    name_ar: string | null;
    region_ro: string;
    region_en: string | null;
    region_ar: string | null;
    sort_order: number;
};

export default function DestinationForm({ destination }: { destination: Destination | null }) {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const base = url('/admin/destinations');

    const form = useForm({
        slug: destination?.slug ?? '',
        name_ro: destination?.name_ro ?? '',
        name_en: destination?.name_en ?? '',
        name_ar: destination?.name_ar ?? '',
        region_ro: destination?.region_ro ?? '',
        region_en: destination?.region_en ?? '',
        region_ar: destination?.region_ar ?? '',
        sort_order: destination?.sort_order ?? 0,
    });

    const save = (event: FormEvent) => {
        event.preventDefault();

        if (destination) {
            form.put(url(`/admin/destinations/${destination.id}`));
        } else {
            form.post(base);
        }
    };

    const setTranslated = (field: string, value: string) => form.setData(field as 'name_ro', value);
    const title = destination?.name_ro ?? t('New destination');

    return (
        <AppLayout breadcrumbs={[{ title: t('Destinations'), href: base }, { title, href: destination ? `${base}/${destination.id}/edit` : `${base}/create` }]}>
            <Head title={title} />

            <form onSubmit={save} className="flex max-w-3xl flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading title={title} description={t('The app shows the name in lists, such as “Classic route · Bran”, and the region on the experience page.')} />
                    <Button type="submit" disabled={form.processing}>
                        {t('Save')}
                    </Button>
                </div>

                <Card>
                    <CardContent className="grid gap-5 pt-6">
                        <TranslatedField label={t('Name')} name="name" values={form.data} onChange={setTranslated} errors={form.errors} hint={t('E.g. “Bran”.')} />
                        <TranslatedField label={t('Region')} name="region" values={form.data} onChange={setTranslated} errors={form.errors} hint={t('E.g. “Bran commune, Brașov”.')} />
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="slug">{t('Address in links')}</Label>
                                <Input id="slug" value={form.data.slug} onChange={(event) => form.setData('slug', event.target.value)} placeholder={t('Made from the name')} />
                                <InputError message={form.errors.slug} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="sort_order">{t('Order in lists')}</Label>
                                <Input id="sort_order" type="number" min={0} value={form.data.sort_order} onChange={(event) => form.setData('sort_order', Number(event.target.value))} />
                                <InputError message={form.errors.sort_order} />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {destination && (
                    <ConfirmButton
                        title={t('Delete this destination?')}
                        description={t('Only a destination without experiences can be deleted.')}
                        confirmLabel={t('Delete')}
                        onConfirm={() => router.delete(url(`/admin/destinations/${destination.id}`))}
                    >
                        <Button type="button" variant="ghost" className="justify-self-start text-destructive hover:text-destructive">
                            <Trash2 className="size-4" />
                            {t('Delete destination')}
                        </Button>
                    </ConfirmButton>
                )}
            </form>
        </AppLayout>
    );
}
