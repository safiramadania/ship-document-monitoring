import { LucideIcon } from 'lucide-react';

import { cn } from '@/lib/utils';

type StatTone = 'cyan' | 'blue' | 'emerald' | 'amber' | 'rose' | 'slate';

type StatCardProps = {
    title: string;
    value: string;
    helper?: string;
    icon?: LucideIcon;
    tone?: StatTone;
};

const toneClasses: Record<StatTone, string> = {
    cyan: 'bg-cyan-50 text-cyan-700 ring-cyan-100',
    blue: 'bg-blue-50 text-blue-700 ring-blue-100',
    emerald: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
    amber: 'bg-amber-50 text-amber-700 ring-amber-100',
    rose: 'bg-rose-50 text-rose-700 ring-rose-100',
    slate: 'bg-slate-100 text-slate-700 ring-slate-200',
};

export default function StatCard({
    title,
    value,
    helper,
    icon: Icon,
    tone = 'cyan',
}: StatCardProps) {
    return (
        <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-sm font-medium text-slate-500">{title}</p>
                    <p className="mt-3 text-3xl font-semibold text-slate-950">
                        {value}
                    </p>
                </div>
                {Icon && (
                    <div
                        className={cn(
                            'rounded-lg p-2.5 ring-1',
                            toneClasses[tone],
                        )}
                    >
                        <Icon className="h-5 w-5" />
                    </div>
                )}
            </div>
            {helper && <p className="mt-4 text-sm text-slate-500">{helper}</p>}
        </div>
    );
}
