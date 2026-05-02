import type { UserRole } from '@/config/navigation';

export interface BranchSummary {
    id: number;
    code: string;
    name: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    role?: UserRole;
    branch?: BranchSummary | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    flash?: {
        success?: string;
        error?: string;
    };
};
