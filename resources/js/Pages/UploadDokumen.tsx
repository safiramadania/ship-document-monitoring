import PlaceholderPage from '@/Components/PlaceholderPage';
import { Head } from '@inertiajs/react';
import { UploadCloud } from 'lucide-react';

export default function UploadDokumen() {
    return (
        <>
            <Head title="Upload Dokumen" />
            <PlaceholderPage
                description="Halaman awal untuk targeted upload dari baris monitoring kapal."
                emptyDescription="Validasi PDF/JPG/PNG, private storage, dan pembuatan record dokumen akan dibuat pada Milestone 6."
                emptyTitle="Upload dokumen belum aktif"
                icon={UploadCloud}
                title="Upload Dokumen"
            />
        </>
    );
}
