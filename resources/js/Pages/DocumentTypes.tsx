import PlaceholderPage from '@/Components/PlaceholderPage';
import { Head } from '@inertiajs/react';
import { FileText } from 'lucide-react';

export default function DocumentTypes() {
    return (
        <>
            <Head title="Document Types" />
            <PlaceholderPage
                description="Placeholder master jenis dokumen, alias, dan keyword untuk smart upload."
                emptyDescription="Document types akan disediakan lewat seeder dari spreadsheet pada Milestone 2."
                emptyTitle="Document types belum disiapkan"
                icon={FileText}
                title="Document Types"
            />
        </>
    );
}
