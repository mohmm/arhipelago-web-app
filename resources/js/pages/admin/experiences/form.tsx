import { Head, router, useForm } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { RefreshCw, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import ConfirmButton from '@/components/admin/confirm-button';
import RouteMap from '@/components/admin/route-map';
import type { PlaceOption } from '@/components/admin/stops-editor';
import StopsEditor from '@/components/admin/stops-editor';
import TranslatedField from '@/components/admin/translated-field';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app-layout';
import { formatDistance, formatDuration } from '@/lib/format';
import { useLocalizedUrl } from '@/lib/locale';

type Option = { value: number | string; label: string };

type Experience = {
    id: number;
    destination_id: number;
    kind: string;
    travel_mode: string;
    title: string;
    description_ro: string | null;
    description_en: string | null;
    description_ar: string | null;
    description_ai_generated: boolean;
    is_published: boolean;
    distance_m: number;
    duration_s: number;
    route_polyline: string | null;
    stops: number[];
    measured_stops: { point_of_interest_id: number; distance_from_start_m: number; duration_from_start_s: number }[];
};

type Props = {
    experience: Experience | null;
    destinations: Option[];
    kinds: (Option & { travel_mode: string })[];
    travelModes: Option[];
    places: PlaceOption[];
};

export default function ExperienceForm({ experience, destinations, kinds, travelModes, places }: Props) {
    const { t, currentLocale } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const base = url('/admin/experiences');
    const locale = currentLocale() ?? 'ro';

    const form = useForm({
        destination_id: experience?.destination_id ?? (destinations[0]?.value as number | undefined) ?? 0,
        kind: experience?.kind ?? String(kinds[0]?.value ?? 'classic'),
        travel_mode: experience?.travel_mode ?? kinds[0]?.travel_mode ?? 'car',
        description_ro: experience?.description_ro ?? '',
        description_en: experience?.description_en ?? '',
        description_ar: experience?.description_ar ?? '',
        description_ai_generated: experience?.description_ai_generated ?? false,
        is_published: experience?.is_published ?? false,
        stops: experience?.stops ?? ([] as number[]),
    });

    const save = (event: FormEvent) => {
        event.preventDefault();

        if (experience) {
            form.put(url(`/admin/experiences/${experience.id}`), { preserveScroll: true });
        } else {
            form.post(base);
        }
    };

    const placeById = new Map(places.map((place) => [place.value, place]));
    const stopsSaved = experience != null && JSON.stringify(experience.stops) == JSON.stringify(form.data.stops);
    const title = experience?.title ?? t('New experience');

    return (
        <AppLayout
            breadcrumbs={[
                { title: t('Experiences'), href: base },
                { title, href: experience ? `${base}/${experience.id}/edit` : `${base}/create` },
            ]}
        >
            <Head title={title} />

            <form onSubmit={save} className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading title={title} description={t('Saving new stops measures the route again on real roads.')} />
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

                <div className="grid gap-6 lg:grid-cols-3">
                    <div className="grid content-start gap-6 lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t('Stops')}</CardTitle>
                                <CardDescription>{t('In the order the route visits them. Only published points of interest can be stops.')}</CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                <StopsEditor stops={form.data.stops} places={places} onChange={(stops) => form.setData('stops', stops)} />
                                <InputError message={form.errors.stops ?? Object.entries(form.errors).find(([field]) => field.startsWith('stops.'))?.[1]} />
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex-row items-start justify-between gap-4 space-y-0">
                                <div className="grid gap-1.5">
                                    <CardTitle>{t('Route')}</CardTitle>
                                    <CardDescription>
                                        {experience && experience.distance_m > 0 && stopsSaved
                                            ? `${formatDistance(experience.distance_m, locale)} · ${formatDuration(experience.duration_s)}`
                                            : stopsSaved
                                              ? t('Not measured yet')
                                              : t('Save to measure the new route.')}
                                    </CardDescription>
                                </div>
                                {experience && stopsSaved && (
                                    <Button type="button" variant="outline" size="sm" onClick={() => router.post(url(`/admin/experiences/${experience.id}/measure`), {}, { preserveScroll: true })}>
                                        <RefreshCw className="size-4" />
                                        {t('Measure again')}
                                    </Button>
                                )}
                            </CardHeader>
                            <CardContent>
                                <RouteMap
                                    polyline={stopsSaved ? (experience?.route_polyline ?? null) : null}
                                    stops={form.data.stops
                                        .map((id) => placeById.get(id))
                                        .filter((place): place is PlaceOption => place != null)
                                        .map((place) => ({ latitude: place.latitude, longitude: place.longitude, label: place.label }))}
                                />
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{t('Description')}</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                <TranslatedField
                                    label={t('Description')}
                                    name="description"
                                    values={form.data}
                                    onChange={(field, value) => form.setData(field as 'description_ro', value)}
                                    errors={form.errors}
                                    multiline
                                />
                                <label className="flex items-center gap-3 text-sm">
                                    <Checkbox checked={form.data.description_ai_generated} onCheckedChange={(checked) => form.setData('description_ai_generated', checked == true)} />
                                    {t('Written with AI (the app says so under the text)')}
                                </label>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="grid content-start gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t('Details')}</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-5">
                                <div className="grid gap-2">
                                    <Label>{t('Destination')}</Label>
                                    <Select value={String(form.data.destination_id)} onValueChange={(value) => form.setData('destination_id', Number(value))}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Choose a destination')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {destinations.map((destination) => (
                                                <SelectItem key={destination.value} value={String(destination.value)}>
                                                    {destination.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={form.errors.destination_id} />
                                </div>
                                <div className="grid gap-2">
                                    <Label>{t('Kind')}</Label>
                                    <Select
                                        value={form.data.kind}
                                        onValueChange={(value) =>
                                            form.setData((data) => ({ ...data, kind: value, travel_mode: kinds.find((kind) => kind.value == value)?.travel_mode ?? data.travel_mode }))
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {kinds.map((kind) => (
                                                <SelectItem key={kind.value} value={String(kind.value)}>
                                                    {kind.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={form.errors.kind} />
                                </div>
                                <div className="grid gap-2">
                                    <Label>{t('Travelled')}</Label>
                                    <Select value={form.data.travel_mode} onValueChange={(value) => form.setData('travel_mode', value)}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {travelModes.map((mode) => (
                                                <SelectItem key={mode.value} value={String(mode.value)}>
                                                    {mode.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={form.errors.travel_mode} />
                                </div>
                            </CardContent>
                        </Card>

                        {experience && (
                            <ConfirmButton
                                title={t('Delete this experience?')}
                                description={t('It disappears from the app. Posts about it stay, without the link.')}
                                confirmLabel={t('Delete')}
                                onConfirm={() => router.delete(url(`/admin/experiences/${experience.id}`))}
                            >
                                <Button type="button" variant="ghost" className="justify-self-start text-destructive hover:text-destructive">
                                    <Trash2 className="size-4" />
                                    {t('Delete experience')}
                                </Button>
                            </ConfirmButton>
                        )}
                    </div>
                </div>
            </form>
        </AppLayout>
    );
}
