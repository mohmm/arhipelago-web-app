import { Head, Link, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Car, Footprints, Plus } from 'lucide-react';
import { experienceTabs } from '@/components/admin/section-tab-sets';
import SectionTabs from '@/components/admin/section-tabs';
import StatusBadge from '@/components/admin/status-badge';
import Heading from '@/components/heading';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatDistance, formatDuration } from '@/lib/format';
import { useLocalizedUrl } from '@/lib/locale';
import type { PaginatedData } from '@/types';

type Experience = {
    id: number;
    title: string;
    kind: string;
    travel_mode: 'car' | 'foot';
    distance_m: number;
    duration_s: number;
    stops_count: number;
    is_published: boolean;
};

type Option = { value: number | string; label: string };

type Props = {
    experiences: PaginatedData<Experience>;
    filters: { destination?: string; kind?: string; status?: string };
    destinations: Option[];
    kinds: Option[];
};

const ALL = 'all';

export default function ExperiencesIndex({ experiences, filters, destinations, kinds }: Props) {
    const { t, currentLocale } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const base = url('/admin/experiences');

    const filter = (changes: Record<string, string>) => {
        const next = { ...filters, ...changes };
        router.get(base, Object.fromEntries(Object.entries(next).filter(([, value]) => value != null && value != '' && value != ALL)), { preserveState: true });
    };

    return (
        <AppLayout breadcrumbs={[{ title: t('Experiences'), href: base }]}>
            <Head title={t('Experiences')} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading title={t('Experiences')} description={t('Day routes with stops at points of interest, measured on real roads.')} />
                    <Button asChild>
                        <Link href={`${base}/create`}>
                            <Plus className="size-4" />
                            {t('New experience')}
                        </Link>
                    </Button>
                </div>

                <SectionTabs tabs={experienceTabs(t, url)} />

                <div className="flex flex-wrap gap-3">
                    <Select value={filters.destination ?? ALL} onValueChange={(value) => filter({ destination: value })}>
                        <SelectTrigger className="w-52">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>{t('All destinations')}</SelectItem>
                            {destinations.map((destination) => (
                                <SelectItem key={destination.value} value={String(destination.value)}>
                                    {destination.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={filters.kind ?? ALL} onValueChange={(value) => filter({ kind: value })}>
                        <SelectTrigger className="w-52">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>{t('All kinds')}</SelectItem>
                            {kinds.map((kind) => (
                                <SelectItem key={kind.value} value={String(kind.value)}>
                                    {kind.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={filters.status ?? ALL} onValueChange={(value) => filter({ status: value })}>
                        <SelectTrigger className="w-44">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>{t('Any status')}</SelectItem>
                            <SelectItem value="published">{t('Published')}</SelectItem>
                            <SelectItem value="draft">{t('Draft')}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <Card className="overflow-hidden p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Experience')}</TableHead>
                                <TableHead>{t('Route')}</TableHead>
                                <TableHead className="text-right">{t('Stops')}</TableHead>
                                <TableHead>{t('Status')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {experiences.data.length == 0 && (
                                <TableRow>
                                    <TableCell colSpan={4} className="py-10 text-center text-muted-foreground">
                                        {t('No experiences match.')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {experiences.data.map((experience) => (
                                <TableRow key={experience.id} className="cursor-pointer" onClick={() => router.visit(`${base}/${experience.id}/edit`)}>
                                    <TableCell className="font-medium">{experience.title}</TableCell>
                                    <TableCell>
                                        <span className="inline-flex items-center gap-2 text-sm">
                                            {experience.travel_mode == 'foot' ? <Footprints className="size-4" /> : <Car className="size-4" />}
                                            {experience.distance_m > 0
                                                ? `${formatDistance(experience.distance_m, currentLocale() ?? 'ro')} · ${formatDuration(experience.duration_s)}`
                                                : t('Not measured yet')}
                                        </span>
                                    </TableCell>
                                    <TableCell className="text-right">{experience.stops_count}</TableCell>
                                    <TableCell>
                                        <StatusBadge published={experience.is_published} />
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Card>

                <TablePagination meta={experiences} links={experiences.links} />
            </div>
        </AppLayout>
    );
}
