<?php

namespace App\Contracts;

use App\Data\AiExtractionResult;
use App\Models\VesselDocument;

interface AiExtractionProviderInterface
{
    public function classifyDocument(string $ocrText): array;

    public function extractMetadata(VesselDocument $document, string $ocrText): AiExtractionResult;
}
