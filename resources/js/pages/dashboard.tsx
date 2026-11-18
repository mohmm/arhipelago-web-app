import { Head, Link } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { EyeOff, MapPin, MessageSquare, Route, Sparkles, Users } from 'lucide-react';
import type { ComponentType } from 'react';
import Heading from '@/components/heading';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { formatDateTime } from '@/lib/format';
import { useLocalizedUrl } from '@/lib/locale';

type Stats = {
    pointsOfInterest: { published: number; total: number };
    experiences: { published: number; total: number };
    posts: { thisWeek: number; hidden: number };
    travellers: { total: number; thisWeek: number };
    mayaConversationsThisWeek: number;
};

type RecentPost = {
    id: number;
    body: string;
    imageUrl: string | null;
    author: string | null;
    pointOfInterest: string | null;
    isApproved: boolean;
    createdAt: string | null;
};

function StatCard({ icon: Icon, label, value, detail, href }: { icon: ComponentType<{ className?: string }>; label: string; value: number; detail: string; href?: string }) {
    const body = (
        <Card className="h-full transition-shadow hover:shadow-md">
            <CardContent className="flex items-start gap-4 p-5">
                <span className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-accent text-accent-foreground">
                    <Icon className="size-5" />
                </span>
                <div className="min-w-0">
                    <p className="text-sm text-muted-foreground">{label}</p>
                    <p className="text-2xl font-bold tracking-tight">{value}</p>
                    <p className="text-xs text-muted-foreground">{detail}</p>
                </div>
            </CardContent>
        </Card>
    );

    return href ? (
        <Link href={href} prefetch>
            {body}
        </Link>
    ) : (
        body
    );
}

/**
 * The admin panel's first page: what is live in the app and what the
 * community did this week.
 */
export default function Dashboard({ stats, recentPosts }: { stats: Stats; recentPosts: RecentPost[] }) {
    const { t, currentLocale } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const locale = currentLocale() ?? 'ro';

    return (
        <AppLayout breadcrumbs={[{ title: t('Dashboard'), href: url('/admin/dashboard') }]}>
            <Head title={t('Dashboard')} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading title={t('Dashboard')} description={t('What is live in the app, and what travellers did this week.')} />

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    <StatCard
                        icon={MapPin}
                        label={t('Points of interest')}
                        value={stats.pointsOfInterest.published}
                        detail={t(':total in total', { total: String(stats.pointsOfInterest.total) })}
                        href={url('/admin/points-of-interest')}
                    />
                    <StatCard
                        icon={Route}
                        label={t('Experiences')}
                        value={stats.experiences.published}
                        detail={t(':total in total', { total: String(stats.experiences.total) })}
                        href={url('/admin/experiences')}
                    />
                    <StatCard
                        icon={MessageSquare}
                        label={t('Posts this week')}
                        value={stats.posts.thisWeek}
                        detail={t(':count hidden', { count: String(stats.posts.hidden) })}
                        href={url('/admin/community/posts')}
                    />
                    <StatCard
                        icon={Users}
                        label={t('Travellers')}
                        value={stats.travellers.total}
                        detail={t(':count new this week', { count: String(stats.travellers.thisWeek) })}
                        href={url('/admin/users')}
                    />
                    <StatCard icon={Sparkles} label={t('Conversations with Maya')} value={stats.mayaConversationsThisWeek} detail={t('this week')} />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('Latest posts')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recentPosts.length == 0 ? (
                            <p className="text-sm text-muted-foreground">{t('No posts yet.')}</p>
                        ) : (
                            <ul className="divide-y">
                                {recentPosts.map((post) => (
                                    <li key={post.id} className="flex items-center gap-4 py-3">
                                        {post.imageUrl ? (
                                            <img src={post.imageUrl} alt="" className="size-14 shrink-0 rounded-lg object-cover" />
                                        ) : (
                                            <span className="flex size-14 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                                                <MessageSquare className="size-5" />
                                            </span>
                                        )}
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate text-sm">{post.body}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {post.author}
                                                {post.pointOfInterest && ` · ${post.pointOfInterest}`} · {formatDateTime(post.createdAt, locale)}
                                            </p>
                                        </div>
                                        {!post.isApproved && (
                                            <span className="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground">
                                                <EyeOff className="size-3" />
                                                {t('Hidden')}
                                            </span>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
