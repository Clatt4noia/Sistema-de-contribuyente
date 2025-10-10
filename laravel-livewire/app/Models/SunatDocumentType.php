<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SunatDocumentType extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'description',
        'sunat_name',
    ];
}
