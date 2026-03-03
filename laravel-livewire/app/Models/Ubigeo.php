<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ubigeo extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'department',
        'province',
        'district',
        'reniec_code',
    ];

    public $incrementing = false;
    protected $primaryKey = 'code';
    protected $keyType = 'string';
}
