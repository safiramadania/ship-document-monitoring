import PlaceholderPage from '@/Components/PlaceholderPage';
import { Head } from '@inertiajs/react';
import { Settings as SettingsIcon } from 'lucide-react';

export default function Settings() {
    return (
        <>
            <Head title="Settings" />
            <PlaceholderPage
                description="Placeholder pengaturan sistem untuk super admin."
                emptyDescription="Pengaturan sistem belum menjadi fokus Milestone 1."
                emptyTitle="Settings belum tersedia"
                icon={SettingsIcon}
                title="Settings"
            />
        </>
    );
}
