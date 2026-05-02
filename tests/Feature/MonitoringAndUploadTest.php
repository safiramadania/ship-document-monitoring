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
use App\Jobs\ProcessVesselDocumentJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_targeted_upload_stores_file_privately_and_processes_fake_ocr(): void
    {
        Storage::fake('local');
        [$branch, $vessel] = $this->branchAndVessel();
        $documentType = $this->documentType();
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('targeted-uploads.store', [$vessel, $documentType]), [
                'document' => UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf'),
            ]);

        $document = VesselDocument::latest('id')->first();

        $response->assertRedirect(route('ocr.confirmation', $document));
        $this->assertSame($vessel->id, $document->vessel_id);
        $this->assertSame($documentType->id, $document->document_type_id);
        $this->assertSame($user->id, $document->uploaded_by);
        $this->assertSame(UploadMode::Targeted->value, $document->upload_mode);
        $this->assertSame(ProcessingStatus::NeedConfirmation->value, $document->processing_status);
        $this->assertSame(ValidityStatus::Unknown->value, $document->validity_status);
        $this->assertNotNull($document->ocr_text);
        $this->assertNotNull($document->extracted_values);
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_processing_job_sets_document_to_need_confirmation(): void
    {
        Storage::fake('local');
        [$branch, $vessel] = $this->branchAndVessel();
        $documentType = $this->documentType();
        $path = 'vessel-documents/test/certificate.pdf';
        Storage::disk('local')->put($path, 'private pdf content');
        $document = VesselDocument::create([
            'vessel_id' => $vessel->id,
            'document_type_id' => $documentType->id,
            'upload_mode' => UploadMode::Targeted->value,
            'processing_status' => ProcessingStatus::Pending->value,
            'validity_status' => ValidityStatus::Unknown->value,
            'file_path' => $path,
            'original_filename' => 'certificate.pdf',
            'mime_type' => 'application/pdf',
        ]);

        ProcessVesselDocumentJob::dispatchSync($document->id);

        $document->refresh();

        $this->assertSame(ProcessingStatus::NeedConfirmation->value, $document->processing_status);
        $this->assertNotNull($document->ocr_text);
        $this->assertNotEmpty($document->extracted_values);
        $this->assertDatabaseHas('document_extractions', [
            'vessel_document_id' => $document->id,
            'provider' => 'fake',
        ]);
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

    public function test_authorized_user_can_access_ocr_confirmation_page(): void
    {
        Storage::fake('local');
        [$branch, $vessel] = $this->branchAndVessel();
        $documentType = $this->documentType();
        $document = $this->processedDocument($vessel, $documentType);
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user)
            ->get(route('ocr.confirmation', $document))
            ->assertOk();
    }

    public function test_user_cabang_cannot_access_another_branch_ocr_confirmation_page(): void
    {
        Storage::fake('local');
        [$ownBranch] = $this->branchAndVessel('OWN');
        [, $otherVessel] = $this->branchAndVessel('OTHER');
        $documentType = $this->documentType();
        $document = $this->processedDocument($otherVessel, $documentType);
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $ownBranch->id,
        ]);

        $this->actingAs($user)
            ->get(route('ocr.confirmation', $document))
            ->assertForbidden();
    }

    public function test_user_cabang_cannot_filter_smart_upload_to_another_branch(): void
    {
        [$ownBranch] = $this->branchAndVessel('OWN');
        [$otherBranch] = $this->branchAndVessel('OTHER');
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $ownBranch->id,
        ]);

        $this->actingAs($user)
            ->get(route('uploads.smart', ['branch_id' => $otherBranch->id]))
            ->assertForbidden();
    }

    public function test_smart_upload_creates_document_and_classifies_document_type(): void
    {
        Storage::fake('local');
        [$branch, $vessel] = $this->branchAndVessel();
        $this->documentType();
        $nibType = DocumentType::create([
            'code' => 'NIB',
            'name' => 'NIB Document',
            'required' => true,
            'aliases' => ['nib'],
            'keywords' => ['nomor induk berusaha'],
            'sort_order' => 2,
        ]);
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('uploads.smart.store'), [
                'vessel_id' => $vessel->id,
                'document' => UploadedFile::fake()->create('nib-document.pdf', 100, 'application/pdf'),
            ]);

        $document = VesselDocument::latest('id')->first();

        $response->assertRedirect(route('ocr.confirmation', $document));
        $this->assertSame(UploadMode::Smart->value, $document->upload_mode);
        $this->assertSame(ProcessingStatus::NeedConfirmation->value, $document->processing_status);
        $this->assertSame($nibType->id, $document->document_type_id);
        $this->assertNotNull($document->classification_confidence);
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_user_cabang_cannot_smart_upload_for_another_branch_vessel(): void
    {
        Storage::fake('local');
        [$ownBranch] = $this->branchAndVessel('OWN');
        [, $otherVessel] = $this->branchAndVessel('OTHER');
        $this->documentType();
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $ownBranch->id,
        ]);

        $this->actingAs($user)
            ->post(route('uploads.smart.store'), [
                'vessel_id' => $otherVessel->id,
                'document' => UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf'),
            ])
            ->assertForbidden();
    }

    public function test_confirming_ocr_result_updates_document_and_validity_status(): void
    {
        Storage::fake('local');
        [$branch, $vessel] = $this->branchAndVessel();
        $documentType = $this->documentType();
        $document = $this->processedDocument($vessel, $documentType);
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user)
            ->put(route('ocr.confirmation.confirm', $document), [
                'document_type_id' => $documentType->id,
                'letter_number' => 'ABC-123',
                'issued_at' => now()->subMonth()->toDateString(),
                'expires_at' => now()->addDays(90)->toDateString(),
                'issuer' => 'KSOP TEST',
                'is_permanent' => false,
            ])
            ->assertRedirect(route('monitoring.index', [
                'branch_id' => $branch->id,
                'vessel_id' => $vessel->id,
            ]));

        $document->refresh();

        $this->assertSame(ProcessingStatus::Confirmed->value, $document->processing_status);
        $this->assertSame(ValidityStatus::Active->value, $document->validity_status);
        $this->assertSame('ABC-123', $document->letter_number);
        $this->assertSame($user->id, $document->confirmed_by);
        $this->assertNotNull($document->confirmed_at);

        $this->actingAs($user)
            ->get(route('monitoring.index', [
                'branch_id' => $branch->id,
                'vessel_id' => $vessel->id,
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('MonitoringKapal')
                ->where('rows.0.document.letter_number', 'ABC-123')
                ->where('rows.0.document.issuer', 'KSOP TEST'));
    }

    public function test_smart_upload_confirmation_saves_corrected_document_type(): void
    {
        Storage::fake('local');
        [$branch, $vessel] = $this->branchAndVessel();
        $firstType = $this->documentType();
        $correctedType = DocumentType::create([
            'code' => 'SPM',
            'name' => 'Surat Persetujuan Menyinggahi Pelabuhan',
            'required' => true,
            'sort_order' => 2,
        ]);
        $path = 'vessel-documents/test/smart.pdf';
        Storage::disk('local')->put($path, 'private pdf content');
        $document = VesselDocument::create([
            'vessel_id' => $vessel->id,
            'document_type_id' => $firstType->id,
            'upload_mode' => UploadMode::Smart->value,
            'processing_status' => ProcessingStatus::NeedConfirmation->value,
            'validity_status' => ValidityStatus::Unknown->value,
            'file_path' => $path,
            'original_filename' => 'smart.pdf',
            'mime_type' => 'application/pdf',
            'extracted_values' => [
                'letter_number' => 'SMART-1',
                'issued_at' => now()->subMonth()->toDateString(),
                'expires_at' => now()->addDays(30)->toDateString(),
                'issuer' => 'KSOP',
                'is_permanent' => false,
            ],
        ]);
        $user = User::factory()->create([
            'role' => UserRole::UserCabang->value,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user)
            ->put(route('ocr.confirmation.confirm', $document), [
                'document_type_id' => $correctedType->id,
                'letter_number' => 'SMART-1',
                'issued_at' => now()->subMonth()->toDateString(),
                'expires_at' => now()->addDays(30)->toDateString(),
                'issuer' => 'KSOP',
                'is_permanent' => false,
            ])
            ->assertRedirect();

        $document->refresh();

        $this->assertSame($correctedType->id, $document->document_type_id);
        $this->assertSame(ValidityStatus::ExpiringSoon->value, $document->validity_status);
        $this->assertSame(ProcessingStatus::Confirmed->value, $document->processing_status);
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

    private function processedDocument(Vessel $vessel, DocumentType $documentType): VesselDocument
    {
        $path = 'vessel-documents/test/certificate.pdf';
        Storage::disk('local')->put($path, 'private pdf content');

        $document = VesselDocument::create([
            'vessel_id' => $vessel->id,
            'document_type_id' => $documentType->id,
            'upload_mode' => UploadMode::Targeted->value,
            'processing_status' => ProcessingStatus::Pending->value,
            'validity_status' => ValidityStatus::Unknown->value,
            'file_path' => $path,
            'original_filename' => 'certificate.pdf',
            'mime_type' => 'application/pdf',
        ]);

        ProcessVesselDocumentJob::dispatchSync($document->id);

        return $document->refresh();
    }
}
