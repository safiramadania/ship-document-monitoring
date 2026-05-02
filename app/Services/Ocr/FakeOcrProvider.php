<?php

namespace App\Services\Ocr;

use App\Contracts\OcrProviderInterface;
use App\Data\OcrResult;
use App\Models\DocumentType;
use App\Models\VesselDocument;
use Carbon\CarbonImmutable;

class FakeOcrProvider implements OcrProviderInterface
{
    public function extractText(VesselDocument $document): OcrResult
    {
        $document->loadMissing(['vessel.branch', 'documentType']);

        $documentType = $document->documentType ?: $this->inferDocumentTypeFromFilename($document);
        $seed = abs(crc32(implode('|', [
            $document->id,
            $document->original_filename,
            $document->vessel?->name,
            $documentType?->name,
        ])));

        $issuedAt = CarbonImmutable::now()
            ->subMonths(2 + ($seed % 14))
            ->subDays($seed % 17);
        $validityMonths = $documentType?->validity_months ?: 12;
        $expiresAt = $issuedAt->addMonths($validityMonths);
        $issuer = $documentType?->agency ?: 'Direktorat Perkapalan dan Kepelautan';
        $letterNumber = sprintf(
            'ASDP/%s/%04d/%s',
            $documentType?->code ?: 'SMART',
            $document->id,
            $issuedAt->format('Y')
        );
        $isPermanent = (bool) ($documentType?->permanent_allowed && $seed % 7 === 0);

        $text = implode(PHP_EOL, [
            'HASIL OCR SIMULASI',
            'Nama Kapal: '.$document->vessel?->name,
            'Cabang: '.$document->vessel?->branch?->name,
            'Jenis Dokumen: '.($documentType?->name ?: 'Dokumen Kapal'),
            'Nomor Surat: '.$letterNumber,
            'Tanggal Terbit: '.$this->formatIndonesianDate($issuedAt),
            $isPermanent
                ? 'Berlaku Sampai: Permanen'
                : 'Berlaku Sampai: '.$this->formatIndonesianDate($expiresAt),
            'Instansi Penerbit: '.$issuer,
            'Status Permanen: '.($isPermanent ? 'Ya' : 'Tidak'),
            'Nama File: '.$document->original_filename,
        ]);

        return new OcrResult($text, 0.88, [
            'provider' => 'fake_ocr',
            'document_id' => $document->id,
            'generated_from' => 'deterministic fixture',
        ]);
    }

    private function inferDocumentTypeFromFilename(VesselDocument $document): ?DocumentType
    {
        $filename = strtolower((string) $document->original_filename);
        $types = DocumentType::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $types->first(function (DocumentType $type) use ($filename): bool {
            $terms = array_filter([
                $type->code,
                $type->name,
                ...($type->aliases ?: []),
                ...($type->keywords ?: []),
            ]);

            foreach ($terms as $term) {
                $term = strtolower((string) $term);

                if ($term !== '' && str_contains($filename, strtolower(str_replace(' ', '-', $term)))) {
                    return true;
                }

                if ($term !== '' && str_contains($filename, $term)) {
                    return true;
                }
            }

            return false;
        }) ?: $types->first();
    }

    private function formatIndonesianDate(CarbonImmutable $date): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $date->format('j').' '.$months[(int) $date->format('n')].' '.$date->format('Y');
    }
}
