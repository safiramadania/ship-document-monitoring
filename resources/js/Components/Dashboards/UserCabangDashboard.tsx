import {
    AlertTriangle,
    ClipboardCheck,
    Clock3,
    FileQuestion,
    FileWarning,
    Infinity,
    MonitorCheck,
    Ship,
    UploadCloud,
} from 'lucide-react';

import QuickActionCard from '@/Components/QuickActionCard';
import RecentActivityList from '@/Components/RecentActivityList';
import StatCard from '@/Components/StatCard';

type Props = {
    data: {
        branch?: { id: number; code: string; name: string } | null;
        stats: Record<string, number>;
        recentUploads: Array<{
            id: number;
            vessel?: string | null;
            branch?: string | null;
            document_type?: string | null;
            uploader?: string | null;
            created_at?: string | null;
            status: string;
        }>;
    };
};

export default function UserCabangDashboard({ data }: Props) {
    const uploadActivities = data.recentUploads.map((upload) => ({
        title: upload.document_type ?? 'Dokumen kapal',
        description: `${upload.vessel ?? '-'} • uploaded by ${upload.uploader ?? 'seed data'}`,
        timestamp: upload.created_at ?? '-',
        status: upload.status,
    }));

    return (
        <div className="space-y-6">
            <div className="rounded-lg border border-cyan-200 bg-white p-5 shadow-sm">
                <p className="text-sm font-medium uppercase tracking-wide text-cyan-700">
                    Assigned Branch
                </p>
                <h2 className="mt-2 text-xl font-semibold text-slate-950">
                    {data.branch
                        ? `${data.branch.name} (${data.branch.code})`
                        : 'Belum ada cabang'}
                </h2>
                <p className="mt-2 text-sm text-slate-500">
                    Dashboard ini hanya menggunakan data kapal dan dokumen dari
                    cabang yang melekat pada user.
                </p>
            </div>

            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <StatCard
                    helper="Assigned branch only"
                    icon={Ship}
                    title="Total Kapal Cabang"
                    tone="cyan"
                    value={String(data.stats.totalVessels)}
                />
                <StatCard
                    helper="Record dokumen cabang"
                    icon={UploadCloud}
                    title="Total Documents"
                    tone="blue"
                    value={String(data.stats.totalVesselDocuments)}
                />
                <StatCard
                    helper="Required but not uploaded"
                    icon={FileWarning}
                    title="Documents Need Upload"
                    tone="rose"
                    value={String(data.stats.missingDocuments)}
                />
                <StatCard
                    helper="Waiting branch review"
                    icon={ClipboardCheck}
                    title="Need Confirmation"
                    tone="blue"
                    value={String(data.stats.documentsNeedConfirmation)}
                />
                <StatCard
                    helper="Active certificates"
                    icon={MonitorCheck}
                    title="Active"
                    tone="emerald"
                    value={String(data.stats.activeDocuments)}
                />
                <StatCard
                    helper="Due soon"
                    icon={Clock3}
                    title="Expiring Soon"
                    tone="amber"
                    value={String(data.stats.expiringSoonDocuments)}
                />
                <StatCard
                    helper="Past expiry"
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
            </div>

            <div className="grid gap-4 md:grid-cols-3">
                <QuickActionCard
                    description="Lihat checklist dokumen kapal cabang."
                    href={route('monitoring.index')}
                    icon={MonitorCheck}
                    title="Monitoring Kapal"
                />
                <QuickActionCard
                    description="Unggah dokumen dari daftar monitoring."
                    href={route('uploads.index')}
                    icon={UploadCloud}
                    title="Upload Dokumen"
                />
                <QuickActionCard
                    description="Unggah dokumen dan klasifikasi otomatis nanti."
                    href={route('uploads.smart')}
                    icon={ClipboardCheck}
                    title="Smart Upload"
                />
            </div>

            <RecentActivityList items={uploadActivities} title="Recent Uploads" />
        </div>
    );
}
