import { LucideIcon } from 'lucide-react';
import { ReactNode } from 'react';

import AppLayout from '@/Components/AppLayout';
import EmptyState from '@/Components/EmptyState';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';

type PlaceholderPageProps = {
    title: string;
    description: string;
    emptyTitle: string;
    emptyDescription: string;
    icon?: LucideIcon;
    children?: ReactNode;
};

export default function PlaceholderPage({
    title,
    description,
    emptyTitle,
    emptyDescription,
    icon,
    children,
}: PlaceholderPageProps) {
    return (
        <AppLayout title={title}>
            <PageHeader
                actions={<StatusBadge label="Milestone 1" status="processing" />}
                description={description}
                title={title}
            />
            {children}
            <EmptyState
                description={emptyDescription}
                icon={icon}
                title={emptyTitle}
            />
        </AppLayout>
    );
}
