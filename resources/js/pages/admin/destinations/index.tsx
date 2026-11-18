import { Head, Link, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Plus } from 'lucide-react';
import { experienceTabs } from '@/components/admin/section-tab-sets';
import SectionTabs from '@/components/admin/section-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { useLocalizedUrl } from '@/lib/locale';

type Destination = { id: number; name: string; region: string; experiences_count: number; sort_order: number };

export default function DestinationsIndex({ destinations }: { destinations: Destination[] }) {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const base = url('/admin/destinations');

    return (
        <AppLayout breadcrumbs={[{ title: t('Experiences'), href: url('/admin/experiences') }, { title: t('Destinations'), href: base }]}>
            <Head title={t('Destinations')} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading title={t('Destinations')} description={t('The areas experiences are set in. Each offers at most one experience of every kind.')} />
                    <Button asChild>
                        <Link href={`${base}/create`}>
                            <Plus className="size-4" />
                            {t('New destination')}
                        </Link>
                    </Button>
                </div>

                <SectionTabs tabs={experienceTabs(t, url)} />

                <Card className="overflow-hidden p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Destination')}</TableHead>
                                <TableHead>{t('Region')}</TableHead>
                                <TableHead className="text-right">{t('Experiences')}</TableHead>
                                <TableHead className="text-right">{t('Order')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {destinations.length == 0 && (
                                <TableRow>
                                    <TableCell colSpan={4} className="py-10 text-center text-muted-foreground">
                                        {t('No destinations yet.')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {destinations.map((destination) => (
                                <TableRow key={destination.id} className="cursor-pointer" onClick={() => router.visit(`${base}/${destination.id}/edit`)}>
                                    <TableCell className="font-medium">{destination.name}</TableCell>
                                    <TableCell>{destination.region}</TableCell>
                                    <TableCell className="text-right">{destination.experiences_count}</TableCell>
                                    <TableCell className="text-right">{destination.sort_order}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Card>
            </div>
        </AppLayout>
    );
}
