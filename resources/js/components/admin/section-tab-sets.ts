import type { SectionTab } from '@/components/admin/section-tabs';

type Translate = (key: string) => string;
type Localize = (path: string) => string;

/**
 * The admin sections that hold more than one page under a single menu item.
 */
export function experienceTabs(t: Translate, url: Localize): SectionTab[] {
    return [
        { title: t('Experiences'), href: url('/admin/experiences') },
        { title: t('Destinations'), href: url('/admin/destinations') },
    ];
}

export function communityTabs(t: Translate, url: Localize): SectionTab[] {
    return [
        { title: t('Posts'), href: url('/admin/community/posts') },
        { title: t('Comments'), href: url('/admin/community/comments') },
        { title: t('Reviews'), href: url('/admin/community/reviews') },
    ];
}
