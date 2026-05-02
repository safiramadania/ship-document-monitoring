import StatusBadge from '@/Components/StatusBadge';

type Activity = {
    title: string;
    description: string;
    timestamp: string;
    status?: string;
};

type RecentActivityListProps = {
    items: Activity[];
    title?: string;
    emptyTitle?: string;
    emptyDescription?: string;
};

export default function RecentActivityList({
    items,
    title = 'Recent Activity',
    emptyTitle = 'Belum ada aktivitas',
    emptyDescription = 'Aktivitas terbaru akan tampil di sini setelah data tersedia.',
}: RecentActivityListProps) {
    return (
        <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div className="border-b border-slate-100 px-5 py-4">
                <h2 className="text-base font-semibold text-slate-950">
                    {title}
                </h2>
            </div>
            <div className="divide-y divide-slate-100">
                {items.length === 0 && (
                    <div className="px-5 py-8 text-center">
                        <p className="text-sm font-medium text-slate-700">
                            {emptyTitle}
                        </p>
                        <p className="mt-1 text-sm text-slate-500">
                            {emptyDescription}
                        </p>
                    </div>
                )}
                {items.map((item) => (
                    <div
                        className="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                        key={`${item.title}-${item.timestamp}`}
                    >
                        <div>
                            <p className="text-sm font-medium text-slate-950">
                                {item.title}
                            </p>
                            <p className="mt-1 text-sm text-slate-500">
                                {item.description}
                            </p>
                        </div>
                        <div className="flex items-center gap-3 sm:justify-end">
                            {item.status && <StatusBadge status={item.status} />}
                            <span className="text-xs font-medium text-slate-400">
                                {item.timestamp}
                            </span>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
