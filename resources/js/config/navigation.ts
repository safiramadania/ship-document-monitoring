import {
    BellRing,
    ClipboardCheck,
    FileArchive,
    FileSearch,
    FileText,
    Gauge,
    History,
    LucideIcon,
    Mail,
    MonitorCheck,
    Settings,
    Ship,
    UploadCloud,
    Users,
    UserCheck,
    Workflow,
} from 'lucide-react';

export type UserRole = 'super_admin' | 'admin' | 'user_cabang';

export type NavigationItem = {
    label: string;
    routeName: string;
    icon: LucideIcon;
    roles: UserRole[];
};

export const roleLabels: Record<UserRole, string> = {
    super_admin: 'Super Admin',
    admin: 'Admin Pusat',
    user_cabang: 'User Cabang',
};

const centralRoles: UserRole[] = ['super_admin', 'admin'];

export const navigationItems: NavigationItem[] = [
    {
        label: 'Dashboard',
        routeName: 'dashboard',
        icon: Gauge,
        roles: centralRoles,
    },
    {
        label: 'Dashboard Cabang',
        routeName: 'dashboard.cabang',
        icon: Gauge,
        roles: ['user_cabang'],
    },
    {
        label: 'Monitoring Kapal',
        routeName: 'monitoring.index',
        icon: MonitorCheck,
        roles: ['super_admin', 'admin', 'user_cabang'],
    },
    {
        label: 'Upload Dokumen',
        routeName: 'uploads.index',
        icon: UploadCloud,
        roles: ['super_admin', 'admin', 'user_cabang'],
    },
    {
        label: 'Smart Upload',
        routeName: 'uploads.smart',
        icon: FileSearch,
        roles: ['super_admin', 'admin', 'user_cabang'],
    },
    {
        label: 'Dokumen Saya',
        routeName: 'documents.mine',
        icon: FileArchive,
        roles: ['user_cabang'],
    },
    {
        label: 'User Approval',
        routeName: 'users.approval',
        icon: UserCheck,
        roles: ['super_admin'],
    },
    {
        label: 'Users',
        routeName: 'users.index',
        icon: Users,
        roles: ['super_admin'],
    },
    {
        label: 'Branches',
        routeName: 'branches.index',
        icon: Workflow,
        roles: ['super_admin'],
    },
    {
        label: 'Vessels',
        routeName: 'vessels.index',
        icon: Ship,
        roles: ['super_admin'],
    },
    {
        label: 'Document Types',
        routeName: 'document-types.index',
        icon: FileText,
        roles: ['super_admin'],
    },
    {
        label: 'Email Logs',
        routeName: 'email-logs.index',
        icon: Mail,
        roles: ['super_admin', 'admin'],
    },
    {
        label: 'Audit Logs',
        routeName: 'audit-logs.index',
        icon: History,
        roles: ['super_admin', 'admin'],
    },
    {
        label: 'Settings',
        routeName: 'settings.index',
        icon: Settings,
        roles: ['super_admin'],
    },
];

export function getNavigationItems(role: UserRole) {
    return navigationItems.filter((item) => item.roles.includes(role));
}

export function isUserRole(value: unknown): value is UserRole {
    return (
        value === 'super_admin' ||
        value === 'admin' ||
        value === 'user_cabang'
    );
}

export const dashboardHighlights = {
    super_admin: [
        { label: 'System Activity', icon: BellRing },
        { label: 'Approval Queue', icon: ClipboardCheck },
    ],
    admin: [
        { label: 'Recent Uploads', icon: UploadCloud },
        { label: 'Document Edits', icon: History },
    ],
    user_cabang: [
        { label: 'Need Upload', icon: UploadCloud },
        { label: 'Need Confirmation', icon: ClipboardCheck },
    ],
};
