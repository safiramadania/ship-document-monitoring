import AppLayout from '@/Components/AppLayout';
import EmptyState from '@/Components/EmptyState';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { Head, usePage } from '@inertiajs/react';
import { ClipboardCheck, FileText } from 'lucide-react';

export default function OcrConfirmation() {
    const flash = usePage().props.flash as
        | { success?: string; error?: string }
        | undefined;

    return (
        <AppLayout title="OCR Confirmation">
            <Head title="OCR Confirmation" />
            <PageHeader
                actions={<StatusBadge status="need_confirmation" />}
                description="Preview struktur halaman konfirmasi hasil OCR sebelum data masuk monitoring."
                title="OCR Confirmation"
            />

            {flash?.success && (
                <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {flash.success}
                </div>
            )}

            <div className="grid gap-6 lg:grid-cols-[1fr_420px]">
                <EmptyState
                    description="Preview PDF/gambar akan muncul di panel ini setelah upload dan private preview route tersedia."
                    icon={FileText}
                    title="Document Preview"
                />
                <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div className="flex items-center gap-3">
                        <div className="rounded-lg bg-purple-50 p-2.5 text-purple-700 ring-1 ring-purple-100">
                            <ClipboardCheck className="h-5 w-5" />
                        </div>
                        <div>
                            <h2 className="text-base font-semibold text-slate-950">
                                Form Konfirmasi
                            </h2>
                            <p className="mt-1 text-sm text-slate-500">
                                Field OCR akan diisi otomatis pada milestone
                                OCR.
                            </p>
                        </div>
                    </div>

                    <div className="mt-5 space-y-3">
                        {[
                            'Document Type',
                            'Letter Number',
                            'Issue Date',
                            'Expiry Date',
                            'Issuer',
                            'Is Permanent',
                        ].map((label) => (
                            <div key={label}>
                                <label className="text-sm font-medium text-slate-600">
                                    {label}
                                </label>
                                <div className="mt-1 h-10 rounded-md border border-slate-200 bg-slate-50" />
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
