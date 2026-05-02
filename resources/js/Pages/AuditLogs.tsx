import PlaceholderPage from '@/Components/PlaceholderPage';
import { Head } from '@inertiajs/react';
import { History } from 'lucide-react';

export default function AuditLogs() {
    return (
        <>
            <Head title="Audit Logs" />
            <PlaceholderPage
                description="Placeholder audit trail untuk aktivitas user dan perubahan dokumen."
                emptyDescription="Audit log terstruktur akan dibuat pada Milestone 9."
                emptyTitle="Audit logs belum tersedia"
                icon={History}
                title="Audit Logs"
            />
        </>
    );
}
