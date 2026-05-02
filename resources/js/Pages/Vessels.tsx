import PlaceholderPage from '@/Components/PlaceholderPage';
import { Head } from '@inertiajs/react';
import { Ship } from 'lucide-react';

export default function Vessels() {
    return (
        <>
            <Head title="Vessels" />
            <PlaceholderPage
                description="Placeholder master kapal per cabang."
                emptyDescription="Model, migration, dan seeder kapal akan dibuat pada Milestone 2."
                emptyTitle="Vessels belum disiapkan"
                icon={Ship}
                title="Vessels"
            />
        </>
    );
}
