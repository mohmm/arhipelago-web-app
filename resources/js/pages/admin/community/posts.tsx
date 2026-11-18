import { Head, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Eye, EyeOff, Heart, MessageCircle, Trash2 } from 'lucide-react';
import ConfirmButton from '@/components/admin/confirm-button';
import SearchFilter from '@/components/admin/search-filter';
import { communityTabs } from '@/components/admin/section-tab-sets';
import SectionTabs from '@/components/admin/section-tabs';
import StatusBadge from '@/components/admin/status-badge';
import Heading from '@/components/heading';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { formatDateTime } from '@/lib/format';
import { useLocalizedUrl } from '@/lib/locale';
import type { PaginatedData } from '@/types';

type Post = {
    id: number;
    body: string;
    image_url: string | null;
    author: { id: number; name: string; email: string } | null;
    place: string | null;
    likes_count: number;
    comments_count: number;
    is_approved: boolean;
    created_at: string | null;
};

const ALL = 'all';

export default function CommunityPosts({ posts, filters }: { posts: PaginatedData<Post>; filters: { search?: string; status?: string } }) {
    const { t, currentLocale } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const base = url('/admin/community/posts');

    return (
        <AppLayout breadcrumbs={[{ title: t('Community'), href: base }, { title: t('Posts'), href: base }]}>
            <Head title={t('Posts')} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading title={t('Posts, comments and reviews')} description={t('Check what travellers publish. A hidden post disappears from the app but stays here.')} />
                <SectionTabs tabs={communityTabs(t, url)} />

                <div className="flex flex-wrap gap-3">
                    <SearchFilter url={base} filters={filters} placeholder={t('Search in posts')} />
                    <Select
                        value={filters.status ?? ALL}
                        onValueChange={(status) =>
                            router.get(base, Object.fromEntries(Object.entries({ ...filters, status }).filter(([, value]) => value && value != ALL)), { preserveState: true })
                        }
                    >
                        <SelectTrigger className="w-44">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>{t('All posts')}</SelectItem>
                            <SelectItem value="visible">{t('Visible')}</SelectItem>
                            <SelectItem value="hidden">{t('Hidden')}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                {posts.data.length == 0 && <p className="py-10 text-center text-muted-foreground">{t('No posts match.')}</p>}

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {posts.data.map((post) => (
                        <Card key={post.id} className="overflow-hidden p-0">
                            {post.image_url && <img src={post.image_url} alt="" className="aspect-[4/3] w-full object-cover" />}
                            <CardContent className="grid gap-3 p-4">
                                <div className="flex items-start justify-between gap-3">
                                    <div className="text-sm">
                                        <p className="font-medium">{post.author?.name ?? t('Deleted account')}</p>
                                        <p className="text-xs text-muted-foreground">{formatDateTime(post.created_at, currentLocale() ?? 'ro')}</p>
                                    </div>
                                    <StatusBadge published={post.is_approved} publishedLabel={t('Visible')} draftLabel={t('Hidden')} />
                                </div>
                                {post.place && <p className="w-fit rounded-full bg-accent px-2.5 py-0.5 text-xs font-medium text-accent-foreground">{post.place}</p>}
                                <p className="text-sm whitespace-pre-line">{post.body}</p>
                                <p className="flex items-center gap-4 text-xs text-muted-foreground">
                                    <span className="inline-flex items-center gap-1">
                                        <Heart className="size-3.5" /> {post.likes_count}
                                    </span>
                                    <span className="inline-flex items-center gap-1">
                                        <MessageCircle className="size-3.5" /> {post.comments_count}
                                    </span>
                                </p>
                                <div className="flex gap-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        onClick={() => router.patch(url(`/admin/community/posts/${post.id}`), { is_approved: !post.is_approved }, { preserveScroll: true })}
                                    >
                                        {post.is_approved ? <EyeOff className="size-4" /> : <Eye className="size-4" />}
                                        {post.is_approved ? t('Hide') : t('Show again')}
                                    </Button>
                                    <ConfirmButton
                                        title={t('Delete this post?')}
                                        description={t('The post, its photo, comments and likes are deleted for good.')}
                                        confirmLabel={t('Delete')}
                                        onConfirm={() => router.delete(url(`/admin/community/posts/${post.id}`), { preserveScroll: true })}
                                    >
                                        <Button type="button" size="sm" variant="ghost" className="text-destructive hover:text-destructive">
                                            <Trash2 className="size-4" />
                                            {t('Delete')}
                                        </Button>
                                    </ConfirmButton>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <TablePagination meta={posts} links={posts.links} />
            </div>
        </AppLayout>
    );
}
