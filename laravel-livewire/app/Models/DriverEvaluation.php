<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'score',
        'evaluator',
        'comments',
        'evaluated_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'evaluated_at' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
