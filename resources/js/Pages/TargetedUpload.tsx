import AppLayout from '@/Components/AppLayout';
import InputError from '@/Components/InputError';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Head, Link, useForm } from '@inertiajs/react';
import { FileUp, Ship, UploadCloud } from 'lucide-react';
import { FormEventHandler } from 'react';

type Props = {
    vessel: {
        id: number;
        name: string;
        code?: string | null;
        branch: {
            id: number;
            code: string;
            name: string;
        };
    };
    documentType: {
        id: number;
        code: string;
        name: string;
    };
};

export default function TargetedUpload({ vessel, documentType }: Props) {
    const { data, setData, post, processing, errors, progress } = useForm<{
        document: File | null;
    }>({
        document: null,
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        post(route('targeted-uploads.store', [vessel.id, documentType.id]), {
            forceFormData: true,
        });
    };

    return (
        <AppLayout title="Upload Dokumen">
            <Head title="Upload Dokumen" />
            <PageHeader
                description="Targeted upload dari baris monitoring. Kapal dan jenis dokumen sudah diketahui."
                title="Upload Dokumen"
            />

            <div className="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                <div className="space-y-4">
                    <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div className="flex items-start gap-3">
                            <div className="rounded-lg bg-cyan-50 p-2.5 text-cyan-700 ring-1 ring-cyan-100">
                                <Ship className="h-5 w-5" />
                            </div>
                            <div>
                                <p className="text-sm font-medium text-slate-500">
                                    Vessel
                                </p>
                                <h2 className="mt-1 text-lg font-semibold text-slate-950">
                                    {vessel.name}
                                </h2>
                                <p className="mt-1 text-sm text-slate-500">
                                    {vessel.branch.name} ({vessel.branch.code})
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">
                            Document Type
                        </p>
                        <h2 className="mt-1 text-lg font-semibold text-slate-950">
                            {documentType.name}
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            {documentType.code}
                        </p>
                    </div>
                </div>

                <form
                    className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm"
                    onSubmit={submit}
                >
                    <div className="flex items-start gap-3">
                        <div className="rounded-lg bg-blue-50 p-2.5 text-blue-700 ring-1 ring-blue-100">
                            <FileUp className="h-5 w-5" />
                        </div>
                        <div>
                            <h2 className="text-base font-semibold text-slate-950">
                                Pilih file dokumen
                            </h2>
                            <p className="mt-1 text-sm text-slate-500">
                                Format PDF, JPG, atau PNG. Maksimal 20MB. File
                                akan disimpan di private storage.
                            </p>
                        </div>
                    </div>

                    <label className="mt-6 flex cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center transition hover:border-cyan-300 hover:bg-cyan-50/40">
                        <UploadCloud className="h-8 w-8 text-cyan-700" />
                        <span className="mt-3 text-sm font-medium text-slate-700">
                            Klik untuk memilih file
                        </span>
                        <span className="mt-1 text-xs text-slate-500">
                            {data.document?.name ?? 'Belum ada file dipilih'}
                        </span>
                        <input
                            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                            className="sr-only"
                            onChange={(event) =>
                                setData(
                                    'document',
                                    event.target.files?.[0] ?? null,
                                )
                            }
                            type="file"
                        />
                    </label>

                    <InputError className="mt-2" message={errors.document} />

                    {progress && (
                        <div className="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div
                                className="h-full rounded-full bg-cyan-500"
                                style={{ width: `${progress.percentage}%` }}
                            />
                        </div>
                    )}

                    <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <Link
                            href={route('monitoring.index', {
                                branch_id: vessel.branch.id,
                                vessel_id: vessel.id,
                            })}
                        >
                            <Button type="button" variant="outline">
                                Kembali
                            </Button>
                        </Link>
                        <Button
                            disabled={processing || !data.document}
                            type="submit"
                        >
                            Upload Dokumen
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
