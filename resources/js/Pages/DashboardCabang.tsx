import AppLayout from '@/Components/AppLayout';
import UserCabangDashboard from '@/Components/Dashboards/UserCabangDashboard';
import PageHeader from '@/Components/PageHeader';
import { Head } from '@inertiajs/react';

export default function DashboardCabang() {
    return (
        <AppLayout roleOverride="user_cabang" title="Dashboard Cabang">
            <Head title="Dashboard Cabang" />
            <PageHeader
                description="Ringkasan cabang untuk kapal, dokumen yang perlu diunggah, dan dokumen yang perlu dikonfirmasi."
                title="Dashboard Cabang"
            />
            <UserCabangDashboard />
        </AppLayout>
    );
}
