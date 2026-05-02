<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VesselDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'vessel_id',
        'document_type_id',
        'uploaded_by',
        'confirmed_by',
        'confirmed_at',
        'upload_mode',
        'letter_number',
        'issued_at',
        'expires_at',
        'issuer',
        'is_permanent',
        'validity_status',
        'processing_status',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'ocr_text',
        'classification_confidence',
        'extraction_confidence',
        'extracted_values',
        'final_values',
        'warnings',
        'processing_error',
        'external_link',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'issued_at' => 'date',
            'expires_at' => 'date',
            'is_permanent' => 'boolean',
            'classification_confidence' => 'decimal:4',
            'extraction_confidence' => 'decimal:4',
            'extracted_values' => 'array',
            'final_values' => 'array',
            'warnings' => 'array',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function documentExtractions(): HasMany
    {
        return $this->hasMany(DocumentExtraction::class);
    }
}
