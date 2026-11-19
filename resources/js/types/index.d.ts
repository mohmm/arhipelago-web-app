import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
    role: string | null;
    permissions: string[];
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
    /**
     * Highlight only on this exact path. Needed by a section landing page
     * whose URL is a prefix of its own children — without it, "Overview"
     * stays lit while you are three screens deep inside the section.
     */
    exact?: boolean;
    /** Other paths that belong to this item, such as the tabs of its section. */
    match?: string[];
}

/** The company behind the product, from `config/company.php`. */
export interface Company {
    legal_name: string;
    trade_register: string;
    cui: string;
    vat_id: string;
    /** Empty until filled in, and then not printed. */
    share_capital: string;
    email: string;
    support_email: string;
    phone: string;
    address: string;
    country: string;
    website: string;
}

export interface SharedData {
    name: string;
    assistant: string;
    quote: { message: string; author: string };
    auth: Auth;
    flash: { success: string | null; error: string | null };
    mapTiles: { url: string; attribution: string };
    company: Company;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface PageProps {
    [key: string]: unknown;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
}

export interface LookupItem {
    id: number;
    slug: string;
    name_en: string;
    name_ro: string;
    name_ar: string | null;
    translated_name: string;
    icon: string | null;
    color: string | null;
    image_url: string | null;
    is_active: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
}

export type PaginatedLookupItems = PaginatedData<LookupItem>;

export interface LookupType {
    name: string;
    slug: string;
    href: string;
    count: number;
    description: string;
    icon: string;
}
