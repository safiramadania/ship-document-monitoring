import AppLayout from '@/Components/AppLayout';
import InputError from '@/Components/InputError';
import Modal from '@/Components/Modal';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { Button } from '@/Components/ui/button';
import { BranchSummary } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { CheckCircle2, UserCheck, XCircle } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

type PendingUser = {
    id: number;
    name: string;
    email: string;
    role: string;
    status: string;
    job_title?: string | null;
    created_at?: string | null;
    branch?: BranchSummary | null;
};

export default function UserApproval({
    pendingUsers = [],
    branches = [],
}: {
    pendingUsers: PendingUser[];
    branches: BranchSummary[];
}) {
    const flash = usePage().props.flash as
        | { success?: string; error?: string }
        | undefined;
    const [approvingUser, setApprovingUser] = useState<PendingUser | null>(
        null,
    );
    const [rejectingUser, setRejectingUser] = useState<PendingUser | null>(
        null,
    );

    const approveForm = useForm({
        role: 'user_cabang',
        branch_id: '',
    });

    const rejectForm = useForm({
        rejected_reason: '',
    });

    const openApprove = (user: PendingUser) => {
        approveForm.setData({
            role: 'user_cabang',
            branch_id: user.branch?.id ? String(user.branch.id) : '',
        });
        approveForm.clearErrors();
        setApprovingUser(user);
    };

    const openReject = (user: PendingUser) => {
        rejectForm.setData('rejected_reason', '');
        rejectForm.clearErrors();
        setRejectingUser(user);
    };

    const submitApprove: FormEventHandler = (event) => {
        event.preventDefault();

        if (!approvingUser) {
            return;
        }

        approveForm.patch(route('users.approve', approvingUser.id), {
            preserveScroll: true,
            onSuccess: () => setApprovingUser(null),
        });
    };

    const submitReject: FormEventHandler = (event) => {
        event.preventDefault();

        if (!rejectingUser) {
            return;
        }

        rejectForm.patch(route('users.reject', rejectingUser.id), {
            preserveScroll: true,
            onSuccess: () => setRejectingUser(null),
        });
    };

    return (
        <AppLayout title="User Approval">
            <Head title="User Approval" />
            <PageHeader
                description="Review registrasi baru, lalu approve dengan role dan branch yang sesuai."
                title="User Approval"
            />

            {flash?.success && (
                <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {flash.success}
                </div>
            )}

            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div className="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 className="text-base font-semibold text-slate-950">
                            Pending Users
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            {pendingUsers.length} akun menunggu keputusan.
                        </p>
                    </div>
                    <div className="rounded-lg bg-cyan-50 p-2.5 text-cyan-700 ring-1 ring-cyan-100">
                        <UserCheck className="h-5 w-5" />
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th className="px-5 py-3">Name</th>
                                <th className="px-5 py-3">Email</th>
                                <th className="px-5 py-3">Branch</th>
                                <th className="px-5 py-3">Job Title</th>
                                <th className="px-5 py-3">Registered</th>
                                <th className="px-5 py-3">Status</th>
                                <th className="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {pendingUsers.map((user) => (
                                <tr key={user.id}>
                                    <td className="px-5 py-4 font-medium text-slate-950">
                                        {user.name}
                                    </td>
                                    <td className="px-5 py-4 text-slate-600">
                                        {user.email}
                                    </td>
                                    <td className="px-5 py-4 text-slate-600">
                                        {user.branch
                                            ? `${user.branch.name} (${user.branch.code})`
                                            : '-'}
                                    </td>
                                    <td className="px-5 py-4 text-slate-600">
                                        {user.job_title ?? '-'}
                                    </td>
                                    <td className="px-5 py-4 text-slate-600">
                                        {user.created_at ?? '-'}
                                    </td>
                                    <td className="px-5 py-4">
                                        <StatusBadge status={user.status} />
                                    </td>
                                    <td className="px-5 py-4">
                                        <div className="flex justify-end gap-2">
                                            <Button
                                                onClick={() =>
                                                    openApprove(user)
                                                }
                                                size="sm"
                                                type="button"
                                            >
                                                <CheckCircle2 className="h-4 w-4" />
                                                Approve
                                            </Button>
                                            <Button
                                                onClick={() => openReject(user)}
                                                size="sm"
                                                type="button"
                                                variant="outline"
                                            >
                                                <XCircle className="h-4 w-4" />
                                                Reject
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {pendingUsers.length === 0 && (
                                <tr>
                                    <td
                                        className="px-5 py-10 text-center text-slate-500"
                                        colSpan={7}
                                    >
                                        Tidak ada user pending saat ini.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <Modal
                maxWidth="lg"
                onClose={() => setApprovingUser(null)}
                show={approvingUser !== null}
            >
                <form className="p-6" onSubmit={submitApprove}>
                    <h2 className="text-lg font-semibold text-slate-950">
                        Approve User
                    </h2>
                    <p className="mt-2 text-sm text-slate-500">
                        Pilih role dan branch sebelum mengaktifkan akun{' '}
                        {approvingUser?.email}.
                    </p>

                    <div className="mt-5 space-y-4">
                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Role
                            </label>
                            <select
                                className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
                                onChange={(event) =>
                                    approveForm.setData(
                                        'role',
                                        event.target.value,
                                    )
                                }
                                value={approveForm.data.role}
                            >
                                <option value="user_cabang">User Cabang</option>
                                <option value="admin">Admin</option>
                            </select>
                            <InputError
                                className="mt-2"
                                message={approveForm.errors.role}
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Branch
                            </label>
                            <select
                                className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 disabled:bg-slate-100"
                                disabled={approveForm.data.role === 'admin'}
                                onChange={(event) =>
                                    approveForm.setData(
                                        'branch_id',
                                        event.target.value,
                                    )
                                }
                                value={approveForm.data.branch_id}
                            >
                                <option value="">Pilih cabang</option>
                                {branches.map((branch) => (
                                    <option key={branch.id} value={branch.id}>
                                        {branch.name} ({branch.code})
                                    </option>
                                ))}
                            </select>
                            <InputError
                                className="mt-2"
                                message={approveForm.errors.branch_id}
                            />
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end gap-3">
                        <Button
                            onClick={() => setApprovingUser(null)}
                            type="button"
                            variant="outline"
                        >
                            Batal
                        </Button>
                        <Button disabled={approveForm.processing} type="submit">
                            Simpan Approval
                        </Button>
                    </div>
                </form>
            </Modal>

            <Modal
                maxWidth="lg"
                onClose={() => setRejectingUser(null)}
                show={rejectingUser !== null}
            >
                <form className="p-6" onSubmit={submitReject}>
                    <h2 className="text-lg font-semibold text-slate-950">
                        Reject User
                    </h2>
                    <p className="mt-2 text-sm text-slate-500">
                        Berikan alasan penolakan untuk {rejectingUser?.email}.
                    </p>

                    <div className="mt-5">
                        <label className="text-sm font-medium text-slate-700">
                            Rejected Reason
                        </label>
                        <textarea
                            className="mt-1 block min-h-28 w-full rounded-md border-slate-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
                            onChange={(event) =>
                                rejectForm.setData(
                                    'rejected_reason',
                                    event.target.value,
                                )
                            }
                            value={rejectForm.data.rejected_reason}
                        />
                        <InputError
                            className="mt-2"
                            message={rejectForm.errors.rejected_reason}
                        />
                    </div>

                    <div className="mt-6 flex justify-end gap-3">
                        <Button
                            onClick={() => setRejectingUser(null)}
                            type="button"
                            variant="outline"
                        >
                            Batal
                        </Button>
                        <Button disabled={rejectForm.processing} type="submit">
                            Reject User
                        </Button>
                    </div>
                </form>
            </Modal>
        </AppLayout>
    );
}
