import { usePage } from '@inertiajs/react';
import type { Auth } from '@/types';

type SharedProps = {
    auth: Auth;
};

export function usePermissions() {
    const { auth } = usePage<SharedProps>().props;
    const role = auth.role;
    const permissions = auth.permissions ?? [];

    const can = (permission: string): boolean => {
        return permissions.includes(permission);
    };

    const canAny = (perms: string[]): boolean => {
        return perms.some((p) => permissions.includes(p));
    };

    const hasRole = (r: string): boolean => {
        return role === r;
    };

    const hasAnyRole = (roles: string[]): boolean => {
        return role != null && roles.includes(role);
    };

    const isAdmin = (): boolean => {
        return hasAnyRole(['super_admin', 'admin']);
    };

    const isSuperAdmin = (): boolean => {
        return hasRole('super_admin');
    };

    return { role, permissions, can, canAny, hasRole, hasAnyRole, isAdmin, isSuperAdmin };
}
