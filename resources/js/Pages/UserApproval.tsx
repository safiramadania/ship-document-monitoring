import PlaceholderPage from '@/Components/PlaceholderPage';
import { Head } from '@inertiajs/react';
import { UserCheck } from 'lucide-react';

export default function UserApproval() {
    return (
        <>
            <Head title="User Approval" />
            <PlaceholderPage
                description="Antrian approval user untuk super admin."
                emptyDescription="Status pending, approve/reject, role assignment, dan branch assignment akan dibuat pada Milestone 3."
                emptyTitle="Belum ada antrian approval"
                icon={UserCheck}
                title="User Approval"
            />
        </>
    );
}
