<?php

namespace App\Services\Ocr;

use App\Contracts\AiExtractionProviderInterface;
use App\Contracts\OcrProviderInterface;
use App\Enums\ProcessingStatus;
use App\Enums\UploadMode;
use App\Models\DocumentExtraction;
use App\Models\VesselDocument;
use App\Services\AuditService;

class DocumentProcessingService
{
    public function __construct(
        private readonly OcrProviderInterface $ocrProvider,
        private readonly AiExtractionProviderInterface $aiProvider,
        private readonly AuditService $auditService,
    ) {}

    public function process(VesselDocument $document): VesselDocument
    {
        $document->forceFill([
            'processing_status' => ProcessingStatus::Processing->value,
            'processing_error' => null,
        ])->save();

        $document->loadMissing(['vessel.branch', 'documentType']);

        $ocrResult = $this->ocrProvider->extractText($document);
        $classificationResult = null;

        $document->forceFill([
            'ocr_text' => $ocrResult->text,
        ])->save();

        if ($document->upload_mode === UploadMode::Smart->value || ! $document->document_type_id) {
            $classificationResult = $this->aiProvider->classifyDocument($ocrResult->text);

            $document->forceFill([
                'document_type_id' => $classificationResult['document_type_id'] ?? null,
                'classification_confidence' => $classificationResult['classification_confidence'] ?? null,
            ])->save();
        }

        $document->refresh()->loadMissing(['vessel.branch', 'documentType']);

        $extractionResult = $this->aiProvider->extractMetadata($document, $ocrResult->text);
        $warnings = array_values(array_filter([
            ...($classificationResult['warnings'] ?? []),
            ...$extractionResult->warnings,
        ]));

        $document->forceFill([
            'extracted_values' => $extractionResult->values(),
            'extraction_confidence' => $extractionResult->confidence,
            'warnings' => $warnings,
            'processing_status' => ProcessingStatus::NeedConfirmation->value,
        ])->save();

        DocumentExtraction::create([
            'vessel_document_id' => $document->id,
            'provider' => 'fake',
            'raw_ocr_response' => $ocrResult->rawResponse + [
                'text' => $ocrResult->text,
                'confidence' => $ocrResult->confidence,
            ],
            'classification_result' => $classificationResult,
            'extracted_result' => $extractionResult->rawResult + [
                'confidence' => $extractionResult->confidence,
            ],
            'warnings' => $warnings,
        ]);

        $this->auditService->log('document.ocr_processed', $document, null, [
            'processing_status' => ProcessingStatus::NeedConfirmation->value,
            'classification_result' => $classificationResult,
            'extraction_confidence' => $extractionResult->confidence,
        ], $document->uploaded_by);

        return $document->refresh();
    }
}
