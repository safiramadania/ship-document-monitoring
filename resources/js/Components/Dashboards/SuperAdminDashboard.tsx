import {
    ClipboardCheck,
    FileText,
    History,
    Ship,
    UserCheck,
    Users,
    Workflow,
} from 'lucide-react';

import RecentActivityList from '@/Components/RecentActivityList';
import StatCard from '@/Components/StatCard';
import StatusBadge from '@/Components/StatusBadge';

type RecentUser = {
    id: number;
    name: string;
    email: string;
    role: string;
    status: string;
    created_at?: string | null;
    branch?: { name: string; code: string } | null;
};

type Props = {
    data: {
        stats: {
            totalBranches: number;
            totalVessels: number;
            totalUsers: number;
            pendingUsers: number;
            activeUsers: number;
            totalDocumentTypes: number;
            totalVesselDocuments: number;
        };
        recentUsers: RecentUser[];
        recentApprovals: Array<{
            id: number;
            name: string;
            email: string;
            status: string;
            approved_by?: string | null;
            approved_at?: string | null;
            updated_at?: string | null;
        }>;
        recentSystemActivity: Array<{
            title: string;
            description: string;
            timestamp: string;
            status?: string;
        }>;
    };
};

export default function SuperAdminDashboard({ data }: Props) {
    return (
        <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    helper="Cabang dari master data"
                    icon={Workflow}
                    title="Total Cabang"
                    tone="cyan"
                    value={String(data.stats.totalBranches)}
                />
                <StatCard
                    helper="Kapal seluruh cabang"
                    icon={Ship}
                    title="Total Kapal"
                    tone="blue"
                    value={String(data.stats.totalVessels)}
                />
                <StatCard
                    helper={`${data.stats.activeUsers} active users`}
                    icon={Users}
                    title="Total Users"
                    tone="slate"
                    value={String(data.stats.totalUsers)}
                />
                <StatCard
                    helper="Menunggu approval"
                    icon={ClipboardCheck}
                    title="Pending Users"
                    tone="amber"
                    value={String(data.stats.pendingUsers)}
                />
                <StatCard
                    helper="User siap mengakses sistem"
                    icon={UserCheck}
                    title="Active Users"
                    tone="emerald"
                    value={String(data.stats.activeUsers)}
                />
                <StatCard
                    helper="Jenis dokumen wajib"
                    icon={FileText}
                    title="Document Types"
                    tone="blue"
                    value={String(data.stats.totalDocumentTypes)}
                />
                <StatCard
                    helper="Record dokumen kapal"
                    icon={History}
                    title="Vessel Documents"
                    tone="cyan"
                    value={String(data.stats.totalVesselDocuments)}
                />
            </div>

            <div className="grid gap-6 xl:grid-cols-2">
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-100 px-5 py-4">
                        <h2 className="text-base font-semibold text-slate-950">
                            Recent Registered Users
                        </h2>
                    </div>
                    <div className="divide-y divide-slate-100">
                        {data.recentUsers.map((user) => (
                            <div
                                className="flex items-center justify-between gap-4 px-5 py-4"
                                key={user.id}
                            >
                                <div>
                                    <p className="text-sm font-medium text-slate-950">
                                        {user.name}
                                    </p>
                                    <p className="mt-1 text-sm text-slate-500">
                                        {user.email}
                                    </p>
                                    <p className="mt-1 text-xs text-slate-400">
                                        {user.branch
                                            ? `${user.branch.name} (${user.branch.code})`
                                            : user.role}
                                    </p>
                                </div>
                                <div className="text-right">
                                    <StatusBadge status={user.status} />
                                    <p className="mt-2 text-xs text-slate-400">
                                        {user.created_at ?? '-'}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-100 px-5 py-4">
                        <h2 className="text-base font-semibold text-slate-950">
                            Recent User Approvals/Rejections
                        </h2>
                    </div>
                    <div className="divide-y divide-slate-100">
                        {data.recentApprovals.length === 0 && (
                            <div className="px-5 py-8 text-center text-sm text-slate-500">
                                Belum ada approval atau rejection terbaru.
                            </div>
                        )}
                        {data.recentApprovals.map((user) => (
                            <div
                                className="flex items-center justify-between gap-4 px-5 py-4"
                                key={user.id}
                            >
                                <div>
                                    <p className="text-sm font-medium text-slate-950">
                                        {user.name}
                                    </p>
                                    <p className="mt-1 text-sm text-slate-500">
                                        {user.email}
                                    </p>
                                    <p className="mt-1 text-xs text-slate-400">
                                        by {user.approved_by ?? '-'}
                                    </p>
                                </div>
                                <div className="text-right">
                                    <StatusBadge status={user.status} />
                                    <p className="mt-2 text-xs text-slate-400">
                                        {user.approved_at ??
                                            user.updated_at ??
                                            '-'}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            <RecentActivityList
                emptyDescription="Audit log akan menampilkan aktivitas sistem saat modul audit diperdalam."
                emptyTitle="Audit log masih kosong"
                items={data.recentSystemActivity}
                title="Recent System Activity"
            />
        </div>
    );
}
