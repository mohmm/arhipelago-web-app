import { Head, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Trash2 } from 'lucide-react';
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

type Comment = {
    id: number;
    body: string;
    author: { id: number; name: string } | null;
    post: { id: number; body: string } | null;
    created_at: string | null;
};

export default function CommunityComments({ comments, filters }: { comments: PaginatedData<Comment>; filters: { search?: string } }) {
    const { t, currentLocale } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const base = url('/admin/community/comments');

    return (
        <AppLayout breadcrumbs={[{ title: t('Community'), href: url('/admin/community/posts') }, { title: t('Comments'), href: base }]}>
            <Head title={t('Comments')} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading title={t('Posts, comments and reviews')} description={t('Check what travellers publish. A hidden post disappears from the app but stays here.')} />
                <SectionTabs tabs={communityTabs(t, url)} />
                <div className="flex">
                    <SearchFilter url={base} filters={filters} placeholder={t('Search in comments')} />
                </div>

                <Card className="overflow-hidden p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('Comment')}</TableHead>
                                <TableHead>{t('On the post')}</TableHead>
                                <TableHead>{t('Author')}</TableHead>
                                <TableHead />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {comments.data.length == 0 && (
                                <TableRow>
                                    <TableCell colSpan={4} className="py-10 text-center text-muted-foreground">
                                        {t('No comments match.')}
                                    </TableCell>
                                </TableRow>
                            )}
                            {comments.data.map((comment) => (
                                <TableRow key={comment.id}>
                                    <TableCell className="max-w-md whitespace-normal">{comment.body}</TableCell>
                                    <TableCell className="max-w-xs text-sm whitespace-normal text-muted-foreground">{comment.post?.body}</TableCell>
                                    <TableCell className="text-sm">
                                        {comment.author?.name ?? t('Deleted account')}
                                        <span className="block text-xs text-muted-foreground">{formatDateTime(comment.created_at, currentLocale() ?? 'ro')}</span>
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <ConfirmButton
                                            title={t('Delete this comment?')}
                                            description={t('It is deleted for good.')}
                                            confirmLabel={t('Delete')}
                                            onConfirm={() => router.delete(url(`/admin/community/comments/${comment.id}`), { preserveScroll: true })}
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

                <TablePagination meta={comments} links={comments.links} />
            </div>
        </AppLayout>
    );
}
