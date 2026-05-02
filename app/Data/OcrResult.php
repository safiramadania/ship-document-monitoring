<?php

namespace App\Data;

class OcrResult
{
    public function __construct(
        public readonly string $text,
        public readonly float $confidence,
        public readonly array $rawResponse = [],
    ) {}
}
