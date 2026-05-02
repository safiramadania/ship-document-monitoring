import AppLayout from '@/Components/AppLayout';
import EmptyState from '@/Components/EmptyState';
import InputError from '@/Components/InputError';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { BranchSummary } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { FileSearch, Ship, UploadCloud } from 'lucide-react';
import { FormEvent } from 'react';

type VesselSummary = {
    id: number;
    branch_id: number;
    code?: string | null;
    name: string;
};

type Props = {
    branches: BranchSummary[];
    vessels: VesselSummary[];
    selectedBranchId?: number | null;
    selectedBranch?: BranchSummary | null;
    branchLocked: boolean;
};

export default function SmartUpload({
    branches,
    vessels,
    selectedBranchId,
    selectedBranch,
    branchLocked,
}: Props) {
    const flash = usePage().props.flash as
        | { success?: string; error?: string }
        | undefined;
    const { data, setData, post, processing, errors, progress } = useForm<{
        vessel_id: string;
        document: File | null;
    }>({
        vessel_id: vessels[0]?.id ? String(vessels[0].id) : '',
        document: null,
    });

    const updateFilters = (params: Record<string, string | number | null>) => {
        router.get(route('uploads.smart'), params, {
            preserveScroll: true,
            preserveState: false,
        });
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        post(route('uploads.smart.store'), {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    return (
        <AppLayout title="Smart Upload">
            <Head title="Smart Upload" />
            <PageHeader
                description="Upload dokumen tanpa memilih jenis surat. Sistem akan mencoba mengklasifikasikan jenis surat secara otomatis."
                title="Smart Upload"
            />

            {flash?.success && (
                <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {flash.success}
                </div>
            )}

            <div className="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
                <div className="rounded-lg border border-cyan-200 bg-white p-5 shadow-sm">
                    <div className="flex items-start gap-3">
                        <div className="rounded-lg bg-cyan-50 p-2.5 text-cyan-700 ring-1 ring-cyan-100">
                            <FileSearch className="h-5 w-5" />
                        </div>
                        <div>
                            <h2 className="text-base font-semibold text-slate-950">
                                Klasifikasi Otomatis
                            </h2>
                            <p className="mt-2 text-sm leading-6 text-slate-600">
                                Pilih kapal, unggah PDF atau gambar, lalu sistem
                                akan menjalankan OCR simulasi dan mencocokkan
                                jenis dokumen dari master document types.
                            </p>
                        </div>
                    </div>

                    <div className="mt-5 rounded-md bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        Cabang aktif:{' '}
                        <span className="font-semibold text-slate-950">
                            {selectedBranch
                                ? `${selectedBranch.name} (${selectedBranch.code})`
                                : '-'}
                        </span>
                    </div>
                </div>

                <form
                    className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                    onSubmit={submit}
                >
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
                                disabled={vessels.length === 0}
                                onChange={(event) =>
                                    setData('vessel_id', event.target.value)
                                }
                                value={data.vessel_id}
                            >
                                {vessels.map((vessel) => (
                                    <option key={vessel.id} value={vessel.id}>
                                        {vessel.name}
                                    </option>
                                ))}
                            </select>
                            <InputError
                                className="mt-1"
                                message={errors.vessel_id}
                            />
                        </div>
                    </div>

                    <div className="mt-5">
                        <label className="text-sm font-medium text-slate-700">
                            File Dokumen
                        </label>
                        <div className="mt-2 rounded-lg border border-dashed border-cyan-200 bg-cyan-50/40 p-6">
                            <div className="flex flex-col items-center text-center">
                                <div className="rounded-lg bg-white p-3 text-cyan-700 shadow-sm ring-1 ring-cyan-100">
                                    <UploadCloud className="h-6 w-6" />
                                </div>
                                <p className="mt-3 text-sm font-medium text-slate-800">
                                    PDF, JPG, atau PNG maksimal 20MB
                                </p>
                                <input
                                    accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                    className="mt-4 block w-full cursor-pointer rounded-md border border-slate-200 bg-white text-sm text-slate-600 file:mr-4 file:border-0 file:bg-cyan-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-cyan-700"
                                    onChange={(event) =>
                                        setData(
                                            'document',
                                            event.target.files?.[0] ?? null,
                                        )
                                    }
                                    type="file"
                                />
                            </div>
                        </div>
                        <InputError className="mt-2" message={errors.document} />
                    </div>

                    {progress && (
                        <div className="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div
                                className="h-full rounded-full bg-cyan-500"
                                style={{ width: `${progress.percentage ?? 0}%` }}
                            />
                        </div>
                    )}

                    <div className="mt-6 flex justify-end">
                        <Button
                            disabled={
                                processing || !data.vessel_id || !data.document
                            }
                            type="submit"
                        >
                            <UploadCloud className="h-4 w-4" />
                            Upload dan Proses OCR
                        </Button>
                    </div>
                </form>
            </div>

            {vessels.length === 0 && (
                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <EmptyState
                        description="Belum ada kapal pada cabang yang dipilih."
                        icon={Ship}
                        title="Kapal tidak tersedia"
                    />
                </div>
            )}
        </AppLayout>
    );
}
