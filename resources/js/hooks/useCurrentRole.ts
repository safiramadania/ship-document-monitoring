import { usePage } from '@inertiajs/react';

import { isUserRole, UserRole } from '@/config/navigation';
import { PageProps } from '@/types';

export function useCurrentRole(forcedRole?: UserRole): UserRole {
    const user = usePage<PageProps>().props.auth.user;

    if (forcedRole) {
        return forcedRole;
    }

    if (isUserRole(user.role)) {
        return user.role;
    }

    if (typeof window !== 'undefined') {
        const previewRole = new URLSearchParams(window.location.search).get(
            'role',
        );

        if (isUserRole(previewRole)) {
            return previewRole;
        }
    }

    return 'super_admin';
}
