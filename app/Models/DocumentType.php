<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'agency',
        'category',
        'required',
        'permanent_allowed',
        'validity_months',
        'sort_order',
        'aliases',
        'keywords',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'permanent_allowed' => 'boolean',
            'aliases' => 'array',
            'keywords' => 'array',
        ];
    }

    public function vesselDocuments(): HasMany
    {
        return $this->hasMany(VesselDocument::class);
    }
}
