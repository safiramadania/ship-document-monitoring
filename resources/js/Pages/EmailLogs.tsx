import AppLayout from '@/Components/AppLayout';
import EmptyState from '@/Components/EmptyState';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { Button } from '@/Components/ui/button';
import { BranchSummary } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { Filter, Mail, Send } from 'lucide-react';
import { FormEvent, useState } from 'react';

type EmailLogRow = {
    id: number;
    sent_at?: string | null;
    sent_date?: string | null;
    branch?: BranchSummary | null;
    document?: {
        id: number;
        vessel?: string | null;
        document_type?: string | null;
        letter_number?: string | null;
    } | null;
    recipients: string[];
    cc: string[];
    subject: string;
    threshold_days?: number | null;
    status: string;
    error?: string | null;
};

type Props = {
    logs: EmailLogRow[];
    filters: {
        branch_id?: string | number | null;
        status?: string | null;
        threshold_days?: string | number | null;
        date?: string | null;
    };
    branches: BranchSummary[];
    statuses: string[];
    thresholds: number[];
};

export default function EmailLogs({
    logs,
    filters,
    branches,
    statuses,
    thresholds,
}: Props) {
    const flash = usePage().props.flash as
        | { success?: string; error?: string }
        | undefined;
    const [form, setForm] = useState({
        branch_id: String(filters.branch_id ?? ''),
        status: filters.status ?? '',
        threshold_days: String(filters.threshold_days ?? ''),
        date: filters.date ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        router.get(route('email-logs.index'), form, {
            preserveScroll: true,
            preserveState: false,
        });
    };

    const clear = () => {
        router.get(route('email-logs.index'), {}, {
            preserveScroll: true,
            preserveState: false,
        });
    };

    const runReminders = () => {
        router.post(route('email-logs.send-reminders'), {}, {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout title="Email Logs">
            <Head title="Email Logs" />
            <PageHeader
                actions={
                    <Button onClick={runReminders} type="button">
                        <Send className="h-4 w-4" />
                        Run Reminders
                    </Button>
                }
                description="Riwayat reminder email dokumen expired dan expiring soon."
                title="Email Logs"
            />

            {flash?.success && (
                <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {flash.success}
                </div>
            )}

            <form
                className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                onSubmit={submit}
            >
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <FilterSelect
                        label="Branch"
                        onChange={(value) =>
                            setForm((current) => ({
                                ...current,
                                branch_id: value,
                            }))
                        }
                        options={branches.map((branch) => ({
                            label: `${branch.name} (${branch.code})`,
                            value: String(branch.id),
                        }))}
                        value={form.branch_id}
                    />
                    <FilterSelect
                        label="Status"
                        onChange={(value) =>
                            setForm((current) => ({
                                ...current,
                                status: value,
                            }))
                        }
                        options={statuses.map((status) => ({
                            label: status,
                            value: status,
                        }))}
                        value={form.status}
                    />
                    <FilterSelect
                        label="Threshold"
                        onChange={(value) =>
                            setForm((current) => ({
                                ...current,
                                threshold_days: value,
                            }))
                        }
                        options={thresholds.map((threshold) => ({
                            label: thresholdLabel(threshold),
                            value: String(threshold),
                        }))}
                        value={form.threshold_days}
                    />
                    <div>
                        <label className="text-sm font-medium text-slate-700">
                            Sent Date
                        </label>
                        <input
                            className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    date: event.target.value,
                                }))
                            }
                            type="date"
                            value={form.date}
                        />
                    </div>
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
                            Notification History
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Menampilkan {logs.length} log reminder terbaru.
                        </p>
                    </div>
                    <div className="rounded-lg bg-cyan-50 p-2.5 text-cyan-700 ring-1 ring-cyan-100">
                        <Mail className="h-5 w-5" />
                    </div>
                </div>

                {logs.length === 0 ? (
                    <div className="p-6">
                        <EmptyState
                            description="Reminder yang terkirim, gagal, atau dilewati akan tampil di sini."
                            icon={Mail}
                            title="Email log kosong"
                        />
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200 text-sm">
                            <thead className="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th className="px-5 py-3">Sent At</th>
                                    <th className="px-5 py-3">Branch</th>
                                    <th className="px-5 py-3">Document</th>
                                    <th className="px-5 py-3">Recipients</th>
                                    <th className="px-5 py-3">Threshold</th>
                                    <th className="px-5 py-3">Status</th>
                                    <th className="px-5 py-3">Error</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {logs.map((log) => (
                                    <tr key={log.id}>
                                        <td className="px-5 py-4 text-slate-600">
                                            {log.sent_at ??
                                                log.sent_date ??
                                                '-'}
                                        </td>
                                        <td className="px-5 py-4 text-slate-600">
                                            {log.branch
                                                ? `${log.branch.name} (${log.branch.code})`
                                                : '-'}
                                        </td>
                                        <td className="px-5 py-4">
                                            <p className="font-medium text-slate-950">
                                                {log.document?.document_type ??
                                                    '-'}
                                            </p>
                                            <p className="mt-1 text-xs text-slate-500">
                                                {log.document?.vessel ?? '-'} -
                                                {log.document?.letter_number ??
                                                    '-'}
                                            </p>
                                        </td>
                                        <td className="max-w-sm px-5 py-4 text-slate-600">
                                            {log.recipients.join(', ') || '-'}
                                        </td>
                                        <td className="px-5 py-4 text-slate-600">
                                            {thresholdLabel(
                                                log.threshold_days ?? 0,
                                            )}
                                        </td>
                                        <td className="px-5 py-4">
                                            <StatusBadge status={log.status} />
                                        </td>
                                        <td className="max-w-sm px-5 py-4 text-rose-600">
                                            {log.error ?? '-'}
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

function thresholdLabel(threshold: number) {
    if (threshold === -1) {
        return 'Expired';
    }

    if (threshold === 0) {
        return 'Expires today';
    }

    return `${threshold} days`;
}
