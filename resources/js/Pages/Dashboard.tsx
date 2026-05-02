import AppLayout from '@/Components/AppLayout';
import AdminDashboard from '@/Components/Dashboards/AdminDashboard';
import SuperAdminDashboard from '@/Components/Dashboards/SuperAdminDashboard';
import UserCabangDashboard from '@/Components/Dashboards/UserCabangDashboard';
import PageHeader from '@/Components/PageHeader';
import { useCurrentRole } from '@/hooks/useCurrentRole';
import { Head } from '@inertiajs/react';

export default function Dashboard() {
    const role = useCurrentRole();
    const title = role === 'user_cabang' ? 'Dashboard Cabang' : 'Dashboard';

    return (
        <AppLayout title={title}>
            <Head title={title} />
            <PageHeader
                description="Ringkasan operasional untuk monitoring dokumen kapal ASDP."
                title={title}
            />

            {role === 'super_admin' && <SuperAdminDashboard />}
            {role === 'admin' && <AdminDashboard />}
            {role === 'user_cabang' && <UserCabangDashboard />}
        </AppLayout>
    );
}
