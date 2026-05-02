import PageHeader from '@/Components/PageHeader';
import PlaceholderPage from '@/Components/PlaceholderPage';
import StatCard from '@/Components/StatCard';
import StatusBadge from '@/Components/StatusBadge';
import { Head } from '@inertiajs/react';
import { CheckCircle2, Clock3, FileWarning, Ship } from 'lucide-react';

const rows = [
    ['1', 'Jenis Dokumen', 'No Surat', 'Terbit', 'Sampai Dengan', 'Status'],
    ['2', 'Contoh baris monitoring', '-', '-', '-', 'missing'],
    ['3', 'Contoh dokumen aktif', '-', '-', '-', 'active'],
];

export default function MonitoringKapal() {
    return (
        <>
            <Head title="Monitoring Kapal" />
            <PlaceholderPage
                description="Tampilan awal tabel monitoring kapal dengan filter cabang/kapal dan status dokumen."
                emptyDescription="Data monitoring, status validitas, dan tombol targeted upload akan terhubung pada milestone berikutnya."
                emptyTitle="Monitoring kapal belum terhubung ke data"
                icon={Ship}
                title="Monitoring Kapal"
            >
                <div className="grid gap-4 md:grid-cols-4">
                    <StatCard
                        icon={Ship}
                        title="Kapal Dipilih"
                        tone="cyan"
                        value="0"
                    />
                    <StatCard
                        icon={CheckCircle2}
                        title="Active"
                        tone="emerald"
                        value="0"
                    />
                    <StatCard
                        icon={Clock3}
                        title="Expiring Soon"
                        tone="amber"
                        value="0"
                    />
                    <StatCard
                        icon={FileWarning}
                        title="Missing"
                        tone="rose"
                        value="0"
                    />
                </div>

                <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-100 p-5">
                        <PageHeader
                            description="Struktur kolom mengikuti kebutuhan monitoring spreadsheet."
                            title="Preview Tabel"
                        />
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200 text-sm">
                            <tbody className="divide-y divide-slate-100">
                                {rows.map((row) => (
                                    <tr key={row.join('-')}>
                                        {row.map((cell, index) => (
                                            <td
                                                className="whitespace-nowrap px-5 py-4 text-slate-600"
                                                key={`${cell}-${index}`}
                                            >
                                                {index === 5 &&
                                                cell !== 'Status' ? (
                                                    <StatusBadge status={cell} />
                                                ) : (
                                                    cell
                                                )}
                                            </td>
                                        ))}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </PlaceholderPage>
        </>
    );
}
