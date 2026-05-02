<?php

namespace App\Jobs;

use App\Enums\ProcessingStatus;
use App\Models\VesselDocument;
use App\Services\Ocr\DocumentProcessingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessVesselDocumentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $vesselDocumentId) {}

    public function handle(DocumentProcessingService $processingService): void
    {
        $document = VesselDocument::findOrFail($this->vesselDocumentId);

        try {
            $processingService->process($document);
        } catch (Throwable $exception) {
            $document->forceFill([
                'processing_status' => ProcessingStatus::Failed->value,
                'processing_error' => $exception->getMessage(),
            ])->save();

            throw $exception;
        }
    }
}
