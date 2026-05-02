<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\EmailNotification;
use App\Services\DocumentReminderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailLogsController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'status' => ['nullable', 'string', 'max:255'],
            'threshold_days' => ['nullable', 'integer'],
            'date' => ['nullable', 'date'],
        ]);

        $logs = EmailNotification::query()
            ->with(['branch:id,code,name', 'vesselDocument.vessel:id,branch_id,name', 'vesselDocument.documentType:id,code,name'])
            ->when($filters['branch_id'] ?? null, fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when(isset($filters['threshold_days']) && $filters['threshold_days'] !== '', fn ($query) => $query->where('threshold_days', $filters['threshold_days']))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('sent_date', $date))
            ->latest('created_at')
            ->limit(100)
            ->get();

        return Inertia::render('EmailLogs', [
            'logs' => $logs->map(fn (EmailNotification $notification): array => $this->logPayload($notification))->values(),
            'filters' => [
                'branch_id' => $filters['branch_id'] ?? '',
                'status' => $filters['status'] ?? '',
                'threshold_days' => $filters['threshold_days'] ?? '',
                'date' => $filters['date'] ?? '',
            ],
            'branches' => Branch::query()
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'statuses' => ['pending', 'sent', 'failed', 'skipped'],
            'thresholds' => [-1, 0, 7, 14, 30, 60, 90],
        ]);
    }

    public function sendReminders(DocumentReminderService $reminderService): RedirectResponse
    {
        $summary = $reminderService->sendDueReminders();

        return back()->with(
            'success',
            "Reminder selesai. Sent: {$summary['sent']}, Failed: {$summary['failed']}, Skipped: {$summary['skipped']}."
        );
    }

    private function logPayload(EmailNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'sent_at' => $notification->sent_at?->toDateTimeString(),
            'sent_date' => $notification->sent_date?->format('Y-m-d'),
            'branch' => $notification->branch ? [
                'id' => $notification->branch->id,
                'code' => $notification->branch->code,
                'name' => $notification->branch->name,
            ] : null,
            'document' => $notification->vesselDocument ? [
                'id' => $notification->vesselDocument->id,
                'vessel' => $notification->vesselDocument->vessel?->name,
                'document_type' => $notification->vesselDocument->documentType?->name,
                'letter_number' => $notification->vesselDocument->letter_number,
            ] : null,
            'recipients' => $notification->recipients ?: [],
            'cc' => $notification->cc ?: [],
            'subject' => $notification->subject,
            'threshold_days' => $notification->threshold_days,
            'status' => $notification->status,
            'error' => $notification->error,
        ];
    }
}
