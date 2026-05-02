import { ReactNode, useState } from 'react';

import Sidebar from '@/Components/Sidebar';
import Topbar from '@/Components/Topbar';
import { UserRole } from '@/config/navigation';
import { useCurrentRole } from '@/hooks/useCurrentRole';

type AppLayoutProps = {
    title: string;
    children: ReactNode;
    roleOverride?: UserRole;
};

export default function AppLayout({
    title,
    children,
    roleOverride,
}: AppLayoutProps) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const role = useCurrentRole(roleOverride);

    return (
        <div className="min-h-screen bg-slate-100 text-slate-900">
            <div className="flex min-h-screen">
                <Sidebar
                    onClose={() => setSidebarOpen(false)}
                    open={sidebarOpen}
                    role={role}
                />

                <div className="min-w-0 flex-1">
                    <Topbar
                        onMenuClick={() => setSidebarOpen(true)}
                        role={role}
                        title={title}
                    />
                    <main className="px-4 py-6 sm:px-6 lg:px-8">
                        <div className="mx-auto max-w-7xl space-y-6">
                            {children}
                        </div>
                    </main>
                </div>
            </div>
        </div>
    );
}
