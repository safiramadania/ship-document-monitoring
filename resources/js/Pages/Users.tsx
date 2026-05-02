import PlaceholderPage from '@/Components/PlaceholderPage';
import { Head } from '@inertiajs/react';
import { Users as UsersIcon } from 'lucide-react';

export default function Users() {
    return (
        <>
            <Head title="Users" />
            <PlaceholderPage
                description="Placeholder manajemen user dengan role, status, last login, dan last online."
                emptyDescription="Data user dan tracking online akan dibuat pada milestone auth dan dashboard."
                emptyTitle="User management belum terhubung"
                icon={UsersIcon}
                title="Users"
            />
        </>
    );
}
