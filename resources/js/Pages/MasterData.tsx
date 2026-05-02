import PlaceholderPage from '@/Components/PlaceholderPage';
import { Head } from '@inertiajs/react';
import { Database } from 'lucide-react';

export default function MasterData() {
    return (
        <>
            <Head title="Master Data" />
            <PlaceholderPage
                description="Placeholder ringkasan master data cabang, kapal, dan jenis dokumen."
                emptyDescription="Master data akan dipisah menjadi halaman cabang, kapal, dan document types."
                emptyTitle="Master data belum terhubung"
                icon={Database}
                title="Master Data"
            />
        </>
    );
}
