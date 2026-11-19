import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import ConfirmButton from '@/components/admin/confirm-button';
import LocationPicker from '@/components/admin/location-picker';
import type { Photo } from '@/components/admin/photo-manager';
import PhotoManager from '@/components/admin/photo-manager';
import TranslatedField from '@/components/admin/translated-field';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app-layout';
import { useLocalizedUrl } from '@/lib/locale';
import type { SharedData } from '@/types';

type Option = { value: number; label: string };

type Place = {
    id: number;
    category_id: number;
    slug: string;
    latitude: number;
    longitude: number;
    is_historic_monument: boolean;
    website: string | null;
    is_published: boolean;
    topics: number[];
    photos: Photo[];
    experiences: { id: number; title: string }[];
    [field: string]: unknown;
};

type Props = { place: Place | null; categories: Option[]; topics: Option[]; photoLicenses: { value: string; label: string }[] };

const TRANSLATED = ['name', 'type', 'locality', 'address', 'description'];

function emptyTranslations(place: Place | null): Record<string, string> {
    return Object.fromEntries(
        TRANSLATED.flatMap((attribute) =>
            ['ro', 'en', 'ar'].map((locale) => [`${attribute}_${locale}`, (place?.[`${attribute}_${locale}`] as string | null) ?? '']),
        ),
    );
}

export default function PointOfInterestForm({ place, categories, topics, photoLicenses }: Props) {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const { assistant } = usePage<SharedData>().props;
    const base = url('/admin/points-of-interest');

    const form = useForm({
        category_id: place?.category_id ?? categories[0]?.value ?? 0,
        slug: place?.slug ?? '',
        ...emptyTranslations(place),
        latitude: place?.latitude ?? (null as number | null),
        longitude: place?.longitude ?? (null as number | null),
        is_historic_monument: place?.is_historic_monument ?? false,
        website: place?.website ?? '',
        is_published: place?.is_published ?? false,
        topics: place?.topics ?? ([] as number[]),
    });

    const save = (event: FormEvent) => {
        event.preventDefault();

        if (place) {
            form.put(url(`/admin/points-of-interest/${place.id}`), { preserveScroll: true });
        } else {
            form.post(base);
        }
    };

    const setTranslated = (field: string, value: string) => form.setData(field as keyof typeof form.data, value as never);
    const title = place ? ((place.name_ro as string) ?? t('Point of interest')) : t('New point of interest');

    return (
        <AppLayout
            breadcrumbs={[
                { title: t('Points of interest'), href: base },
                { title, href: place ? `${base}/${place.id}/edit` : `${base}/create` },
            ]}
        >
            <Head title={title} />

            <form onSubmit={save} className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title={title}
                        description={t('Written in Romanian first; English and Arabic fall back to Romanian until they are written.')}
                    />
                    <div className="flex items-center gap-3">
                        <label className="flex items-center gap-2 text-sm font-medium">
                            <Switch checked={form.data.is_published} onCheckedChange={(checked) => form.setData('is_published', checked)} />
                            {t('Published in the app')}
                        </label>
                        <Button type="submit" disabled={form.processing}>
                            {t('Save')}
                        </Button>
                    </div>
                </div>
                <InputError message={form.errors.is_published} />

                <div className="grid gap-6 lg:grid-cols-3">
                    <div className="grid gap-6 lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t('Description')}</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-5">
                                <TranslatedField label={t('Name')} name="name" values={form.data} onChange={setTranslated} errors={form.errors} />
                                <div className="grid gap-5 sm:grid-cols-2">
                                    <TranslatedField
                                        label={t('Kind of place')}
                                        name="type"
                                        values={form.data}
                                        onChange={setTranslated}
                                        errors={form.errors}
                                        hint={t('Short, e.g. “Castle” or “Fortress”.')}
                                    />
                                    <TranslatedField
                                        label={t('Locality')}
                                        name="locality"
                                        values={form.data}
                                        onChange={setTranslated}
                                        errors={form.errors}
                                        hint={t('As the list shows it, e.g. “Bran” or “near Zărnești”.')}
                                    />
                                </div>
                                <TranslatedField
                                    label={t('Address')}
                                    name="address"
                                    values={form.data}
                                    onChange={setTranslated}
                                    errors={form.errors}
                                    hint={t('In full, e.g. “Bran commune, Brașov County”.')}
                                />
                                <TranslatedField
                                    label={t('Description')}
                                    name="description"
                                    values={form.data}
                                    onChange={setTranslated}
                                    errors={form.errors}
                                    multiline
                                />
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{t('Location')}</CardTitle>
                                <CardDescription>{t('Click the map to place the pin.')}</CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                <LocationPicker
                                    latitude={form.data.latitude}
                                    longitude={form.data.longitude}
                                    onChange={(latitude, longitude) => form.setData((data) => ({ ...data, latitude, longitude }))}
                                />
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="latitude">{t('Latitude')}</Label>
                                        <Input
                                            id="latitude"
                                            type="number"
                                            step="0.000001"
                                            value={form.data.latitude ?? ''}
                                            onChange={(event) =>
                                                form.setData('latitude', event.target.value == '' ? null : Number(event.target.value))
                                            }
                                        />
                                        <InputError message={form.errors.latitude} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="longitude">{t('Longitude')}</Label>
                                        <Input
                                            id="longitude"
                                            type="number"
                                            step="0.000001"
                                            value={form.data.longitude ?? ''}
                                            onChange={(event) =>
                                                form.setData('longitude', event.target.value == '' ? null : Number(event.target.value))
                                            }
                                        />
                                        <InputError message={form.errors.longitude} />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {place && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>{t('Photos')}</CardTitle>
                                    <CardDescription>
                                        {t('The first photo is the cover. Credit every photo as its licence requires.')}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <PhotoManager placeId={place.id} photos={place.photos} licenses={photoLicenses} />
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    <div className="grid content-start gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t('Details')}</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-5">
                                <div className="grid gap-2">
                                    <Label>{t('Category')}</Label>
                                    <Select
                                        value={String(form.data.category_id)}
                                        onValueChange={(value) => form.setData('category_id', Number(value))}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categories.map((category) => (
                                                <SelectItem key={category.value} value={String(category.value)}>
                                                    {category.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={form.errors.category_id} />
                                </div>
                                <label className="flex items-center gap-3 text-sm">
                                    <Checkbox
                                        checked={form.data.is_historic_monument}
                                        onCheckedChange={(checked) => form.setData('is_historic_monument', checked == true)}
                                    />
                                    {t('Listed historic monument')}
                                </label>
                                <div className="grid gap-2">
                                    <Label htmlFor="website">{t('Official website')}</Label>
                                    <Input
                                        id="website"
                                        type="url"
                                        value={form.data.website}
                                        onChange={(event) => form.setData('website', event.target.value)}
                                        placeholder="https://"
                                    />
                                    <InputError message={form.errors.website} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="slug">{t('Address in links')}</Label>
                                    <Input
                                        id="slug"
                                        value={form.data.slug}
                                        onChange={(event) => form.setData('slug', event.target.value)}
                                        placeholder={t('Made from the name')}
                                    />
                                    <InputError message={form.errors.slug} />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{t('Themes')}</CardTitle>
                                <CardDescription>{t('Help :assistant recommend it to the right travellers.', { assistant })}</CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-3">
                                {topics.map((topic) => (
                                    <label key={topic.value} className="flex items-center gap-3 text-sm">
                                        <Checkbox
                                            checked={form.data.topics.includes(topic.value)}
                                            onCheckedChange={(checked) =>
                                                form.setData(
                                                    'topics',
                                                    checked ? [...form.data.topics, topic.value] : form.data.topics.filter((id) => id != topic.value),
                                                )
                                            }
                                        />
                                        {topic.label}
                                    </label>
                                ))}
                            </CardContent>
                        </Card>

                        {place && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>{t('In experiences')}</CardTitle>
                                </CardHeader>
                                <CardContent className="grid gap-2 text-sm">
                                    {place.experiences.length == 0 && <p className="text-muted-foreground">{t('No experience stops here yet.')}</p>}
                                    {place.experiences.map((experience) => (
                                        <Link
                                            key={experience.id}
                                            href={url(`/admin/experiences/${experience.id}/edit`)}
                                            className="text-primary hover:underline"
                                        >
                                            {experience.title}
                                        </Link>
                                    ))}
                                </CardContent>
                            </Card>
                        )}

                        {place && (
                            <ConfirmButton
                                title={t('Delete this point of interest?')}
                                description={t('It disappears from the app, with its photos. This cannot be undone.')}
                                confirmLabel={t('Delete')}
                                onConfirm={() => router.delete(url(`/admin/points-of-interest/${place.id}`))}
                            >
                                <Button type="button" variant="ghost" className="justify-self-start text-destructive hover:text-destructive">
                                    <Trash2 className="size-4" />
                                    {t('Delete point of interest')}
                                </Button>
                            </ConfirmButton>
                        )}
                    </div>
                </div>
            </form>
        </AppLayout>
    );
}
