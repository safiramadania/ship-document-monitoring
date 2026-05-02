<?php

namespace App\Contracts;

use App\Data\OcrResult;
use App\Models\VesselDocument;

interface OcrProviderInterface
{
    public function extractText(VesselDocument $document): OcrResult;
}
