import { cn } from '@/lib/utils';

type StatusTone =
    | 'active'
    | 'expiring_soon'
    | 'expired'
    | 'permanent'
    | 'missing'
    | 'unknown'
    | 'need_confirmation'
    | 'processing'
    | 'failed'
    | 'pending';

type StatusBadgeProps = {
    status: StatusTone | string;
    label?: string;
};

const statusClasses: Record<StatusTone, string> = {
    active: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    expiring_soon: 'bg-amber-50 text-amber-700 ring-amber-200',
    expired: 'bg-rose-50 text-rose-700 ring-rose-200',
    permanent: 'bg-blue-50 text-blue-700 ring-blue-200',
    missing: 'bg-slate-100 text-slate-700 ring-slate-200',
    unknown: 'bg-slate-50 text-slate-600 ring-slate-200',
    need_confirmation: 'bg-purple-50 text-purple-700 ring-purple-200',
    processing: 'bg-cyan-50 text-cyan-700 ring-cyan-200',
    failed: 'bg-red-50 text-red-700 ring-red-200',
    pending: 'bg-slate-50 text-slate-600 ring-slate-200',
};

const fallbackClass = 'bg-slate-50 text-slate-600 ring-slate-200';

export default function StatusBadge({ status, label }: StatusBadgeProps) {
    const className =
        status in statusClasses
            ? statusClasses[status as StatusTone]
            : fallbackClass;

    return (
        <span
            className={cn(
                'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset',
                className,
            )}
        >
            {label ?? status.replaceAll('_', ' ')}
        </span>
    );
}
