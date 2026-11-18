import { Link, router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Calendar, Edit, Eye, MoreHorizontal, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useLocalizedUrl } from '@/lib/locale';
import { firstError, toast } from '@/lib/toast';
import type { LookupItem, PaginatedLookupItems } from '@/types';

interface Props {
    items: PaginatedLookupItems;
    lookupLabel: string;
    baseUrl: string;
    hasColor?: boolean;
}

export default function LookupIndex({ items, lookupLabel, baseUrl, hasColor = true }: Props) {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const [deleteItem, setDeleteItem] = useState<LookupItem | null>(null);
    const [isDeleting, setIsDeleting] = useState(false);

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

    function formatDate(dateString: string) {
        return new Date(dateString).toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 className="text-xl font-semibold tracking-tight">{lookupLabel}</h2>
                </div>
                <Button asChild>
                    <Link href={`${baseUrl}/create`}>
                        <Plus className="h-4 w-4" />
                        {t('New')}
                    </Link>
                </Button>
            </div>

            {items.data.length === 0 ? (
                <Card className="border-dashed">
                    <CardContent className="flex flex-col items-center justify-center py-12 text-center">
                        <p className="text-lg font-medium text-muted-foreground">
                            {t('No items yet')}
                        </p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {t('Get started by creating your first entry.')}
                        </p>
                        <Button asChild className="mt-4">
                            <Link href={`${baseUrl}/create`}>
                                <Plus className="h-4 w-4" />
                                {t('Create the first one')}
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            ) : (
                <div className="space-y-4">
                    <Card>
                        <CardContent className="p-0">
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>{t('Name')}</TableHead>
                                            <TableHead>{t('Slug')}</TableHead>
                                            {hasColor && <TableHead>{t('Color')}</TableHead>}
                                            <TableHead>{t('Status')}</TableHead>
                                            <TableHead>{t('Created')}</TableHead>
                                            <TableHead className="text-right">{t('Actions')}</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {items.data.map((item) => (
                                            <TableRow key={item.id}>
                                                <TableCell>
                                                    <div className="flex items-center gap-3">
                                                        {item.color && (
                                                            <div
                                                                className="h-8 w-8 shrink-0 rounded-md border"
                                                                style={{ backgroundColor: item.color }}
                                                            />
                                                        )}
                                                        <div className="min-w-0">
                                                            <p className="truncate font-medium">
                                                                {item.translated_name}
                                                            </p>
                                                            <p className="truncate text-xs text-muted-foreground">
                                                                {item.name_en} / {item.name_ro}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <code className="rounded bg-muted px-1.5 py-0.5 text-xs">
                                                        {item.slug}
                                                    </code>
                                                </TableCell>
                                                {hasColor && (
                                                    <TableCell>
                                                        {item.color ? (
                                                            <div className="flex items-center gap-2">
                                                                <div
                                                                    className="h-4 w-4 rounded-full border"
                                                                    style={{ backgroundColor: item.color }}
                                                                />
                                                                <span className="text-xs text-muted-foreground">
                                                                    {item.color}
                                                                </span>
                                                            </div>
                                                        ) : (
                                                            <span className="text-xs text-muted-foreground">-</span>
                                                        )}
                                                    </TableCell>
                                                )}
                                                <TableCell>
                                                    <Badge variant={item.is_active ? 'default' : 'secondary'}>
                                                        {item.is_active ? t('Active') : t('Inactive')}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center gap-1.5 text-sm text-muted-foreground">
                                                        <Calendar className="h-3.5 w-3.5" />
                                                        {formatDate(item.created_at)}
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" size="icon">
                                                                <MoreHorizontal className="h-4 w-4" />
                                                                <span className="sr-only">{t('Actions')}</span>
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end">
                                                            <DropdownMenuItem asChild>
                                                                <Link href={`${baseUrl}/${item.id}`}>
                                                                    <Eye className="h-4 w-4" />
                                                                    {t('View')}
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem asChild>
                                                                <Link href={`${baseUrl}/${item.id}/edit`}>
                                                                    <Edit className="h-4 w-4" />
                                                                    {t('Edit')}
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                onClick={() => setDeleteItem(item)}
                                                                className="text-destructive focus:text-destructive"
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                                {t('Delete')}
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </TableCell>
                                            </TableRow>
                                        ))}
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
            )}

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
                            {t('Delete')} "{deleteItem?.translated_name}"?
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
    );
}
