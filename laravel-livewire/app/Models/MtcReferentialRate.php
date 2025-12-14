<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MtcReferentialRate extends Model
{
    protected $table = 'mtc_referential_rates';

    protected $fillable = [
        'source',
        'year',
        'route_key',
        'origin',
        'destination',
        'dv_partial_km',
        'dv_acum_km',
        'rate_soles_per_tm',
    ];

    protected $casts = [
        'year' => 'integer',
        'dv_partial_km' => 'decimal:2',
        'dv_acum_km' => 'decimal:2',
        'rate_soles_per_tm' => 'decimal:2',
    ];
}
