import { Head, router, useForm } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { KeyRound, Lock, Plus, Shield, Trash2, Users } from 'lucide-react';
import { useState } from 'react';

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
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { usePermissions } from '@/hooks/use-permissions';
import AppLayout from '@/layouts/app-layout';
import { useLocalizedUrl } from '@/lib/locale';
import { firstError, toast } from '@/lib/toast';
import type { BreadcrumbItem } from '@/types';

import { groupTitle, roleTitle } from './labels';
import type { PermissionGroups } from './labels';
import PermissionPicker from './permission-picker';

const baseUrl = '/admin/roles';
const VISIBLE_CHIPS = 8;

interface Role {
    id: number;
    name: string;
    users_count: number;
    permissions: string[];
    is_protected: boolean;
    can_delete: boolean;
}

interface RolesIndexProps {
    roles: Role[];
    permissionGroups: PermissionGroups;
}

/**
 * Server-side failures on the permissions field arrive keyed `permissions` or
 * `permissions.<index>`; without this the dialog would only show a generic toast
 * and never say which permission was rejected.
 */
function PermissionErrors({ errors }: { errors: Record<string, string> }) {
    const messages = Object.entries(errors)
        .filter(([key]) => key === 'permissions' || key.startsWith('permissions.'))
        .map(([, message]) => message);

    if (messages.length === 0) {
        return null;
    }

    return (
        <div className="space-y-1">
            {messages.map((message) => (
                <p key={message} className="text-sm text-destructive">
                    {message}
                </p>
            ))}
        </div>
    );
}

export default function RolesIndex({ roles, permissionGroups }: RolesIndexProps) {
    const { t, tChoice } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const { can } = usePermissions();

    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [editRole, setEditRole] = useState<Role | null>(null);
    const [deleteRole, setDeleteRole] = useState<Role | null>(null);
    const [isDeleting, setIsDeleting] = useState(false);

    const createForm = useForm<{ name: string; permissions: string[] }>({ name: '', permissions: [] });
    const editForm = useForm<{ permissions: string[] }>({ permissions: [] });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Administration'), href: baseUrl },
        { title: t('Roles'), href: baseUrl },
    ];

    function openCreate() {
        createForm.reset();
        createForm.clearErrors();
        setIsCreateOpen(true);
    }

    function openEdit(role: Role) {
        editForm.setData('permissions', role.permissions);
        editForm.clearErrors();
        setEditRole(role);
    }

    function submitCreate(event: React.FormEvent) {
        event.preventDefault();

        createForm.post(url(baseUrl), {
            preserveScroll: true,
            onSuccess: () => {
                setIsCreateOpen(false);
                createForm.reset();
            },
        });
    }

    function submitEdit(event: React.FormEvent) {
        event.preventDefault();

        if (!editRole) {
            return;
        }

        editForm.patch(url(`${baseUrl}/${editRole.id}`), {
            preserveScroll: true,
            onSuccess: () => setEditRole(null),
        });
    }

    function handleDelete() {
        if (!deleteRole) {
            return;
        }

        setIsDeleting(true);
        router.delete(url(`${baseUrl}/${deleteRole.id}`), {
            preserveScroll: true,
            onSuccess: () => setDeleteRole(null),
            onError: (errors) => toast.error(firstError(errors) ?? t('Something went wrong. Please try again.')),
            onFinish: () => setIsDeleting(false),
        });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Roles')} />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-xl font-semibold tracking-tight">{t('Manage Roles')}</h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {t('View and manage role permissions')}
                        </p>
                    </div>
                    {can('role.create') && (
                        <Button onClick={openCreate}>
                            <Plus className="h-4 w-4" />
                            {t('Create Role')}
                        </Button>
                    )}
                </div>

                <div className="space-y-4">
                    {roles.map((role) => (
                        <Card key={role.id}>
                            <CardContent className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div className="min-w-0 space-y-3">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <span
                                            className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-md ${
                                                role.is_protected
                                                    ? 'bg-destructive/10 text-destructive'
                                                    : 'bg-primary/10 text-primary'
                                            }`}
                                        >
                                            <Shield className="h-4 w-4" />
                                        </span>
                                        <span className="font-semibold">{roleTitle(role.name, t)}</span>
                                        {role.is_protected && (
                                            <Badge variant="secondary" className="gap-1">
                                                <Lock className="h-3 w-3" />
                                                {t('Protected')}
                                            </Badge>
                                        )}
                                        <span className="flex items-center gap-1.5 text-xs text-muted-foreground">
                                            <Users className="h-3.5 w-3.5" />
                                            {tChoice(':count user|:count users', role.users_count)}
                                        </span>
                                    </div>

                                    <div className="flex flex-wrap gap-1.5">
                                        {role.permissions.slice(0, VISIBLE_CHIPS).map((permission) => (
                                            <Badge key={permission} variant="outline" className="font-mono text-xs font-normal">
                                                {permission}
                                            </Badge>
                                        ))}
                                        {role.permissions.length > VISIBLE_CHIPS && (
                                            <Badge variant="secondary" className="text-xs">
                                                +{role.permissions.length - VISIBLE_CHIPS} {t('more')}
                                            </Badge>
                                        )}
                                        {role.permissions.length === 0 && (
                                            <span className="text-xs text-muted-foreground">
                                                {t('No permissions assigned.')}
                                            </span>
                                        )}
                                    </div>
                                </div>

                                <div className="flex shrink-0 items-center gap-2">
                                    {role.is_protected ? (
                                        <span className="text-xs text-muted-foreground">
                                            {t('Always holds every permission.')}
                                        </span>
                                    ) : (
                                        can('role.update') && (
                                            <Button variant="outline" onClick={() => openEdit(role)}>
                                                <KeyRound className="h-4 w-4" />
                                                {t('Edit Permissions')}
                                            </Button>
                                        )
                                    )}
                                    {role.can_delete && can('role.delete') && (
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="text-destructive hover:text-destructive"
                                            onClick={() => setDeleteRole(role)}
                                        >
                                            <Trash2 className="h-4 w-4" />
                                            <span className="sr-only">{t('Delete')}</span>
                                        </Button>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <div className="space-y-4">
                    <div>
                        <h2 className="text-xl font-semibold tracking-tight">{t('Available Permissions')}</h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {t('Generated from the application policies. Run "php artisan permissions:sync" after adding a policy.')}
                        </p>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {Object.entries(permissionGroups).map(([group, names]) => (
                            <Card key={group}>
                                <CardHeader>
                                    <CardTitle className="text-base">{groupTitle(group, t)}</CardTitle>
                                </CardHeader>
                                <CardContent className="flex flex-wrap gap-1.5">
                                    {names.map((name) => (
                                        <Badge key={name} variant="outline" className="font-mono text-xs font-normal">
                                            {name}
                                        </Badge>
                                    ))}
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </div>
            </div>

            <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                <DialogContent className="flex max-h-[85vh] flex-col sm:max-w-3xl">
                    <DialogHeader>
                        <DialogTitle>{t('Create Role')}</DialogTitle>
                        <DialogDescription>
                            {t('Choose a name and the permissions this role grants.')}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={submitCreate} className="flex min-h-0 flex-1 flex-col gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="role-name">{t('Name')}</Label>
                            <Input
                                id="role-name"
                                value={createForm.data.name}
                                onChange={(event) => createForm.setData('name', event.target.value)}
                                placeholder="hotel_manager"
                                className="font-mono"
                                autoFocus
                            />
                            <p className="text-xs text-muted-foreground">
                                {t('Lowercase letters, numbers and underscores only.')}
                            </p>
                            {createForm.errors.name && (
                                <p className="text-sm text-destructive">{createForm.errors.name}</p>
                            )}
                        </div>

                        <PermissionPicker
                            groups={permissionGroups}
                            selected={createForm.data.permissions}
                            onChange={(next) => createForm.setData('permissions', next)}
                            disabled={createForm.processing}
                        />

                        <PermissionErrors errors={createForm.errors} />

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setIsCreateOpen(false)}
                                disabled={createForm.processing}
                            >
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={createForm.processing}>
                                {t('Create Role')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={editRole !== null} onOpenChange={(open) => !open && setEditRole(null)}>
                <DialogContent className="flex max-h-[85vh] flex-col sm:max-w-3xl">
                    <DialogHeader>
                        <DialogTitle>
                            {t('Edit Permissions')} — {editRole ? roleTitle(editRole.name, t) : ''}
                        </DialogTitle>
                        <DialogDescription>
                            {t('Select every permission this role should grant.')}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={submitEdit} className="flex min-h-0 flex-1 flex-col gap-4">
                        <PermissionPicker
                            groups={permissionGroups}
                            selected={editForm.data.permissions}
                            onChange={(next) => editForm.setData('permissions', next)}
                            disabled={editForm.processing}
                        />

                        <PermissionErrors errors={editForm.errors} />

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setEditRole(null)}
                                disabled={editForm.processing}
                            >
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={editForm.processing}>
                                {t('Save')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <AlertDialog open={deleteRole !== null} onOpenChange={(open) => !open && setDeleteRole(null)}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {t('Delete')} "{deleteRole ? roleTitle(deleteRole.name, t) : ''}"?
                        </AlertDialogTitle>
                        <AlertDialogDescription>{t('This action cannot be undone.')}</AlertDialogDescription>
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
        </AppLayout>
    );
}
