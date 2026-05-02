import DocumentDownloadButton from '@/Components/DocumentDownloadButton';
import DocumentPreviewButton from '@/Components/DocumentPreviewButton';
import EmptyState from '@/Components/EmptyState';
import InputError from '@/Components/InputError';
import Modal from '@/Components/Modal';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Components/AppLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    AlertTriangle,
    CheckCircle2,
    ClipboardCheck,
    FileText,
    RefreshCw,
} from 'lucide-react';
import { FormEvent, useState } from 'react';

type DocumentTypeOption = {
    id: number;
    code: string;
    name: string;
};

type OcrDocument = {
    id: number;
    upload_mode: 'targeted' | 'smart' | string;
    processing_status: string;
    processing_error?: string | null;
    letter_number?: string | null;
    issued_at?: string | null;
    expires_at?: string | null;
    issuer?: string | null;
    is_permanent: boolean;
    ocr_text?: string | null;
    classification_confidence?: string | number | null;
    extraction_confidence?: string | number | null;
    extracted_values: {
        letter_number?: string | null;
        issued_at?: string | null;
        expires_at?: string | null;
        issuer?: string | null;
        is_permanent?: boolean;
    };
    warnings: string[];
    original_filename?: string | null;
    mime_type?: string | null;
    is_pdf: boolean;
    is_image: boolean;
    preview_url?: string | null;
    download_url?: string | null;
    vessel: {
        id: number;
        name: string;
        code?: string | null;
    };
    branch: {
        id: number;
        code: string;
        name: string;
    };
    document_type?: DocumentTypeOption | null;
    uploaded_by?: string | null;
    confirmed_by?: string | null;
    confirmed_at?: string | null;
};

type Props = {
    document: OcrDocument;
    documentTypes: DocumentTypeOption[];
};

export default function OcrConfirmation({ document, documentTypes }: Props) {
    const [showModal, setShowModal] = useState(false);
    const flash = usePage().props.flash as
        | { success?: string; error?: string }
        | undefined;
    const extracted = document.extracted_values ?? {};
    const editable = ['need_confirmation', 'confirmed'].includes(
        document.processing_status,
    );
    const isSmartUpload = document.upload_mode === 'smart';

    const { data, setData, put, processing, errors } = useForm({
        document_type_id: document.document_type?.id
            ? String(document.document_type.id)
            : '',
        letter_number:
            document.letter_number ?? extracted.letter_number ?? '',
        issued_at: document.issued_at ?? extracted.issued_at ?? '',
        expires_at: document.expires_at ?? extracted.expires_at ?? '',
        issuer: document.issuer ?? extracted.issuer ?? '',
        is_permanent: Boolean(
            document.is_permanent ?? extracted.is_permanent ?? false,
        ),
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        setShowModal(true);
    };

    const confirmSave = () => {
        put(route('ocr.confirmation.confirm', document.id), {
            preserveScroll: true,
            onSuccess: () => setShowModal(false),
        });
    };

    return (
        <AppLayout title="OCR Confirmation">
            <Head title="OCR Confirmation" />
            <PageHeader
                actions={<StatusBadge status={document.processing_status} />}
                description="Review hasil OCR simulasi, koreksi field bila perlu, lalu simpan langsung ke monitoring."
                title="OCR Confirmation"
            />

            {flash?.success && (
                <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {flash.success}
                </div>
            )}

            <div className="grid gap-6 lg:grid-cols-[1fr_440px]">
                <DocumentPreviewPanel document={document} />

                <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-cyan-700">
                                {document.branch.name}
                            </p>
                            <h2 className="mt-1 text-lg font-semibold text-slate-950">
                                {document.vessel.name}
                            </h2>
                            <p className="mt-1 text-sm text-slate-500">
                                {document.original_filename ?? 'Dokumen kapal'}
                            </p>
                        </div>
                        <StatusBadge status={document.upload_mode} />
                    </div>

                    {document.processing_status === 'pending' ||
                    document.processing_status === 'processing' ? (
                        <ProcessingState />
                    ) : null}

                    {document.processing_status === 'failed' ? (
                        <FailedState message={document.processing_error} />
                    ) : null}

                    {editable ? (
                        <form className="mt-6 space-y-4" onSubmit={submit}>
                            <div>
                                <label className="text-sm font-medium text-slate-700">
                                    Jenis Dokumen
                                </label>
                                {isSmartUpload ? (
                                    <select
                                        className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
                                        onChange={(event) =>
                                            setData(
                                                'document_type_id',
                                                event.target.value,
                                            )
                                        }
                                        value={data.document_type_id}
                                    >
                                        <option value="">
                                            Pilih jenis dokumen
                                        </option>
                                        {documentTypes.map((type) => (
                                            <option
                                                key={type.id}
                                                value={type.id}
                                            >
                                                {type.name} ({type.code})
                                            </option>
                                        ))}
                                    </select>
                                ) : (
                                    <div className="mt-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                        {document.document_type?.name ?? '-'}
                                    </div>
                                )}
                                <InputError
                                    className="mt-1"
                                    message={errors.document_type_id}
                                />
                            </div>

                            <TextField
                                error={errors.letter_number}
                                label="Nomor Surat"
                                onChange={(value) =>
                                    setData('letter_number', value)
                                }
                                value={data.letter_number}
                            />

                            <div className="grid gap-4 md:grid-cols-2">
                                <TextField
                                    error={errors.issued_at}
                                    label="Tanggal Terbit"
                                    onChange={(value) =>
                                        setData('issued_at', value)
                                    }
                                    type="date"
                                    value={data.issued_at}
                                />
                                <TextField
                                    disabled={data.is_permanent}
                                    error={errors.expires_at}
                                    label="Sampai Dengan"
                                    onChange={(value) =>
                                        setData('expires_at', value)
                                    }
                                    type="date"
                                    value={data.expires_at}
                                />
                            </div>

                            <TextField
                                error={errors.issuer}
                                label="Instansi Penerbit"
                                onChange={(value) => setData('issuer', value)}
                                value={data.issuer}
                            />

                            <label className="flex items-center gap-3 rounded-md border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-700">
                                <input
                                    checked={data.is_permanent}
                                    className="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                                    onChange={(event) =>
                                        setData(
                                            'is_permanent',
                                            event.target.checked,
                                        )
                                    }
                                    type="checkbox"
                                />
                                Dokumen berlaku permanen
                            </label>

                            <ConfidencePanel document={document} />

                            <Button
                                className="w-full"
                                disabled={processing}
                                type="submit"
                            >
                                <ClipboardCheck className="h-4 w-4" />
                                Simpan Data Monitoring
                            </Button>
                        </form>
                    ) : null}
                </div>
            </div>

            <Modal maxWidth="md" onClose={() => setShowModal(false)} show={showModal}>
                <div className="p-6">
                    <div className="flex items-center gap-3">
                        <div className="rounded-lg bg-cyan-50 p-2 text-cyan-700 ring-1 ring-cyan-100">
                            <CheckCircle2 className="h-5 w-5" />
                        </div>
                        <h2 className="text-lg font-semibold text-slate-950">
                            Konfirmasi Data OCR
                        </h2>
                    </div>
                    <p className="mt-4 text-sm leading-6 text-slate-600">
                        Apakah data sudah sesuai dengan dokumen yang diunggah?
                        Data ini akan digunakan untuk monitoring pusat.
                    </p>
                    <div className="mt-6 flex justify-end gap-3">
                        <Button
                            onClick={() => setShowModal(false)}
                            type="button"
                            variant="outline"
                        >
                            Batal
                        </Button>
                        <Button
                            disabled={processing}
                            onClick={confirmSave}
                            type="button"
                        >
                            Ya, Simpan Data
                        </Button>
                    </div>
                </div>
            </Modal>
        </AppLayout>
    );
}

function DocumentPreviewPanel({ document }: { document: OcrDocument }) {
    return (
        <div className="space-y-4">
            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div className="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 className="text-base font-semibold text-slate-950">
                            Preview Dokumen
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            File disajikan melalui route terproteksi.
                        </p>
                    </div>
                    <div className="flex gap-2">
                        {document.preview_url && (
                            <DocumentPreviewButton href={document.preview_url} />
                        )}
                        {document.download_url && (
                            <DocumentDownloadButton
                                href={document.download_url}
                            />
                        )}
                    </div>
                </div>
                <div className="min-h-[520px] bg-slate-50">
                    {document.preview_url && document.is_pdf && (
                        <iframe
                            className="h-[520px] w-full"
                            src={document.preview_url}
                            title="Document preview"
                        />
                    )}
                    {document.preview_url && document.is_image && (
                        <div className="flex min-h-[520px] items-center justify-center p-4">
                            <img
                                alt="Document preview"
                                className="max-h-[500px] rounded-md border border-slate-200 object-contain shadow-sm"
                                src={document.preview_url}
                            />
                        </div>
                    )}
                    {(!document.preview_url ||
                        (!document.is_pdf && !document.is_image)) && (
                        <div className="p-6">
                            <EmptyState
                                description="Preview inline hanya tersedia untuk PDF dan gambar. Gunakan tombol download untuk file lain."
                                icon={FileText}
                                title="Preview tidak tersedia"
                            />
                        </div>
                    )}
                </div>
            </div>

            <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <details>
                    <summary className="cursor-pointer text-sm font-semibold text-slate-800">
                        Lihat teks OCR
                    </summary>
                    <pre className="mt-4 max-h-80 overflow-auto whitespace-pre-wrap rounded-md bg-slate-950 p-4 text-xs leading-5 text-slate-100">
                        {document.ocr_text ?? 'OCR belum tersedia.'}
                    </pre>
                </details>
            </div>
        </div>
    );
}

function ProcessingState() {
    return (
        <div className="mt-6 rounded-lg border border-cyan-200 bg-cyan-50 p-4 text-sm text-cyan-800">
            <div className="flex items-start gap-3">
                <RefreshCw className="mt-0.5 h-4 w-4" />
                <div>
                    <p className="font-medium">OCR sedang diproses.</p>
                    <p className="mt-1">
                        Tunggu beberapa saat lalu refresh halaman untuk melihat
                        hasil ekstraksi.
                    </p>
                    <Button
                        className="mt-3"
                        onClick={() => router.reload()}
                        size="sm"
                        type="button"
                        variant="outline"
                    >
                        Refresh Status
                    </Button>
                </div>
            </div>
        </div>
    );
}

function FailedState({ message }: { message?: string | null }) {
    return (
        <div className="mt-6 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <div className="flex items-start gap-3">
                <AlertTriangle className="mt-0.5 h-4 w-4" />
                <div>
                    <p className="font-medium">OCR gagal diproses.</p>
                    <p className="mt-1">{message ?? 'Silakan coba unggah ulang.'}</p>
                </div>
            </div>
        </div>
    );
}

function TextField({
    disabled = false,
    error,
    label,
    onChange,
    type = 'text',
    value,
}: {
    disabled?: boolean;
    error?: string;
    label: string;
    onChange: (value: string) => void;
    type?: string;
    value: string;
}) {
    return (
        <div>
            <label className="text-sm font-medium text-slate-700">{label}</label>
            <input
                className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 disabled:bg-slate-100"
                disabled={disabled}
                onChange={(event) => onChange(event.target.value)}
                type={type}
                value={value}
            />
            <InputError className="mt-1" message={error} />
        </div>
    );
}

function ConfidencePanel({ document }: { document: OcrDocument }) {
    const formatConfidence = (value?: string | number | null) => {
        if (value === null || value === undefined || value === '') {
            return '-';
        }

        return `${Math.round(Number(value) * 100)}%`;
    };

    return (
        <div className="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <div className="grid gap-3 text-sm md:grid-cols-2">
                <div>
                    <p className="text-slate-500">Extraction Confidence</p>
                    <p className="mt-1 font-semibold text-slate-950">
                        {formatConfidence(document.extraction_confidence)}
                    </p>
                </div>
                <div>
                    <p className="text-slate-500">Classification Confidence</p>
                    <p className="mt-1 font-semibold text-slate-950">
                        {formatConfidence(document.classification_confidence)}
                    </p>
                </div>
            </div>
            {document.warnings.length > 0 && (
                <div className="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2">
                    <p className="text-sm font-medium text-amber-800">
                        Catatan OCR
                    </p>
                    <ul className="mt-2 space-y-1 text-sm text-amber-700">
                        {document.warnings.map((warning) => (
                            <li key={warning}>{warning}</li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}
