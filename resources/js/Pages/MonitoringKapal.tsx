import AppLayout from '@/Components/AppLayout';
import DocumentDownloadButton from '@/Components/DocumentDownloadButton';
import DocumentPreviewButton from '@/Components/DocumentPreviewButton';
import PageHeader from '@/Components/PageHeader';
import StatCard from '@/Components/StatCard';
import StatusBadge from '@/Components/StatusBadge';
import { Button } from '@/Components/ui/button';
import { BranchSummary } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    AlertTriangle,
    CheckCircle2,
    Clock3,
    FileWarning,
    Infinity,
    Ship,
    UploadCloud,
} from 'lucide-react';

type VesselSummary = {
    id: number;
    branch_id: number;
    code?: string | null;
    name: string;
};

type MonitoringRow = {
    no: number;
    vessel_id: number;
    document_type: {
        id: number;
        code: string;
        name: string;
    };
    document?: {
        id: number;
        letter_number?: string | null;
        issued_at?: string | null;
        expires_at?: string | null;
        issuer?: string | null;
        is_permanent: boolean;
        status: string;
        processing_status?: string | null;
        has_file: boolean;
        external_link?: string | null;
        preview_url?: string | null;
        download_url?: string | null;
    } | null;
    status: string;
    upload_url: string;
};

type Props = {
    branches: BranchSummary[];
    vessels: VesselSummary[];
    selectedBranchId?: number | null;
    selectedVesselId?: number | null;
    selectedBranch?: BranchSummary | null;
    selectedVessel?: VesselSummary | null;
    branchLocked: boolean;
    summary: {
        totalRequiredDocuments: number;
        documentsWithRecords: number;
        missingDocuments: number;
        expiredDocuments: number;
        expiringSoonDocuments: number;
        permanentDocuments: number;
        completionPercentage: number;
    };
    rows: MonitoringRow[];
};

export default function MonitoringKapal({
    branches,
    vessels,
    selectedBranchId,
    selectedVesselId,
    selectedBranch,
    selectedVessel,
    branchLocked,
    summary,
    rows,
}: Props) {
    const updateFilters = (params: Record<string, string | number | null>) => {
        router.get(route('monitoring.index'), params, {
            preserveScroll: true,
            preserveState: false,
        });
    };

    return (
        <AppLayout title="Monitoring Kapal">
            <Head title="Monitoring Kapal" />
            <PageHeader
                actions={
                    <Link href={route('uploads.smart')}>
                        <Button type="button">
                            <UploadCloud className="h-4 w-4" />
                            Smart Upload
                        </Button>
                    </Link>
                }
                description="Checklist dokumen wajib per kapal dengan status kelengkapan dan masa berlaku."
                title="Monitoring Kapal"
            />

            <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <label className="text-sm font-medium text-slate-700">
                            Branch
                        </label>
                        <select
                            className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 disabled:bg-slate-100"
                            disabled={branchLocked}
                            onChange={(event) =>
                                updateFilters({
                                    branch_id: event.target.value,
                                    vessel_id: null,
                                })
                            }
                            value={selectedBranchId ?? ''}
                        >
                            {branches.map((branch) => (
                                <option key={branch.id} value={branch.id}>
                                    {branch.name} ({branch.code})
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="text-sm font-medium text-slate-700">
                            Vessel
                        </label>
                        <select
                            className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
                            onChange={(event) =>
                                updateFilters({
                                    branch_id: selectedBranchId ?? '',
                                    vessel_id: event.target.value,
                                })
                            }
                            value={selectedVesselId ?? ''}
                        >
                            {vessels.map((vessel) => (
                                <option key={vessel.id} value={vessel.id}>
                                    {vessel.name}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                <div className="mt-4 rounded-md bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    {selectedVessel ? (
                        <>
                            Monitoring{' '}
                            <span className="font-semibold text-slate-900">
                                {selectedVessel.name}
                            </span>{' '}
                            di cabang{' '}
                            <span className="font-semibold text-slate-900">
                                {selectedBranch?.name ?? '-'}
                            </span>
                            .
                        </>
                    ) : (
                        'Belum ada kapal untuk filter yang dipilih.'
                    )}
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    helper="Jenis dokumen wajib"
                    icon={Ship}
                    title="Required Documents"
                    tone="cyan"
                    value={String(summary.totalRequiredDocuments)}
                />
                <StatCard
                    helper="Memiliki record dokumen"
                    icon={CheckCircle2}
                    title="Documents With Records"
                    tone="emerald"
                    value={String(summary.documentsWithRecords)}
                />
                <StatCard
                    helper="Belum ada record"
                    icon={FileWarning}
                    title="Missing Documents"
                    tone="rose"
                    value={String(summary.missingDocuments)}
                />
                <StatCard
                    helper="Persentase kelengkapan"
                    icon={CheckCircle2}
                    title="Completion"
                    tone="blue"
                    value={`${summary.completionPercentage}%`}
                />
                <StatCard
                    helper="Lewat masa berlaku"
                    icon={AlertTriangle}
                    title="Expired"
                    tone="rose"
                    value={String(summary.expiredDocuments)}
                />
                <StatCard
                    helper="Jatuh tempo 60 hari"
                    icon={Clock3}
                    title="Expiring Soon"
                    tone="amber"
                    value={String(summary.expiringSoonDocuments)}
                />
                <StatCard
                    helper="Tidak memiliki expiry"
                    icon={Infinity}
                    title="Permanent"
                    tone="blue"
                    value={String(summary.permanentDocuments)}
                />
            </div>

            <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h2 className="text-base font-semibold text-slate-950">
                            Compliance Progress
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            {summary.documentsWithRecords} dari{' '}
                            {summary.totalRequiredDocuments} dokumen wajib sudah
                            memiliki record.
                        </p>
                    </div>
                    <span className="text-xl font-semibold text-cyan-700">
                        {summary.completionPercentage}%
                    </span>
                </div>
                <div className="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
                    <div
                        className="h-full rounded-full bg-cyan-500"
                        style={{
                            width: `${Math.min(summary.completionPercentage, 100)}%`,
                        }}
                    />
                </div>
            </div>

            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th className="px-4 py-3">No</th>
                                <th className="px-4 py-3">
                                    Jenis Sertifikat / Dokumen
                                </th>
                                <th className="px-4 py-3">No Surat</th>
                                <th className="px-4 py-3">Terbit</th>
                                <th className="px-4 py-3">Sampai Dengan</th>
                                <th className="px-4 py-3">Instansi Penerbit</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3">Link Dokumen</th>
                                <th className="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {rows.map((row) => (
                                <tr key={row.document_type.id}>
                                    <td className="px-4 py-4 text-slate-500">
                                        {row.no}
                                    </td>
                                    <td className="max-w-md px-4 py-4">
                                        <p className="font-medium text-slate-950">
                                            {row.document_type.name}
                                        </p>
                                        <p className="mt-1 text-xs text-slate-400">
                                            {row.document_type.code}
                                        </p>
                                    </td>
                                    <td className="px-4 py-4 text-slate-600">
                                        {row.document?.letter_number ?? '-'}
                                    </td>
                                    <td className="px-4 py-4 text-slate-600">
                                        {row.document?.issued_at ?? '-'}
                                    </td>
                                    <td className="px-4 py-4 text-slate-600">
                                        {row.document?.is_permanent
                                            ? 'Permanent'
                                            : row.document?.expires_at ?? '-'}
                                    </td>
                                    <td className="px-4 py-4 text-slate-600">
                                        {row.document?.issuer ?? '-'}
                                    </td>
                                    <td className="px-4 py-4">
                                        <StatusBadge status={row.status} />
                                    </td>
                                    <td className="px-4 py-4">
                                        <DocumentLink document={row.document} />
                                    </td>
                                    <td className="px-4 py-4">
                                        <div className="flex justify-end gap-2">
                                            <Link href={row.upload_url}>
                                                <Button
                                                    size="sm"
                                                    type="button"
                                                    variant={
                                                        row.document
                                                            ? 'outline'
                                                            : 'default'
                                                    }
                                                >
                                                    {row.document
                                                        ? 'Upload Ulang'
                                                        : 'Upload'}
                                                </Button>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {rows.length === 0 && (
                                <tr>
                                    <td
                                        className="px-4 py-10 text-center text-slate-500"
                                        colSpan={9}
                                    >
                                        Tidak ada data monitoring untuk kapal
                                        yang dipilih.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}

function DocumentLink({
    document,
}: {
    document: MonitoringRow['document'];
}) {
    if (!document) {
        return <span className="text-slate-400">-</span>;
    }

    if (document.preview_url) {
        return (
            <div className="flex flex-wrap gap-2">
                <DocumentPreviewButton href={document.preview_url} />
                {document.download_url && (
                    <DocumentDownloadButton href={document.download_url} />
                )}
            </div>
        );
    }

    if (document.external_link?.startsWith('http')) {
        return (
            <a
                className="font-medium text-cyan-700 hover:text-cyan-900"
                href={document.external_link}
                rel="noreferrer"
                target="_blank"
            >
                Lihat Dokumen
            </a>
        );
    }

    if (document.external_link) {
        return (
            <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                Ref: {document.external_link}
            </span>
        );
    }

    return <span className="text-slate-400">-</span>;
}
