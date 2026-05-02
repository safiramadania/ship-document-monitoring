import PlaceholderPage from '@/Components/PlaceholderPage';
import { Head } from '@inertiajs/react';
import { Mail } from 'lucide-react';

export default function EmailLogs() {
    return (
        <>
            <Head title="Email Logs" />
            <PlaceholderPage
                description="Placeholder riwayat reminder email dokumen expired dan expiring soon."
                emptyDescription="Scheduler reminder, deduplication, dan log email akan dibuat pada Milestone 10."
                emptyTitle="Email logs belum tersedia"
                icon={Mail}
                title="Email Logs"
            />
        </>
    );
}
