<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'truck_id',
        'maintenance_date',
        'maintenance_type',
        'cost',
        'status',
        'description',
        'notes',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }
}
