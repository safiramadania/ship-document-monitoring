import { usePage } from '@inertiajs/react';

import { isUserRole, UserRole } from '@/config/navigation';
import { PageProps } from '@/types';

export function useCurrentRole(): UserRole {
    const user = usePage<PageProps>().props.auth.user;

    if (isUserRole(user.role)) {
        return user.role;
    }

    return 'super_admin';
}
