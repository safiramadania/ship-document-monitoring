import AppLayout from '@/Components/AppLayout';
import EmptyState from '@/Components/EmptyState';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Head, router } from '@inertiajs/react';
import { Filter, History } from 'lucide-react';
import { FormEvent, useState } from 'react';

type AuditUser = {
    id: number;
    name: string;
    email: string;
    role?: string | null;
    branch?: {
        id: number;
        code: string;
        name: string;
    } | null;
};

type AuditLogRow = {
    id: number;
    timestamp?: string | null;
    user?: AuditUser | null;
    action: string;
    entity_label: string;
    entity_type?: string | null;
    entity_id?: number | null;
    summary: string;
    change_summary: string;
};

type Props = {
    logs: AuditLogRow[];
    filters: {
        action?: string | number | null;
        user_id?: string | number | null;
        entity_type?: string | number | null;
        date_from?: string | null;
        date_to?: string | null;
    };
    actions: string[];
    entityTypes: Array<{ value: string; label: string }>;
    users: Array<{ id: number; name: string; email: string }>;
};

export default function AuditLogs({
    logs,
    filters,
    actions,
    entityTypes,
    users,
}: Props) {
    const [form, setForm] = useState({
        action: String(filters.action ?? ''),
        user_id: String(filters.user_id ?? ''),
        entity_type: String(filters.entity_type ?? ''),
        date_from: filters.date_from ?? '',
        date_to: filters.date_to ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        router.get(route('audit-logs.index'), form, {
            preserveScroll: true,
            preserveState: false,
        });
    };

    const clear = () => {
        router.get(route('audit-logs.index'), {}, {
            preserveScroll: true,
            preserveState: false,
        });
    };

    return (
        <AppLayout title="Audit Logs">
            <Head title="Audit Logs" />
            <PageHeader
                description="Jejak aktivitas user, upload dokumen, OCR, konfirmasi, dan perubahan data."
                title="Audit Logs"
            />

            <form
                className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                onSubmit={submit}
            >
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <FilterSelect
                        label="Action"
                        onChange={(value) =>
                            setForm((current) => ({
                                ...current,
                                action: value,
                            }))
                        }
                        options={actions.map((action) => ({
                            label: action,
                            value: action,
                        }))}
                        value={form.action}
                    />
                    <FilterSelect
                        label="User"
                        onChange={(value) =>
                            setForm((current) => ({
                                ...current,
                                user_id: value,
                            }))
                        }
                        options={users.map((user) => ({
                            label: `${user.name} (${user.email})`,
                            value: String(user.id),
                        }))}
                        value={form.user_id}
                    />
                    <FilterSelect
                        label="Entity"
                        onChange={(value) =>
                            setForm((current) => ({
                                ...current,
                                entity_type: value,
                            }))
                        }
                        options={entityTypes}
                        value={form.entity_type}
                    />
                    <DateInput
                        label="From"
                        onChange={(value) =>
                            setForm((current) => ({
                                ...current,
                                date_from: value,
                            }))
                        }
                        value={form.date_from}
                    />
                    <DateInput
                        label="To"
                        onChange={(value) =>
                            setForm((current) => ({
                                ...current,
                                date_to: value,
                            }))
                        }
                        value={form.date_to}
                    />
                </div>
                <div className="mt-4 flex justify-end gap-3">
                    <Button onClick={clear} type="button" variant="outline">
                        Reset
                    </Button>
                    <Button type="submit">
                        <Filter className="h-4 w-4" />
                        Apply Filter
                    </Button>
                </div>
            </form>

            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div className="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 className="text-base font-semibold text-slate-950">
                            Audit Trail
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Menampilkan {logs.length} aktivitas terbaru.
                        </p>
                    </div>
                    <div className="rounded-lg bg-cyan-50 p-2.5 text-cyan-700 ring-1 ring-cyan-100">
                        <History className="h-5 w-5" />
                    </div>
                </div>

                {logs.length === 0 ? (
                    <div className="p-6">
                        <EmptyState
                            description="Belum ada aktivitas untuk filter yang dipilih."
                            icon={History}
                            title="Audit log kosong"
                        />
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200 text-sm">
                            <thead className="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th className="px-5 py-3">Timestamp</th>
                                    <th className="px-5 py-3">User</th>
                                    <th className="px-5 py-3">Role</th>
                                    <th className="px-5 py-3">Branch</th>
                                    <th className="px-5 py-3">Action</th>
                                    <th className="px-5 py-3">Entity</th>
                                    <th className="px-5 py-3">Summary</th>
                                    <th className="px-5 py-3">Before/After</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {logs.map((log) => (
                                    <tr key={log.id}>
                                        <td className="px-5 py-4 text-slate-600">
                                            {log.timestamp ?? '-'}
                                        </td>
                                        <td className="px-5 py-4">
                                            <p className="font-medium text-slate-950">
                                                {log.user?.name ?? 'System'}
                                            </p>
                                            <p className="mt-1 text-xs text-slate-400">
                                                {log.user?.email ?? '-'}
                                            </p>
                                        </td>
                                        <td className="px-5 py-4 text-slate-600">
                                            {log.user?.role ?? '-'}
                                        </td>
                                        <td className="px-5 py-4 text-slate-600">
                                            {log.user?.branch
                                                ? `${log.user.branch.name} (${log.user.branch.code})`
                                                : '-'}
                                        </td>
                                        <td className="px-5 py-4 font-medium text-cyan-700">
                                            {log.action}
                                        </td>
                                        <td className="px-5 py-4 text-slate-600">
                                            {log.entity_label} #{log.entity_id ?? '-'}
                                        </td>
                                        <td className="max-w-md px-5 py-4 text-slate-600">
                                            {log.summary}
                                        </td>
                                        <td className="max-w-md px-5 py-4 text-slate-500">
                                            {log.change_summary}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

function FilterSelect({
    label,
    onChange,
    options,
    value,
}: {
    label: string;
    onChange: (value: string) => void;
    options: Array<{ label: string; value: string }>;
    value: string;
}) {
    return (
        <div>
            <label className="text-sm font-medium text-slate-700">{label}</label>
            <select
                className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
                onChange={(event) => onChange(event.target.value)}
                value={value}
            >
                <option value="">All</option>
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        </div>
    );
}

function DateInput({
    label,
    onChange,
    value,
}: {
    label: string;
    onChange: (value: string) => void;
    value: string;
}) {
    return (
        <div>
            <label className="text-sm font-medium text-slate-700">{label}</label>
            <input
                className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
                onChange={(event) => onChange(event.target.value)}
                type="date"
                value={value}
            />
        </div>
    );
}
