import { Head, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Star, Trash2 } from 'lucide-react';
import ConfirmButton from '@/components/admin/confirm-button';
import SearchFilter from '@/components/admin/search-filter';
import { communityTabs } from '@/components/admin/section-tab-sets';
import SectionTabs from '@/components/admin/section-tabs';
import Heading from '@/components/heading';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatDateTime } from '@/lib/format';
import { useLocalizedUrl } from '@/lib/locale';
import type { PaginatedData } from '@/types';

type Review = {
    id: number;
    rating: number;
    body: string | null;
    author: { id: number; name: string } | null;
    target: string | null;
    created_at: string | null;
};

export default function CommunityReviews({ reviews, filters }: { reviews: PaginatedData<Review>; filters: { search?: string; rating?: string } }) {
    const { t, currentLocale } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const base = url('/admin/community/reviews');

    return (
        <AppLayout breadcrumbs={[{ title: t('Community'), href: url('/admin/community/posts') }, { title: t('Reviews'), href: base }]}>
            <Head title={t('Reviews')} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading title={t('Posts, comments and reviews')} description={t('Check what travellers publish. A hidden post disappears from the app but stays here.')} />
                <SectionTabs tabs={communityTabs(t, url)} />
                <div className="flex">
                    <SearchFilter url={base} filters={filters} placeholder={t('Search in reviews')} />
                </div>

                <Card className="overflow-hidden p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Rating')}</TableHead>
                                <TableHead>{t('Review')}</TableHead>
                                <TableHead>{t('Of')}</TableHead>
                                <TableHead>{t('Author')}</TableHead>
                                <TableHead />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {reviews.data.length == 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-10 text-center text-muted-foreground">
                                        {t('No reviews match.')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {reviews.data.map((review) => (
                                <TableRow key={review.id}>
                                    <TableCell>
                                        <span className="inline-flex items-center gap-1 font-medium">
                                            <Star className="size-4 fill-star text-star" /> {review.rating}
                                        </span>
                                    </TableCell>
                                    <TableCell className="max-w-md whitespace-normal">{review.body}</TableCell>
                                    <TableCell className="text-sm">{review.target}</TableCell>
                                    <TableCell className="text-sm">
                                        {review.author?.name ?? t('Deleted account')}
                                        <span className="block text-xs text-muted-foreground">{formatDateTime(review.created_at, currentLocale() ?? 'ro')}</span>
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <ConfirmButton
                                            title={t('Delete this review?')}
                                            description={t('It is deleted for good.')}
                                            confirmLabel={t('Delete')}
                                            onConfirm={() => router.delete(url(`/admin/community/reviews/${review.id}`), { preserveScroll: true })}
                                        >
                                            <Button type="button" size="icon" variant="ghost" className="text-destructive hover:text-destructive" aria-label={t('Delete')}>
                                                <Trash2 className="size-4" />
                                            </Button>
                                        </ConfirmButton>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Card>

                <TablePagination meta={reviews} links={reviews.links} />
            </div>
        </AppLayout>
    );
}
