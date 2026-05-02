import { ClipboardCheck, History, Ship, Users, Workflow } from 'lucide-react';

import RecentActivityList from '@/Components/RecentActivityList';
import StatCard from '@/Components/StatCard';

const activities = [
    {
        title: 'User approval queue reviewed',
        description: 'Mock activity for upcoming approval workflow.',
        timestamp: '09:15',
        status: 'pending',
    },
    {
        title: 'Master data page prepared',
        description: 'Branches, vessels, and document types will connect later.',
        timestamp: '08:40',
        status: 'processing',
    },
    {
        title: 'Audit log placeholder ready',
        description: 'System events will appear after Milestone 9.',
        timestamp: 'Yesterday',
        status: 'unknown',
    },
];

export default function SuperAdminDashboard() {
    return (
        <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    helper="Mock total for base UI preview"
                    icon={Workflow}
                    title="Total Cabang"
                    tone="cyan"
                    value="27"
                />
                <StatCard
                    helper="Master data will be seeded later"
                    icon={Ship}
                    title="Total Kapal"
                    tone="blue"
                    value="164"
                />
                <StatCard
                    helper="Auth starter users only for now"
                    icon={Users}
                    title="Total Users"
                    tone="slate"
                    value="0"
                />
                <StatCard
                    helper="Approval flow starts in Milestone 3"
                    icon={ClipboardCheck}
                    title="Pending User Approval"
                    tone="amber"
                    value="0"
                />
            </div>

            <RecentActivityList items={activities} />

            <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div className="flex items-center gap-3">
                    <div className="rounded-lg bg-blue-50 p-2.5 text-blue-700 ring-1 ring-blue-100">
                        <History className="h-5 w-5" />
                    </div>
                    <div>
                        <h2 className="text-base font-semibold text-slate-950">
                            Recent System Activity
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Audit-backed system activity will be connected in a
                            later milestone.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
