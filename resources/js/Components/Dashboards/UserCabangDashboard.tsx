import {
    AlertTriangle,
    ClipboardCheck,
    Clock3,
    FileWarning,
    MonitorCheck,
    Ship,
    UploadCloud,
} from 'lucide-react';

import QuickActionCard from '@/Components/QuickActionCard';
import StatCard from '@/Components/StatCard';

export default function UserCabangDashboard() {
    return (
        <div className="space-y-6">
            <div className="rounded-lg border border-cyan-200 bg-white p-5 shadow-sm">
                <p className="text-sm font-medium uppercase tracking-wide text-cyan-700">
                    Assigned Branch
                </p>
                <h2 className="mt-2 text-xl font-semibold text-slate-950">
                    Cabang pengguna akan tampil di sini
                </h2>
                <p className="mt-2 text-sm text-slate-500">
                    Data cabang aktual akan terhubung setelah roles, approval,
                    dan branch access dibuat.
                </p>
            </div>

            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <StatCard
                    helper="Assigned branch only"
                    icon={Ship}
                    title="Total Kapal Cabang"
                    tone="cyan"
                    value="0"
                />
                <StatCard
                    helper="Required but not uploaded"
                    icon={FileWarning}
                    title="Documents Need Upload"
                    tone="slate"
                    value="0"
                />
                <StatCard
                    helper="Waiting branch review"
                    icon={ClipboardCheck}
                    title="Documents Need Confirmation"
                    tone="blue"
                    value="0"
                />
                <StatCard
                    helper="Due soon"
                    icon={Clock3}
                    title="Expiring Soon"
                    tone="amber"
                    value="0"
                />
                <StatCard
                    helper="Past expiry"
                    icon={AlertTriangle}
                    title="Expired"
                    tone="rose"
                    value="0"
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
        </div>
    );
}
