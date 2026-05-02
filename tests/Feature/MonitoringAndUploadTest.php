<?php

namespace Tests\Feature;

use App\Enums\ProcessingStatus;
use App\Enums\UploadMode;
use App\Enums\UserRole;
use App\Enums\ValidityStatus;
use App\Models\Branch;
use App\Models\DocumentType;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MonitoringAndUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_monitoring_page(): void
    {
        [$branch, $vessel] = $this->branchAndVessel();
        DocumentType::create([
            'code' => 'DOC-1',
            'name' => 'Required Document',
            'required' => true,
        ]);
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get(route('monitoring.index', [
                'branch_id' => $branch->id,
                'vessel_id' => $vessel->id,
            ]))
            ->assertOk();
    }

    public function test_user_cabang_cannot_filter_monitoring_to_another_branch(): void
    {
        [$ownBranch] = $this->branchAndVessel('OWN');
        [$otherBranch] = $this->branchAndVessel('OTHER');
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $ownBranch->id,
        ]);

        $this->actingAs($user)
            ->get(route('monitoring.index', ['branch_id' => $otherBranch->id]))
            ->assertForbidden();
    }

    public function test_targeted_upload_stores_file_privately_and_creates_pending_document(): void
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
            ->assertRedirect(route('ocr.confirmation', ['vessel_document_id' => VesselDocument::latest('id')->first()->id]));

        $document = VesselDocument::latest('id')->first();

        $this->assertSame($vessel->id, $document->vessel_id);
        $this->assertSame($documentType->id, $document->document_type_id);
        $this->assertSame($user->id, $document->uploaded_by);
        $this->assertSame(UploadMode::Targeted->value, $document->upload_mode);
        $this->assertSame(ProcessingStatus::Pending->value, $document->processing_status);
        $this->assertSame(ValidityStatus::Unknown->value, $document->validity_status);
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_user_cabang_cannot_upload_for_another_branch(): void
    {
        Storage::fake('local');
        [$ownBranch] = $this->branchAndVessel('OWN');
        [, $otherVessel] = $this->branchAndVessel('OTHER');
        $documentType = $this->documentType();
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $ownBranch->id,
        ]);

        $this->actingAs($user)
            ->post(route('targeted-uploads.store', [$otherVessel, $documentType]), [
                'document' => UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf'),
            ])
            ->assertForbidden();
    }

    public function test_protected_download_requires_branch_access(): void
    {
        Storage::fake('local');
        [$ownBranch] = $this->branchAndVessel('OWN');
        [, $otherVessel] = $this->branchAndVessel('OTHER');
        $documentType = $this->documentType();
        $path = 'vessel-documents/test/certificate.pdf';
        Storage::disk('local')->put($path, 'private pdf content');
        $document = VesselDocument::create([
            'vessel_id' => $otherVessel->id,
            'document_type_id' => $documentType->id,
            'file_path' => $path,
            'original_filename' => 'certificate.pdf',
            'mime_type' => 'application/pdf',
        ]);
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $ownBranch->id,
        ]);

        $this->actingAs($user)
            ->get(route('documents.download', $document))
            ->assertForbidden();
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
}
