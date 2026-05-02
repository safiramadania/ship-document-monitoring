import AppLayout from '@/Components/AppLayout';
import { PropsWithChildren, ReactNode } from 'react';

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    return (
        <AppLayout title="Profile">
            {header && (
                <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    {header}
                </div>
            )}
            {children}
        </AppLayout>
    );
}
