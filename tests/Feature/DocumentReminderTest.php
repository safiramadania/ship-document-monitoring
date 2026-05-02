<?php

namespace Tests\Feature;

use App\Enums\ProcessingStatus;
use App\Enums\UploadMode;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\ValidityStatus;
use App\Mail\DocumentExpiryReminder;
use App\Models\Branch;
use App\Models\DocumentType;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselDocument;
use App\Services\DocumentReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DocumentReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_service_sends_expired_and_threshold_reminders_to_branch_users(): void
    {
        Mail::fake();
        [$branch, $vessel] = $this->branchAndVessel();
        $documentType = $this->documentType();
        $branchUser = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'status' => UserStatus::Active->value,
            'branch_id' => $branch->id,
            'email' => 'branch@example.test',
        ]);
        $expiredDocument = $this->confirmedDocument($vessel, $documentType, now()->subDay()->toDateString());
        $upcomingDocument = $this->confirmedDocument($vessel, $documentType, now()->addDays(7)->toDateString());

        $summary = app(DocumentReminderService::class)->sendDueReminders();

        $this->assertSame(2, $summary['sent']);
        Mail::assertSent(DocumentExpiryReminder::class, 2);
        Mail::assertSent(DocumentExpiryReminder::class, fn (DocumentExpiryReminder $mail) => $mail->hasTo($branchUser->email));
        $this->assertDatabaseHas('email_notifications', [
            'vessel_document_id' => $expiredDocument->id,
            'threshold_days' => -1,
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('email_notifications', [
            'vessel_document_id' => $upcomingDocument->id,
            'threshold_days' => 7,
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'reminder.sent',
        ]);
    }

    public function test_duplicate_reminder_for_same_document_threshold_and_date_is_skipped(): void
    {
        Mail::fake();
        [$branch, $vessel] = $this->branchAndVessel();
        $documentType = $this->documentType();
        User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'status' => UserStatus::Active->value,
            'branch_id' => $branch->id,
        ]);
        $document = $this->confirmedDocument($vessel, $documentType, now()->addDays(7)->toDateString());

        app(DocumentReminderService::class)->sendDueReminders();
        $summary = app(DocumentReminderService::class)->sendDueReminders();

        $this->assertSame(1, $summary['skipped']);
        $this->assertSame(1, $document->emailNotifications()->count());
        Mail::assertSent(DocumentExpiryReminder::class, 1);
    }

    public function test_reminder_command_runs(): void
    {
        Mail::fake();
        [$branch, $vessel] = $this->branchAndVessel();
        $documentType = $this->documentType();
        User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'status' => UserStatus::Active->value,
            'branch_id' => $branch->id,
        ]);
        $this->confirmedDocument($vessel, $documentType, now()->toDateString());

        $this->artisan('documents:send-reminders')
            ->expectsOutputToContain('Document reminders finished.')
            ->assertSuccessful();

        $this->assertDatabaseHas('email_notifications', [
            'threshold_days' => 0,
            'status' => 'sent',
        ]);
    }

    public function test_user_cabang_cannot_access_email_logs_page(): void
    {
        $userCabang = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($userCabang)
            ->get(route('email-logs.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('email-logs.index'))
            ->assertOk();
    }

    private function branchAndVessel(string $code = 'BRANCH'): array
    {
        $branch = Branch::create([
            'code' => $code,
            'name' => "Branch {$code}",
        ]);

        $vessel = Vessel::create([
            'branch_id' => $branch->id,
            'code' => "VESSEL-{$code}",
            'name' => "Vessel {$code}",
            'operator' => 'ASDP',
            'status' => 'active',
        ]);

        return [$branch, $vessel];
    }

    private function documentType(): DocumentType
    {
        return DocumentType::create([
            'code' => 'CERT',
            'name' => 'Certificate',
            'required' => true,
        ]);
    }

    private function confirmedDocument(Vessel $vessel, DocumentType $documentType, string $expiresAt): VesselDocument
    {
        return VesselDocument::create([
            'vessel_id' => $vessel->id,
            'document_type_id' => $documentType->id,
            'upload_mode' => UploadMode::Targeted->value,
            'processing_status' => ProcessingStatus::Confirmed->value,
            'validity_status' => ValidityStatus::Active->value,
            'letter_number' => 'CERT-REM',
            'issued_at' => now()->subYear()->toDateString(),
            'expires_at' => $expiresAt,
            'issuer' => 'KSOP',
            'is_permanent' => false,
        ]);
    }
}
