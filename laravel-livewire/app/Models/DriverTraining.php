<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverTraining extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'name',
        'provider',
        'issued_at',
        'expires_at',
        'hours',
        'status',
        'certificate_url',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expires_at' => 'date',
        'hours' => 'integer',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
