<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'regional',
        'email',
        'source_link',
    ];

    public function vessels(): HasMany
    {
        return $this->hasMany(Vessel::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
