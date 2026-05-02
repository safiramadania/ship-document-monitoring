import { Link } from '@inertiajs/react';
import { ArrowRight, LucideIcon } from 'lucide-react';

type QuickActionCardProps = {
    title: string;
    description: string;
    href: string;
    icon: LucideIcon;
};

export default function QuickActionCard({
    title,
    description,
    href,
    icon: Icon,
}: QuickActionCardProps) {
    return (
        <Link
            href={href}
            className="group rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:border-cyan-200 hover:shadow-md"
        >
            <div className="flex items-start justify-between gap-4">
                <div className="rounded-lg bg-cyan-50 p-2.5 text-cyan-700 ring-1 ring-cyan-100">
                    <Icon className="h-5 w-5" />
                </div>
                <ArrowRight className="h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-cyan-600" />
            </div>
            <h3 className="mt-4 text-base font-semibold text-slate-950">
                {title}
            </h3>
            <p className="mt-2 text-sm leading-6 text-slate-500">
                {description}
            </p>
        </Link>
    );
}
