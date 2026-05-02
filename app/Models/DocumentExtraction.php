<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentExtraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'vessel_document_id',
        'provider',
        'raw_ocr_response',
        'classification_result',
        'extracted_result',
        'warnings',
    ];

    protected function casts(): array
    {
        return [
            'raw_ocr_response' => 'array',
            'classification_result' => 'array',
            'extracted_result' => 'array',
            'warnings' => 'array',
        ];
    }

    public function vesselDocument(): BelongsTo
    {
        return $this->belongsTo(VesselDocument::class);
    }
}
