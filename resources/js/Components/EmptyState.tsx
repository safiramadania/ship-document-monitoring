import { FileSearch, LucideIcon } from 'lucide-react';

type EmptyStateProps = {
    title: string;
    description: string;
    icon?: LucideIcon;
};

export default function EmptyState({
    title,
    description,
    icon: Icon = FileSearch,
}: EmptyStateProps) {
    return (
        <div className="rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
            <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100">
                <Icon className="h-6 w-6" />
            </div>
            <h3 className="mt-4 text-base font-semibold text-slate-950">
                {title}
            </h3>
            <p className="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">
                {description}
            </p>
        </div>
    );
}
