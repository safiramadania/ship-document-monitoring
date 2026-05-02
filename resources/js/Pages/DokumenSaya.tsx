import PlaceholderPage from '@/Components/PlaceholderPage';
import { Head } from '@inertiajs/react';
import { FileArchive } from 'lucide-react';

export default function DokumenSaya() {
    return (
        <>
            <Head title="Dokumen Saya" />
            <PlaceholderPage
                description="Placeholder daftar dokumen milik cabang user."
                emptyDescription="Branch-scoped document list akan dibuat setelah model dan authorization tersedia."
                emptyTitle="Dokumen cabang belum tersedia"
                icon={FileArchive}
                title="Dokumen Saya"
            />
        </>
    );
}
