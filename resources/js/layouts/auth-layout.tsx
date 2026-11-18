import type { ReactNode } from 'react';
import AuthBrandLayout from '@/layouts/auth/auth-brand-layout';

export default function AuthLayout({ children, title, description }: { children: ReactNode; title: string; description: string }) {
    return (
        <AuthBrandLayout title={title} description={description}>
            {children}
        </AuthBrandLayout>
    );
}
