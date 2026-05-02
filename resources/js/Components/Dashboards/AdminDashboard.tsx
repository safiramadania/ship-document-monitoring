import {
    AlertTriangle,
    CheckCircle2,
    Clock3,
    FileWarning,
    Ship,
    UploadCloud,
} from 'lucide-react';

import RecentActivityList from '@/Components/RecentActivityList';
import StatCard from '@/Components/StatCard';

const uploads = [
    {
        title: 'Document upload placeholder',
        description: 'Recent uploads will show branch, vessel, and uploader.',
        timestamp: '10:05',
        status: 'processing',
    },
    {
        title: 'Document edit placeholder',
        description: 'Editor identity and timestamp will appear here later.',
        timestamp: '09:22',
        status: 'need_confirmation',
    },
    {
        title: 'Email reminder placeholder',
        description: 'Reminder history will connect in Milestone 10.',
        timestamp: 'Yesterday',
        status: 'unknown',
    },
];

export default function AdminDashboard() {
    return (
        <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <StatCard
                    helper="All-branch monitoring scope"
                    icon={Ship}
                    title="Total Kapal"
                    tone="cyan"
                    value="164"
                />
                <StatCard
                    helper="Confirmed active documents"
                    icon={CheckCircle2}
                    title="Active Documents"
                    tone="emerald"
                    value="0"
                />
                <StatCard
                    helper="Due within configured threshold"
                    icon={Clock3}
                    title="Expiring Soon"
                    tone="amber"
                    value="0"
                />
                <StatCard
                    helper="Past expiry date"
                    icon={AlertTriangle}
                    title="Expired"
                    tone="rose"
                    value="0"
                />
                <StatCard
                    helper="Required document not present"
                    icon={FileWarning}
                    title="Missing Documents"
                    tone="slate"
                    value="0"
                />
            </div>

            <div className="grid gap-6 xl:grid-cols-[1fr_0.8fr]">
                <RecentActivityList items={uploads} />
                <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div className="flex items-center gap-3">
                        <div className="rounded-lg bg-cyan-50 p-2.5 text-cyan-700 ring-1 ring-cyan-100">
                            <UploadCloud className="h-5 w-5" />
                        </div>
                        <div>
                            <h2 className="text-base font-semibold text-slate-950">
                                Recent Uploads
                            </h2>
                            <p className="mt-1 text-sm text-slate-500">
                                Upload records will include branch, vessel,
                                timestamp, and uploader identity.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
