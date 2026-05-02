import PlaceholderPage from '@/Components/PlaceholderPage';
import { Head } from '@inertiajs/react';
import { FileSearch } from 'lucide-react';

export default function SmartUpload() {
    return (
        <>
            <Head title="Smart Upload" />
            <PlaceholderPage
                description="Halaman awal untuk unggah dokumen dengan klasifikasi jenis dokumen otomatis."
                emptyDescription="Klasifikasi memakai nama, alias, dan keyword document types akan dibuat pada Milestone 8."
                emptyTitle="Smart upload belum aktif"
                icon={FileSearch}
                title="Smart Upload"
            />
        </>
    );
}
