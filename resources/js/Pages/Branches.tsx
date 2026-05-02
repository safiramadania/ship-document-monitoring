import PlaceholderPage from '@/Components/PlaceholderPage';
import { Head } from '@inertiajs/react';
import { Workflow } from 'lucide-react';

export default function Branches() {
    return (
        <>
            <Head title="Branches" />
            <PlaceholderPage
                description="Placeholder master cabang ASDP."
                emptyDescription="Master cabang akan berasal dari seed data spreadsheet pada Milestone 2."
                emptyTitle="Branches belum disiapkan"
                icon={Workflow}
                title="Branches"
            />
        </>
    );
}
