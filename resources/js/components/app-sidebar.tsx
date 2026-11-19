import { Link, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { LayoutGrid, MapPin, MessagesSquare, Route, Scale, Sparkles, Tags, Users } from 'lucide-react';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { usePermissions } from '@/hooks/use-permissions';
import { useLocalizedUrl } from '@/lib/locale';
import type { NavItem, SharedData } from '@/types';
import AppLogo from './app-logo';

/**
 * The admin panel's menu: the four areas of the app's design (content,
 * community, people, settings) and nothing else. Each item shows only to
 * staff whose role opens that section.
 */
export function AppSidebar() {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const { can } = usePermissions();
    const { assistant } = usePage<SharedData>().props;

    const overview: NavItem[] = [{ title: t('Dashboard'), href: url('/admin/dashboard'), icon: LayoutGrid, exact: true }];

    const content: NavItem[] = can('content.view')
        ? [
              { title: t('Points of interest'), href: url('/admin/points-of-interest'), icon: MapPin },
              { title: t('Experiences'), href: url('/admin/experiences'), icon: Route, match: ['/admin/destinations'] },
              { title: t('Categories and themes'), href: url('/admin/lookups'), icon: Tags },
          ]
        : [];

    const community: NavItem[] = can('community.view')
        ? [{ title: t('Posts, comments and reviews'), href: url('/admin/community/posts'), icon: MessagesSquare, match: ['/admin/community'] }]
        : [];

    const administration: NavItem[] = [
        ...(can('users.view') ? [{ title: t('Users and roles'), href: url('/admin/users'), icon: Users, match: ['/admin/roles'] }] : []),
        ...(can('settings.view')
            ? [
                  { title: assistant, href: url('/admin/maya'), icon: Sparkles },
                  { title: t('Legal documents'), href: url('/admin/legal-documents'), icon: Scale },
              ]
            : []),
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={url('/admin/dashboard')} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={overview} />
                {content.length > 0 && <NavMain items={content} label={t('Content')} />}
                {community.length > 0 && <NavMain items={community} label={t('Community')} />}
                {administration.length > 0 && <NavMain items={administration} label={t('Administration')} />}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
