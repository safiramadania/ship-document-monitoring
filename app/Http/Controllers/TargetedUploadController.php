<?php

namespace App\Http\Controllers;

use App\Enums\ProcessingStatus;
use App\Enums\UploadMode;
use App\Enums\ValidityStatus;
use App\Models\AuditLog;
use App\Models\DocumentType;
use App\Models\Vessel;
use App\Models\VesselDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TargetedUploadController extends Controller
{
    public function create(Request $request, Vessel $vessel, DocumentType $documentType): Response
    {
        $this->authorizeVesselAccess($request, $vessel);

        $vessel->load('branch:id,code,name');

        return Inertia::render('TargetedUpload', [
            'vessel' => [
                'id' => $vessel->id,
                'name' => $vessel->name,
                'code' => $vessel->code,
                'branch' => [
                    'id' => $vessel->branch->id,
                    'code' => $vessel->branch->code,
                    'name' => $vessel->branch->name,
                ],
            ],
            'documentType' => [
                'id' => $documentType->id,
                'code' => $documentType->code,
                'name' => $documentType->name,
            ],
        ]);
    }

    public function store(Request $request, Vessel $vessel, DocumentType $documentType): RedirectResponse
    {
        $this->authorizeVesselAccess($request, $vessel);

        $validated = $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
        ]);

        $file = $validated['document'];
        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $path = $file->storeAs(
            "vessel-documents/{$vessel->id}",
            Str::uuid().'.'.$extension,
            'local'
        );

        $vesselDocument = VesselDocument::create([
            'vessel_id' => $vessel->id,
            'document_type_id' => $documentType->id,
            'uploaded_by' => $request->user()->id,
            'upload_mode' => UploadMode::Targeted->value,
            'processing_status' => ProcessingStatus::Pending->value,
            'validity_status' => ValidityStatus::Unknown->value,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'document.uploaded',
            'entity_type' => VesselDocument::class,
            'entity_id' => $vesselDocument->id,
            'new_values' => [
                'vessel_id' => $vessel->id,
                'document_type_id' => $documentType->id,
                'upload_mode' => UploadMode::Targeted->value,
                'processing_status' => ProcessingStatus::Pending->value,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()
            ->route('ocr.confirmation', ['vessel_document_id' => $vesselDocument->id])
            ->with('success', 'Dokumen berhasil diunggah. Proses OCR akan diterapkan pada milestone berikutnya.');
    }

    private function authorizeVesselAccess(Request $request, Vessel $vessel): void
    {
        abort_unless($request->user()->canAccessBranch($vessel->branch_id), 403);
    }
}
