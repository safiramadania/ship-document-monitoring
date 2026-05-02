<?php

namespace App\Services\Ocr;

use App\Contracts\AiExtractionProviderInterface;
use App\Data\AiExtractionResult;
use App\Models\DocumentType;
use App\Models\VesselDocument;
use App\Support\IndonesianDateNormalizer;

class FakeAiExtractionProvider implements AiExtractionProviderInterface
{
    public function __construct(private readonly IndonesianDateNormalizer $dateNormalizer) {}

    public function classifyDocument(string $ocrText): array
    {
        $text = mb_strtolower($ocrText);
        $best = null;
        $bestScore = 0;
        $bestEvidence = [];

        DocumentType::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->each(function (DocumentType $documentType) use ($text, &$best, &$bestScore, &$bestEvidence): void {
                $terms = array_values(array_unique(array_filter([
                    $documentType->code,
                    $documentType->name,
                    ...($documentType->aliases ?: []),
                    ...($documentType->keywords ?: []),
                ])));

                $score = 0;
                $evidence = [];

                foreach ($terms as $term) {
                    $term = mb_strtolower((string) $term);

                    if ($term !== '' && str_contains($text, $term)) {
                        $score += max(10, min(40, mb_strlen($term)));
                        $evidence[] = $term;
                    }
                }

                if ($score > $bestScore) {
                    $best = $documentType;
                    $bestScore = $score;
                    $bestEvidence = $evidence;
                }
            });

        if (! $best) {
            $best = DocumentType::query()->where('required', true)->orderBy('sort_order')->first();
        }

        $confidence = $bestScore > 0 ? min(0.96, 0.55 + ($bestScore / 120)) : 0.34;

        return [
            'document_type_id' => $best?->id,
            'document_type_name' => $best?->name,
            'classification_confidence' => round($confidence, 4),
            'evidence' => $bestEvidence,
            'warnings' => $confidence < 0.6
                ? ['Confidence klasifikasi rendah. Mohon periksa jenis dokumen sebelum menyimpan.']
                : [],
        ];
    }

    public function extractMetadata(VesselDocument $document, string $ocrText): AiExtractionResult
    {
        $letterNumber = $this->match('/Nomor Surat:\s*(.+)$/mi', $ocrText);
        $issuedAt = $this->dateNormalizer->normalize($this->match('/Tanggal Terbit:\s*(.+)$/mi', $ocrText));
        $expiryText = $this->match('/Berlaku Sampai:\s*(.+)$/mi', $ocrText);
        $isPermanent = str_contains(mb_strtolower((string) $expiryText), 'permanen')
            || str_contains(mb_strtolower($this->match('/Status Permanen:\s*(.+)$/mi', $ocrText) ?? ''), 'ya');
        $expiresAt = $isPermanent ? null : $this->dateNormalizer->normalize($expiryText);
        $issuer = $this->match('/Instansi Penerbit:\s*(.+)$/mi', $ocrText);

        $warnings = [];

        if (! $letterNumber) {
            $warnings[] = 'Nomor surat tidak terbaca jelas.';
        }

        if (! $issuedAt) {
            $warnings[] = 'Tanggal terbit belum dapat dinormalisasi.';
        }

        if (! $expiresAt && ! $isPermanent) {
            $warnings[] = 'Tanggal berlaku tidak ditemukan. Status akan menjadi unknown sampai dikoreksi.';
        }

        return new AiExtractionResult(
            letterNumber: $letterNumber,
            issuedAt: $issuedAt,
            expiresAt: $expiresAt,
            issuer: $issuer,
            isPermanent: $isPermanent,
            confidence: empty($warnings) ? 0.91 : 0.72,
            warnings: $warnings,
            rawResult: [
                'letter_number' => $letterNumber,
                'issued_at' => $issuedAt,
                'expires_at' => $expiresAt,
                'issuer' => $issuer,
                'is_permanent' => $isPermanent,
                'source' => 'fake_ai_extraction',
            ],
        );
    }

    private function match(string $pattern, string $text): ?string
    {
        if (preg_match($pattern, $text, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }
}
