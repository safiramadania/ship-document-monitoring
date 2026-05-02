import {
    AlertTriangle,
    CheckCircle2,
    Clock3,
    FileQuestion,
    FileWarning,
    Infinity,
    Ship,
    UploadCloud,
    Workflow,
} from 'lucide-react';

import EmptyState from '@/Components/EmptyState';
import RecentActivityList from '@/Components/RecentActivityList';
import StatCard from '@/Components/StatCard';

type RecentUpload = {
    id: number;
    vessel?: string | null;
    branch?: string | null;
    document_type?: string | null;
    uploader?: string | null;
    created_at?: string | null;
    status: string;
};

type Props = {
    data: {
        stats: Record<string, number>;
        recentUploads: RecentUpload[];
        recentDocumentEdits: Array<{
            id: number;
            timestamp?: string | null;
            user?: string | null;
            action: string;
            summary: string;
        }>;
    };
};

export default function AdminDashboard({ data }: Props) {
    const uploadActivities = data.recentUploads.map((upload) => ({
        title: upload.document_type ?? 'Dokumen kapal',
        description: `${upload.vessel ?? '-'} • ${upload.branch ?? '-'} • uploaded by ${upload.uploader ?? 'seed data'}`,
        timestamp: upload.created_at ?? '-',
        status: upload.status,
    }));

    return (
        <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <StatCard
                    helper="Cabang dalam monitoring"
                    icon={Workflow}
                    title="Total Cabang"
                    tone="cyan"
                    value={String(data.stats.totalBranches)}
                />
                <StatCard
                    helper="All-branch monitoring scope"
                    icon={Ship}
                    title="Total Kapal"
                    tone="cyan"
                    value={String(data.stats.totalVessels)}
                />
                <StatCard
                    helper="Record dokumen kapal"
                    icon={UploadCloud}
                    title="Total Documents"
                    tone="blue"
                    value={String(data.stats.totalVesselDocuments)}
                />
                <StatCard
                    helper="Confirmed active documents"
                    icon={CheckCircle2}
                    title="Active Documents"
                    tone="emerald"
                    value={String(data.stats.activeDocuments)}
                />
                <StatCard
                    helper="Due within 60 days"
                    icon={Clock3}
                    title="Expiring Soon"
                    tone="amber"
                    value={String(data.stats.expiringSoonDocuments)}
                />
                <StatCard
                    helper="Past expiry date"
                    icon={AlertTriangle}
                    title="Expired"
                    tone="rose"
                    value={String(data.stats.expiredDocuments)}
                />
                <StatCard
                    helper="Permanent certificates"
                    icon={Infinity}
                    title="Permanent"
                    tone="blue"
                    value={String(data.stats.permanentDocuments)}
                />
                <StatCard
                    helper="No expiry or unclear"
                    icon={FileQuestion}
                    title="Unknown"
                    tone="slate"
                    value={String(data.stats.unknownDocuments)}
                />
                <StatCard
                    helper="Required document absent"
                    icon={FileWarning}
                    title="Missing Documents"
                    tone="rose"
                    value={String(data.stats.missingDocuments)}
                />
                <StatCard
                    helper="Waiting branch confirmation"
                    icon={Clock3}
                    title="Need Confirmation"
                    tone="amber"
                    value={String(data.stats.documentsNeedConfirmation)}
                />
            </div>

            <div className="grid gap-6 xl:grid-cols-[1fr_0.8fr]">
                <RecentActivityList
                    items={uploadActivities}
                    title="Recent Uploads"
                />
                <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 className="text-base font-semibold text-slate-950">
                        Recent Document Edits
                    </h2>
                    {data.recentDocumentEdits.length === 0 ? (
                        <div className="mt-4">
                            <EmptyState
                                description="Perubahan dokumen akan tampil setelah modul audit mencatat document.updated atau document.confirmed."
                                title="Belum ada perubahan dokumen"
                            />
                        </div>
                    ) : (
                        <div className="mt-4 space-y-3">
                            {data.recentDocumentEdits.map((edit) => (
                                <div
                                    className="rounded-md border border-slate-100 p-3"
                                    key={edit.id}
                                >
                                    <p className="text-sm font-medium text-slate-950">
                                        {edit.action}
                                    </p>
                                    <p className="mt-1 text-sm text-slate-500">
                                        {edit.summary}
                                    </p>
                                    <p className="mt-1 text-xs text-slate-400">
                                        {edit.user ?? '-'} •{' '}
                                        {edit.timestamp ?? '-'}
                                    </p>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
