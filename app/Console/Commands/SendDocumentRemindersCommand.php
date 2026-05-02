<?php

namespace App\Console\Commands;

use App\Services\DocumentReminderService;
use Illuminate\Console\Command;

class SendDocumentRemindersCommand extends Command
{
    protected $signature = 'documents:send-reminders';

    protected $description = 'Send daily reminders for expired and soon-to-expire vessel documents.';

    public function handle(DocumentReminderService $reminderService): int
    {
        $summary = $reminderService->sendDueReminders();

        $this->info(sprintf(
            'Document reminders finished. Sent: %d, Failed: %d, Skipped: %d.',
            $summary['sent'],
            $summary['failed'],
            $summary['skipped'],
        ));

        return self::SUCCESS;
    }
}
