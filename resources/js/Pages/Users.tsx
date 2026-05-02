import AppLayout from '@/Components/AppLayout';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { BranchSummary } from '@/types';
import { Head } from '@inertiajs/react';
import { Users as UsersIcon } from 'lucide-react';

type UserRow = {
    id: number;
    name: string;
    email: string;
    role: string;
    status: string;
    job_title?: string | null;
    last_login_at?: string | null;
    last_seen_at?: string | null;
    created_at?: string | null;
    branch?: BranchSummary | null;
};

export default function Users({ users = [] }: { users: UserRow[] }) {
    return (
        <AppLayout title="Users">
            <Head title="Users" />
            <PageHeader
                description="Daftar user dengan role, cabang, status, last login, dan last online."
                title="Users"
            />

            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div className="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 className="text-base font-semibold text-slate-950">
                            User Management
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            {users.length} user terdaftar.
                        </p>
                    </div>
                    <div className="rounded-lg bg-cyan-50 p-2.5 text-cyan-700 ring-1 ring-cyan-100">
                        <UsersIcon className="h-5 w-5" />
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th className="px-5 py-3">Name</th>
                                <th className="px-5 py-3">Email</th>
                                <th className="px-5 py-3">Role</th>
                                <th className="px-5 py-3">Branch</th>
                                <th className="px-5 py-3">Status</th>
                                <th className="px-5 py-3">Job Title</th>
                                <th className="px-5 py-3">Last Login</th>
                                <th className="px-5 py-3">Last Online</th>
                                <th className="px-5 py-3">Created</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {users.map((user) => (
                                <tr key={user.id}>
                                    <td className="px-5 py-4 font-medium text-slate-950">
                                        {user.name}
                                    </td>
                                    <td className="px-5 py-4 text-slate-600">
                                        {user.email}
                                    </td>
                                    <td className="px-5 py-4 text-slate-600">
                                        {user.role}
                                    </td>
                                    <td className="px-5 py-4 text-slate-600">
                                        {user.branch
                                            ? `${user.branch.name} (${user.branch.code})`
                                            : '-'}
                                    </td>
                                    <td className="px-5 py-4">
                                        <StatusBadge status={user.status} />
                                    </td>
                                    <td className="px-5 py-4 text-slate-600">
                                        {user.job_title ?? '-'}
                                    </td>
                                    <td className="px-5 py-4 text-slate-600">
                                        {user.last_login_at ?? '-'}
                                    </td>
                                    <td className="px-5 py-4 text-slate-600">
                                        {user.last_seen_at ?? '-'}
                                    </td>
                                    <td className="px-5 py-4 text-slate-600">
                                        {user.created_at ?? '-'}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
