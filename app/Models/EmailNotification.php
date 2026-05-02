<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'vessel_document_id',
        'recipients',
        'cc',
        'subject',
        'body',
        'threshold_days',
        'sent_date',
        'sent_at',
        'status',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'recipients' => 'array',
            'cc' => 'array',
            'sent_date' => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function vesselDocument(): BelongsTo
    {
        return $this->belongsTo(VesselDocument::class);
    }
}
