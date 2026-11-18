import { Head, Link, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Calendar, Edit, Eye, Plus, Search, Shield, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { ReactNode } from 'react';

import Heading from '@/components/heading';
import TablePagination from '@/components/table-pagination';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { useLocalizedUrl } from '@/lib/locale';
import { firstError, toast } from '@/lib/toast';
import type { BreadcrumbItem, PaginatedData } from '@/types';

type UserItem = {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string;
    role: string | null;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    can_delete: boolean;
};

type RoleOption = { value: string; label: string };

type UserFilters = {
    search?: string;
    role?: string;
    verified?: string;
};

const baseUrl = '/admin/users';
const allValue = 'all';

const roleVariant: Record<string, 'default' | 'secondary' | 'outline'> = {
    super_admin: 'default',
    admin: 'secondary',
};

function buildFilterParams(filters: UserFilters): Record<string, string> {
    const params: Record<string, string> = {};

    (Object.entries(filters) as [keyof UserFilters, string | undefined][]).forEach(([key, value]) => {
        if (value && value !== allValue) {
            params[key] = value;
        }
    });

    return params;
}

function FilterField({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div>
            <label className="mb-1.5 block text-xs font-medium text-muted-foreground">{label}</label>
            {children}
        </div>
    );
}

export default function UsersIndex({
    items,
    filters,
    roles,
}: {
    items: PaginatedData<UserItem>;
    filters: UserFilters;
    roles: RoleOption[];
}) {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const [deleteItem, setDeleteItem] = useState<UserItem | null>(null);
    const [isDeleting, setIsDeleting] = useState(false);
    const [filterValues, setFilterValues] = useState<UserFilters>({
        search: filters.search ?? '',
        role: filters.role ?? '',
        verified: filters.verified ?? '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Administration'), href: '/admin/lookups' },
        { title: t('Users'), href: baseUrl },
    ];

    function handleDelete() {
        if (!deleteItem) {
            return;
        }

        setIsDeleting(true);
        router.delete(url(`${baseUrl}/${deleteItem.id}`), {
            preserveScroll: true,
            onSuccess: () => setDeleteItem(null),
            onError: (errors) => toast.error(firstError(errors) ?? t('Something went wrong. Please try again.')),
            onFinish: () => setIsDeleting(false),
        });
    }

    function setFilterValue(key: keyof UserFilters, value: string) {
        setFilterValues((current) => ({
            ...current,
            [key]: value === allValue ? '' : value,
        }));
    }

    function applyFilters() {
        router.get(baseUrl, buildFilterParams(filterValues), {
            preserveState: true,
            preserveScroll: true,
        });
    }

    function resetFilters() {
        setFilterValues({ search: '', role: '', verified: '' });
        router.get(baseUrl, {}, { preserveState: true, preserveScroll: true });
    }

    function formatDate(dateString: string) {
        return new Date(dateString).toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }

    function formatRole(role: string | null) {
        if (!role) {
            return '-';
        }

        return role.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Users')} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="space-y-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <Heading title={t('Users')} description={t('Manage users and their roles.')} />
                        <Button asChild>
                            <Link href={`${baseUrl}/create`}>
                                <Plus className="h-4 w-4" />
                                {t('New user')}
                            </Link>
                        </Button>
                    </div>

                    <Card>
                        <CardContent className="pt-6">
                            <form
                                className="space-y-4"
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    applyFilters();
                                }}
                            >
                                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                    <FilterField label={t('Search')}>
                                        <div className="relative">
                                            <Search className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                            <Input
                                                value={filterValues.search ?? ''}
                                                onChange={(event) => setFilterValue('search', event.target.value)}
                                                placeholder={t('Search by name or email...')}
                                                className="pl-9"
                                            />
                                        </div>
                                    </FilterField>

                                    <FilterField label={t('Role')}>
                                        <Select
                                            value={filterValues.role || allValue}
                                            onValueChange={(value) => setFilterValue('role', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value={allValue}>{t('All roles')}</SelectItem>
                                                <SelectItem value="none">{t('No role')}</SelectItem>
                                                {roles.map((role) => (
                                                    <SelectItem key={role.value} value={role.value}>
                                                        {role.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </FilterField>

                                    <FilterField label={t('Verified')}>
                                        <Select
                                            value={filterValues.verified || allValue}
                                            onValueChange={(value) => setFilterValue('verified', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value={allValue}>{t('All statuses')}</SelectItem>
                                                <SelectItem value="verified">{t('Verified')}</SelectItem>
                                                <SelectItem value="unverified">{t('Unverified')}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </FilterField>
                                </div>

                                <div className="flex justify-end gap-2">
                                    <Button type="submit">
                                        <Search className="mr-1 h-4 w-4" />
                                        {t('Search')}
                                    </Button>
                                    <Button type="button" variant="outline" onClick={resetFilters}>
                                        {t('Reset')}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    <div className="space-y-4">
                        <Card>
                            <CardContent className="p-0">
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>{t('Name')}</TableHead>
                                                <TableHead>{t('Email')}</TableHead>
                                                <TableHead>{t('Role')}</TableHead>
                                                <TableHead>{t('Verified')}</TableHead>
                                                <TableHead>{t('Created')}</TableHead>
                                                <TableHead className="text-right">{t('Actions')}</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {items.data.length === 0 ? (
                                                <TableRow>
                                                    <TableCell
                                                        colSpan={6}
                                                        className="py-12 text-center text-muted-foreground"
                                                    >
                                                        {t('No users found')}
                                                    </TableCell>
                                                </TableRow>
                                            ) : (
                                                items.data.map((item) => (
                                                    <TableRow key={item.id}>
                                                        <TableCell>
                                                            <p className="font-medium">{item.full_name}</p>
                                                        </TableCell>
                                                        <TableCell>
                                                            <span className="text-sm text-muted-foreground">
                                                                {item.email}
                                                            </span>
                                                        </TableCell>
                                                        <TableCell>
                                                            {item.role ? (
                                                                <Badge variant={roleVariant[item.role] ?? 'outline'}>
                                                                    <Shield className="mr-1 h-3 w-3" />
                                                                    {formatRole(item.role)}
                                                                </Badge>
                                                            ) : (
                                                                <span className="text-xs text-muted-foreground">-</span>
                                                            )}
                                                        </TableCell>
                                                        <TableCell>
                                                            <Badge
                                                                variant={
                                                                    item.email_verified_at ? 'default' : 'secondary'
                                                                }
                                                            >
                                                                {item.email_verified_at
                                                                    ? t('Verified')
                                                                    : t('Unverified')}
                                                            </Badge>
                                                        </TableCell>
                                                        <TableCell>
                                                            <div className="flex items-center gap-1.5 text-sm text-muted-foreground">
                                                                <Calendar className="h-3.5 w-3.5" />
                                                                {formatDate(item.created_at)}
                                                            </div>
                                                        </TableCell>
                                                        <TableCell className="text-right">
                                                            <div className="flex items-center justify-end gap-1">
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    className="h-7 w-7"
                                                                    asChild
                                                                >
                                                                    <Link href={`${baseUrl}/${item.id}`} title={t('View')}>
                                                                        <Eye className="h-4 w-4" />
                                                                    </Link>
                                                                </Button>
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    className="h-7 w-7"
                                                                    asChild
                                                                >
                                                                    <Link
                                                                        href={`${baseUrl}/${item.id}/edit`}
                                                                        title={t('Edit')}
                                                                    >
                                                                        <Edit className="h-4 w-4" />
                                                                    </Link>
                                                                </Button>
                                                                {item.can_delete && (
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="icon"
                                                                        className="h-7 w-7 text-destructive hover:text-destructive"
                                                                        onClick={() => setDeleteItem(item)}
                                                                        title={t('Delete')}
                                                                    >
                                                                        <Trash2 className="h-4 w-4" />
                                                                    </Button>
                                                                )}
                                                            </div>
                                                        </TableCell>
                                                    </TableRow>
                                                ))
                                            )}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>

                        <TablePagination
                            meta={{
                                current_page: items.current_page,
                                last_page: items.last_page,
                                from: items.from,
                                to: items.to,
                                total: items.total,
                            }}
                            links={items.links}
                        />
                    </div>

                    <AlertDialog
                        open={deleteItem !== null}
                        onOpenChange={(open) => {
                            if (!open) {
                                setDeleteItem(null);
                            }
                        }}
                    >
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    {t('Delete')} "{deleteItem?.full_name}"?
                                </AlertDialogTitle>
                                <AlertDialogDescription>
                                    {t('This action cannot be undone.')}
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel disabled={isDeleting}>{t('Cancel')}</AlertDialogCancel>
                                <AlertDialogAction
                                    onClick={handleDelete}
                                    disabled={isDeleting}
                                    className="bg-destructive text-white hover:bg-destructive/90"
                                >
                                    {t('Delete')}
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>
        </AppLayout>
    );
}
