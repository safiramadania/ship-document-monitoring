<?php

namespace Tests\Feature;

use App\Enums\ProcessingStatus;
use App\Enums\UploadMode;
use App\Enums\UserRole;
use App\Enums\ValidityStatus;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\DocumentType;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_upload_and_ocr_processing_create_audit_logs(): void
    {
        Storage::fake('local');
        [$branch, $vessel] = $this->branchAndVessel();
        $documentType = $this->documentType();
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user)
            ->post(route('targeted-uploads.store', [$vessel, $documentType]), [
                'document' => UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $document = VesselDocument::latest('id')->first();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'document.uploaded',
            'entity_type' => VesselDocument::class,
            'entity_id' => $document->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'document.ocr_processed',
            'entity_type' => VesselDocument::class,
            'entity_id' => $document->id,
        ]);
    }

    public function test_document_confirmation_creates_audit_log(): void
    {
        Storage::fake('local');
        [$branch, $vessel] = $this->branchAndVessel();
        $documentType = $this->documentType();
        $document = $this->documentNeedingConfirmation($vessel, $documentType);
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user)
            ->put(route('ocr.confirmation.confirm', $document), [
                'document_type_id' => $documentType->id,
                'letter_number' => 'CONF-1',
                'issued_at' => now()->subMonth()->toDateString(),
                'expires_at' => now()->addDays(90)->toDateString(),
                'issuer' => 'KSOP',
                'is_permanent' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'document.confirmed',
            'entity_type' => VesselDocument::class,
            'entity_id' => $document->id,
        ]);
    }

    public function test_user_approval_and_rejection_create_audit_logs(): void
    {
        $branch = Branch::create(['code' => 'BR', 'name' => 'Branch BR']);
        $superAdmin = User::factory()->superAdmin()->create();
        $pendingForApproval = User::factory()->pending()->create(['branch_id' => $branch->id]);
        $pendingForRejection = User::factory()->pending()->create(['branch_id' => $branch->id]);

        $this->actingAs($superAdmin)
            ->patch(route('users.approve', $pendingForApproval), [
                'role' => UserRole::UserCabang->value,
                'branch_id' => $branch->id,
            ])
            ->assertRedirect();

        $this->actingAs($superAdmin)
            ->patch(route('users.reject', $pendingForRejection), [
                'rejected_reason' => 'Data tidak lengkap.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'user.approved',
            'entity_type' => User::class,
            'entity_id' => $pendingForApproval->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'user.rejected',
            'entity_type' => User::class,
            'entity_id' => $pendingForRejection->id,
        ]);
    }

    public function test_audit_logs_page_is_restricted_to_admin_roles(): void
    {
        $userCabang = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($userCabang)
            ->get(route('audit-logs.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('audit-logs.index'))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->get(route('audit-logs.index'))
            ->assertOk();
    }

    public function test_user_cabang_dashboard_recent_changes_are_branch_scoped(): void
    {
        [$ownBranch, $ownVessel] = $this->branchAndVessel('OWN');
        [, $otherVessel] = $this->branchAndVessel('OTHER');
        $documentType = $this->documentType();
        $ownDocument = $this->documentNeedingConfirmation($ownVessel, $documentType);
        $otherDocument = $this->documentNeedingConfirmation($otherVessel, $documentType);
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $ownBranch->id,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'document.confirmed',
            'entity_type' => VesselDocument::class,
            'entity_id' => $ownDocument->id,
            'created_at' => now(),
        ]);
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'document.confirmed',
            'entity_type' => VesselDocument::class,
            'entity_id' => $otherDocument->id,
            'created_at' => now()->subMinute(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('dashboardData.recentDocumentEdits', 1)
                ->where('dashboardData.recentDocumentEdits.0.branch', 'Branch OWN'));
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

    private function documentNeedingConfirmation(Vessel $vessel, DocumentType $documentType): VesselDocument
    {
        return VesselDocument::create([
            'vessel_id' => $vessel->id,
            'document_type_id' => $documentType->id,
            'upload_mode' => UploadMode::Targeted->value,
            'processing_status' => ProcessingStatus::NeedConfirmation->value,
            'validity_status' => ValidityStatus::Unknown->value,
            'file_path' => 'vessel-documents/test/certificate.pdf',
            'original_filename' => 'certificate.pdf',
            'mime_type' => 'application/pdf',
            'extracted_values' => [
                'letter_number' => 'CERT-1',
                'issued_at' => now()->subMonth()->toDateString(),
                'expires_at' => now()->addYear()->toDateString(),
                'issuer' => 'KSOP',
                'is_permanent' => false,
            ],
        ]);
    }
}
