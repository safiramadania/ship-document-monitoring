import { Link } from '@inertiajs/react';
import { X } from 'lucide-react';

import { Button } from '@/Components/ui/button';
import {
    getNavigationItems,
    roleLabels,
    UserRole,
} from '@/config/navigation';
import { cn } from '@/lib/utils';

type SidebarProps = {
    role: UserRole;
    open: boolean;
    onClose: () => void;
};

export default function Sidebar({ role, open, onClose }: SidebarProps) {
    const items = getNavigationItems(role);

    const content = (
        <div className="flex h-full flex-col bg-slate-950 text-white">
            <div className="flex h-16 items-center justify-between border-b border-white/10 px-5">
                <Link href={route('dashboard')} className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-400 text-slate-950">
                        SD
                    </div>
                    <div>
                        <p className="text-sm font-semibold">
                            Ship Document
                        </p>
                        <p className="text-xs text-cyan-100">Monitoring</p>
                    </div>
                </Link>
                <Button
                    className="text-white hover:bg-white/10 lg:hidden"
                    onClick={onClose}
                    size="icon"
                    type="button"
                    variant="ghost"
                >
                    <X className="h-5 w-5" />
                </Button>
            </div>

            <div className="px-4 py-4">
                <div className="rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-3 py-2">
                    <p className="text-xs font-medium text-cyan-100">Role</p>
                    <p className="text-sm font-semibold text-white">
                        {roleLabels[role]}
                    </p>
                </div>
            </div>

            <nav className="flex-1 space-y-1 px-3 pb-5">
                {items.map((item) => {
                    const active = route().current(item.routeName);
                    const Icon = item.icon;

                    return (
                        <Link
                            className={cn(
                                'flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition',
                                active
                                    ? 'bg-cyan-400 text-slate-950 shadow-sm'
                                    : 'text-slate-300 hover:bg-white/10 hover:text-white',
                            )}
                            href={route(item.routeName)}
                            key={item.routeName}
                            onClick={onClose}
                        >
                            <Icon className="h-4 w-4" />
                            {item.label}
                        </Link>
                    );
                })}
            </nav>
        </div>
    );

    return (
        <>
            <aside className="hidden h-screen w-72 shrink-0 lg:sticky lg:top-0 lg:block">
                {content}
            </aside>

            <div
                className={cn(
                    'fixed inset-0 z-40 bg-slate-950/40 transition lg:hidden',
                    open ? 'opacity-100' : 'pointer-events-none opacity-0',
                )}
                onClick={onClose}
            />
            <aside
                className={cn(
                    'fixed inset-y-0 left-0 z-50 w-72 transform transition lg:hidden',
                    open ? 'translate-x-0' : '-translate-x-full',
                )}
            >
                {content}
            </aside>
        </>
    );
}
