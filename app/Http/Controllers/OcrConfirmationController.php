<?php

namespace App\Http\Controllers;

use App\Enums\ProcessingStatus;
use App\Enums\UploadMode;
use App\Models\DocumentType;
use App\Models\VesselDocument;
use App\Services\AuditService;
use App\Support\DocumentStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OcrConfirmationController extends Controller
{
    public function __construct(private readonly AuditService $auditService) {}

    public function show(Request $request, VesselDocument $vesselDocument): Response
    {
        $this->authorizeDocumentAccess($request, $vesselDocument);

        $vesselDocument->loadMissing([
            'vessel.branch',
            'documentType',
            'uploadedBy:id,name',
            'confirmedBy:id,name',
        ]);

        return Inertia::render('OcrConfirmation', [
            'document' => $this->documentPayload($vesselDocument),
            'documentTypes' => DocumentType::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'code', 'name'])
                ->map(fn (DocumentType $documentType): array => [
                    'id' => $documentType->id,
                    'code' => $documentType->code,
                    'name' => $documentType->name,
                ]),
        ]);
    }

    public function confirm(Request $request, VesselDocument $vesselDocument): RedirectResponse
    {
        $this->authorizeDocumentAccess($request, $vesselDocument);

        abort_unless(
            in_array($vesselDocument->processing_status, [
                ProcessingStatus::NeedConfirmation->value,
                ProcessingStatus::Confirmed->value,
            ], true),
            422
        );

        $isSmartUpload = $vesselDocument->upload_mode === UploadMode::Smart->value;

        $validated = $request->validate([
            'document_type_id' => [
                Rule::requiredIf($isSmartUpload || ! $vesselDocument->document_type_id),
                'nullable',
                'exists:document_types,id',
            ],
            'letter_number' => ['nullable', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'is_permanent' => ['required', 'boolean'],
        ]);

        $oldValues = $vesselDocument->only([
            'document_type_id',
            'letter_number',
            'issued_at',
            'expires_at',
            'issuer',
            'is_permanent',
            'validity_status',
            'processing_status',
        ]);

        $documentTypeId = $isSmartUpload
            ? (int) $validated['document_type_id']
            : $vesselDocument->document_type_id;
        $expiresAt = $validated['is_permanent'] ? null : ($validated['expires_at'] ?? null);
        $finalValues = [
            'document_type_id' => $documentTypeId,
            'letter_number' => $validated['letter_number'] ?? null,
            'issued_at' => $validated['issued_at'] ?? null,
            'expires_at' => $expiresAt,
            'issuer' => $validated['issuer'] ?? null,
            'is_permanent' => (bool) $validated['is_permanent'],
        ];

        $vesselDocument->forceFill([
            ...$finalValues,
            'validity_status' => DocumentStatus::fromValues((bool) $validated['is_permanent'], $expiresAt),
            'final_values' => $finalValues,
            'processing_status' => ProcessingStatus::Confirmed->value,
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ])->save();

        $this->auditService->log(
            action: 'document.confirmed',
            entity: $vesselDocument,
            oldValues: $oldValues,
            newValues: $vesselDocument->only([
                'document_type_id',
                'letter_number',
                'issued_at',
                'expires_at',
                'issuer',
                'is_permanent',
                'validity_status',
                'processing_status',
            ]),
            request: $request,
        );

        $vesselDocument->loadMissing('vessel');

        return redirect()
            ->route('monitoring.index', [
                'branch_id' => $vesselDocument->vessel->branch_id,
                'vessel_id' => $vesselDocument->vessel_id,
            ])
            ->with('success', 'Data dokumen berhasil disimpan ke monitoring.');
    }

    private function authorizeDocumentAccess(Request $request, VesselDocument $vesselDocument): void
    {
        $vesselDocument->loadMissing('vessel');

        abort_unless($request->user()->canAccessBranch($vesselDocument->vessel->branch_id), 403);
    }

    private function documentPayload(VesselDocument $document): array
    {
        $mimeType = (string) $document->mime_type;

        return [
            'id' => $document->id,
            'upload_mode' => $document->upload_mode,
            'processing_status' => $document->processing_status,
            'processing_error' => $document->processing_error,
            'letter_number' => $document->letter_number,
            'issued_at' => $document->issued_at?->format('Y-m-d'),
            'expires_at' => $document->expires_at?->format('Y-m-d'),
            'issuer' => $document->issuer,
            'is_permanent' => (bool) $document->is_permanent,
            'validity_status' => $document->validity_status,
            'ocr_text' => $document->ocr_text,
            'classification_confidence' => $document->classification_confidence,
            'extraction_confidence' => $document->extraction_confidence,
            'extracted_values' => $document->extracted_values ?: [],
            'final_values' => $document->final_values ?: [],
            'warnings' => $document->warnings ?: [],
            'original_filename' => $document->original_filename,
            'mime_type' => $document->mime_type,
            'is_pdf' => str_contains($mimeType, 'pdf'),
            'is_image' => str_starts_with($mimeType, 'image/'),
            'preview_url' => filled($document->file_path) ? route('documents.preview', $document) : null,
            'download_url' => filled($document->file_path) ? route('documents.download', $document) : null,
            'vessel' => [
                'id' => $document->vessel->id,
                'name' => $document->vessel->name,
                'code' => $document->vessel->code,
            ],
            'branch' => [
                'id' => $document->vessel->branch->id,
                'code' => $document->vessel->branch->code,
                'name' => $document->vessel->branch->name,
            ],
            'document_type' => $document->documentType ? [
                'id' => $document->documentType->id,
                'code' => $document->documentType->code,
                'name' => $document->documentType->name,
            ] : null,
            'uploaded_by' => $document->uploadedBy?->name,
            'confirmed_by' => $document->confirmedBy?->name,
            'confirmed_at' => $document->confirmed_at?->toDateTimeString(),
        ];
    }
}
