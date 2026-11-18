import { Head, Link, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ImageOff, Plus, Search } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import StatusBadge from '@/components/admin/status-badge';
import Heading from '@/components/heading';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { useLocalizedUrl } from '@/lib/locale';
import type { PaginatedData } from '@/types';

type Place = {
    id: number;
    name: string;
    type: string | null;
    locality: string | null;
    category: string | null;
    thumb_url: string | null;
    experiences_count: number;
    likes_count: number;
    is_published: boolean;
    missing_languages: string[];
};

type Option = { value: number; label: string };

type Props = {
    places: PaginatedData<Place>;
    filters: { search?: string; category?: string; status?: string };
    categories: Option[];
};

const ALL = 'all';

export default function PointsOfInterestIndex({ places, filters, categories }: Props) {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const base = url('/admin/points-of-interest');
    const [search, setSearch] = useState(filters.search ?? '');

    const filter = (changes: Record<string, string | undefined>) => {
        const next = { ...filters, search, ...changes };
        router.get(base, Object.fromEntries(Object.entries(next).filter(([, value]) => value != null && value != '' && value != ALL)), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={[{ title: t('Points of interest'), href: base }]}>
            <Head title={t('Points of interest')} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading title={t('Points of interest')} description={t('Castles, fortresses, museums and nature, with photos and their place on the map.')} />
                    <Button asChild>
                        <Link href={`${base}/create`}>
                            <Plus className="size-4" />
                            {t('New point of interest')}
                        </Link>
                    </Button>
                </div>

                <div className="flex flex-wrap gap-3">
                    <form
                        className="relative min-w-64 flex-1"
                        onSubmit={(event: FormEvent) => {
                            event.preventDefault();
                            filter({});
                        }}
                    >
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input value={search} onChange={(event) => setSearch(event.target.value)} placeholder={t('Search a point of interest')} className="pl-9" />
                    </form>
                    <Select value={filters.category ?? ALL} onValueChange={(value) => filter({ category: value })}>
                        <SelectTrigger className="w-56">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>{t('All categories')}</SelectItem>
                            {categories.map((category) => (
                                <SelectItem key={category.value} value={String(category.value)}>
                                    {category.label}
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
                                <TableHead>{t('Point of interest')}</TableHead>
                                <TableHead>{t('Category')}</TableHead>
                                <TableHead className="text-right">{t('Experiences')}</TableHead>
                                <TableHead className="text-right">{t('Likes')}</TableHead>
                                <TableHead>{t('Status')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {places.data.length == 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-10 text-center text-muted-foreground">
                                        {t('No points of interest match.')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {places.data.map((place) => (
                                <TableRow key={place.id} className="cursor-pointer" onClick={() => router.visit(`${base}/${place.id}/edit`)}>
                                    <TableCell>
                                        <div className="flex items-center gap-3">
                                            {place.thumb_url ? (
                                                <img src={place.thumb_url} alt="" className="size-12 rounded-lg object-cover" />
                                            ) : (
                                                <span className="flex size-12 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                                                    <ImageOff className="size-4" />
                                                </span>
                                            )}
                                            <div>
                                                <p className="font-medium">{place.name}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    {[place.type, place.locality].filter(Boolean).join(' · ')}
                                                    {place.missing_languages.length > 0 && (
                                                        <span className="ml-2 rounded bg-heritage px-1.5 py-0.5 text-heritage-foreground">
                                                            {t('Missing: :languages', { languages: place.missing_languages.join(', ').toUpperCase() })}
                                                        </span>
                                                    )}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>{place.category}</TableCell>
                                    <TableCell className="text-right">{place.experiences_count}</TableCell>
                                    <TableCell className="text-right">{place.likes_count}</TableCell>
                                    <TableCell>
                                        <StatusBadge published={place.is_published} />
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Card>

                <TablePagination meta={places} links={places.links} />
            </div>
        </AppLayout>
    );
}
