import type { UserRole } from '@/config/navigation';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    role?: UserRole;
    branch?: {
        id?: number;
        code: string;
        name: string;
    };
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
