import { Link, usePage } from '@inertiajs/react';
import { Bell, Menu, Search, UserCircle } from 'lucide-react';

import { Button } from '@/Components/ui/button';
import { roleLabels, UserRole } from '@/config/navigation';
import { PageProps } from '@/types';

type TopbarProps = {
    title: string;
    role: UserRole;
    onMenuClick: () => void;
};

export default function Topbar({ title, role, onMenuClick }: TopbarProps) {
    const user = usePage<PageProps>().props.auth.user;

    return (
        <header className="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div className="flex min-h-16 items-center gap-4 px-4 sm:px-6 lg:px-8">
                <Button
                    className="lg:hidden"
                    onClick={onMenuClick}
                    size="icon"
                    type="button"
                    variant="ghost"
                >
                    <Menu className="h-5 w-5" />
                </Button>

                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm text-slate-500">
                        {roleLabels[role]}
                    </p>
                    <h2 className="truncate text-base font-semibold text-slate-950">
                        {title}
                    </h2>
                </div>

                <div className="hidden w-full max-w-sm items-center rounded-md border border-slate-200 bg-slate-50 px-3 py-2 md:flex">
                    <Search className="h-4 w-4 text-slate-400" />
                    <input
                        className="ml-2 w-full border-0 bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:ring-0"
                        placeholder="Cari kapal, dokumen, atau cabang"
                        type="search"
                    />
                </div>

                <Button size="icon" type="button" variant="outline">
                    <Bell className="h-4 w-4" />
                </Button>

                <Link
                    className="hidden items-center gap-3 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition hover:bg-slate-50 sm:flex"
                    href={route('profile.edit')}
                >
                    <div className="flex h-8 w-8 items-center justify-center rounded-full bg-cyan-50 text-cyan-700">
                        <UserCircle className="h-5 w-5" />
                    </div>
                    <div className="text-left">
                        <p className="max-w-36 truncate font-medium text-slate-800">
                            {user.name}
                        </p>
                        <p className="max-w-36 truncate text-xs text-slate-500">
                            {user.email}
                        </p>
                    </div>
                </Link>
            </div>
        </header>
    );
}
