<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'sku',
        'name',
        'description',
        'unit_price',
        'sale_price',
        'currency',
        'tax_percentage',
        'metadata',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'metadata' => 'array',
    ];
}
