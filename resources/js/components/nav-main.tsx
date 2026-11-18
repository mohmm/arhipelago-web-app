import { Link, usePage } from '@inertiajs/react';
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import type {NavItem} from '@/types';

/**
 * One labelled group of sidebar links.
 *
 * `label` is optional rather than defaulted. It used to default to the literal
 * string 'Platform', which meant the first group printed an English heading to
 * a Romanian reader — `t()` never ran on it — and there was no way to render a
 * group with no heading at all. The dashboard needs exactly that: it is one
 * item above every group and heading it would say nothing.
 */
export function NavMain({ items = [], label }: { items: NavItem[]; label?: string }) {
    const page = usePage();

    const isActive = (item: NavItem): boolean => {
        const href = typeof item.href === 'string' ? item.href : item.href.url;
        const path = page.url.split('?')[0];

        // Matched on the end rather than on equality: URLs carry a locale
        // prefix (`/ro/admin/email`), so an exact comparison would never hit.
        if (item.exact) {
            return path.endsWith(href);
        }

        return [href, ...(item.match ?? [])].some((prefix) => path.includes(prefix));
    };

    return (
        <SidebarGroup className="px-2 py-0">
            {label != null && <SidebarGroupLabel>{label}</SidebarGroupLabel>}
            <SidebarMenu>
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton asChild isActive={isActive(item)} tooltip={{ children: item.title }}>
                            <Link href={item.href} prefetch>
                                {item.icon && <item.icon />}
                                <span>{item.title}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
