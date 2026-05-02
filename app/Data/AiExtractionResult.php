<?php

namespace App\Data;

class AiExtractionResult
{
    public function __construct(
        public readonly ?string $letterNumber,
        public readonly ?string $issuedAt,
        public readonly ?string $expiresAt,
        public readonly ?string $issuer,
        public readonly bool $isPermanent,
        public readonly float $confidence,
        public readonly array $warnings = [],
        public readonly array $rawResult = [],
    ) {}

    public function values(): array
    {
        return [
            'letter_number' => $this->letterNumber,
            'issued_at' => $this->issuedAt,
            'expires_at' => $this->expiresAt,
            'issuer' => $this->issuer,
            'is_permanent' => $this->isPermanent,
        ];
    }
}
