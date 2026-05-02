<?php

namespace App\Services;

use App\Enums\ProcessingStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\DocumentExpiryReminder;
use App\Models\EmailNotification;
use App\Models\User;
use App\Models\VesselDocument;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DocumentReminderService
{
    public const EXPIRED_THRESHOLD = -1;

    public const UPCOMING_THRESHOLDS = [0, 7, 14, 30, 60, 90];

    public function __construct(private readonly AuditService $auditService) {}

    public function sendDueReminders(?CarbonInterface $date = null): array
    {
        $today = ($date ?: now())->copy()->startOfDay();
        $summary = [
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        VesselDocument::query()
            ->with(['vessel.branch', 'documentType'])
            ->where('processing_status', ProcessingStatus::Confirmed->value)
            ->where('is_permanent', false)
            ->whereNotNull('expires_at')
            ->orderBy('expires_at')
            ->chunkById(100, function ($documents) use ($today, &$summary): void {
                foreach ($documents as $document) {
                    $threshold = $this->thresholdFor($document, $today);

                    if ($threshold === null) {
                        continue;
                    }

                    $sentDate = $today->toDateString();

                    if (EmailNotification::query()
                        ->where('vessel_document_id', $document->id)
                        ->where('threshold_days', $threshold)
                        ->whereDate('sent_date', $sentDate)
                        ->exists()) {
                        $summary['skipped']++;

                        continue;
                    }

                    $recipients = $this->recipientsFor($document);
                    $subject = $this->subjectFor($document);
                    $body = $this->bodyFor($document, $threshold, $today);

                    $notification = EmailNotification::create([
                        'branch_id' => $document->vessel?->branch_id,
                        'vessel_document_id' => $document->id,
                        'recipients' => $recipients,
                        'cc' => [],
                        'subject' => $subject,
                        'body' => $body,
                        'threshold_days' => $threshold,
                        'sent_date' => $sentDate,
                        'status' => 'pending',
                    ]);

                    if (empty($recipients)) {
                        $notification->forceFill([
                            'status' => 'skipped',
                            'error' => 'Tidak ada user_cabang aktif untuk cabang ini.',
                        ])->save();
                        $summary['skipped']++;

                        continue;
                    }

                    try {
                        Mail::to($recipients)->send(
                            new DocumentExpiryReminder($document, $threshold, $subject, $body)
                        );

                        $notification->forceFill([
                            'status' => 'sent',
                            'sent_at' => now(),
                        ])->save();
                        $summary['sent']++;

                        $this->auditService->log('reminder.sent', $notification, null, [
                            'vessel_document_id' => $document->id,
                            'threshold_days' => $threshold,
                            'recipients' => $recipients,
                        ]);
                    } catch (Throwable $exception) {
                        $notification->forceFill([
                            'status' => 'failed',
                            'sent_at' => now(),
                            'error' => $exception->getMessage(),
                        ])->save();
                        $summary['failed']++;

                        $this->auditService->log('reminder.failed', $notification, null, [
                            'vessel_document_id' => $document->id,
                            'threshold_days' => $threshold,
                            'error' => $exception->getMessage(),
                        ]);
                    }
                }
            });

        return $summary;
    }

    private function thresholdFor(VesselDocument $document, CarbonInterface $today): ?int
    {
        $days = (int) $today->copy()->startOfDay()->diffInDays(
            $document->expires_at->copy()->startOfDay(),
            false
        );

        if ($days < 0) {
            return self::EXPIRED_THRESHOLD;
        }

        if (in_array($days, self::UPCOMING_THRESHOLDS, true)) {
            return $days;
        }

        return null;
    }

    private function recipientsFor(VesselDocument $document): array
    {
        return User::query()
            ->where('role', UserRole::UserCabang->value)
            ->where('status', UserStatus::Active->value)
            ->where('branch_id', $document->vessel?->branch_id)
            ->orderBy('email')
            ->pluck('email')
            ->values()
            ->all();
    }

    private function subjectFor(VesselDocument $document): string
    {
        $branch = $document->vessel?->branch?->name ?? '-';

        return "[Reminder] Dokumen Kapal Cabang {$branch} Akan/Sudah Expired";
    }

    private function bodyFor(VesselDocument $document, int $threshold, CarbonInterface $today): string
    {
        $expiresAt = $document->expires_at?->format('Y-m-d') ?? '-';
        $days = (int) $today->copy()->startOfDay()->diffInDays(
            $document->expires_at->copy()->startOfDay(),
            false
        );
        $timing = match (true) {
            $threshold === self::EXPIRED_THRESHOLD => abs($days).' hari lewat masa berlaku',
            $threshold === 0 => 'expired hari ini',
            default => "{$days} hari menuju expired",
        };

        return implode(PHP_EOL, [
            'Reminder dokumen kapal.',
            'Cabang: '.($document->vessel?->branch?->name ?? '-'),
            'Kapal: '.($document->vessel?->name ?? '-'),
            'Jenis Dokumen: '.($document->documentType?->name ?? '-'),
            'Nomor Surat: '.($document->letter_number ?? '-'),
            'Tanggal Expired: '.$expiresAt,
            'Status: '.$timing,
            'Silakan buka sistem Ship Document Monitoring untuk meninjau dokumen ini.',
        ]);
    }
}
