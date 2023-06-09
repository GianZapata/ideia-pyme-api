<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NominaDeducciones extends Model
{
    use HasFactory;

    protected $table = 'nomina_deducciones';

    protected $fillable = [
        'total_impuestos_retenidos',
        'total_otras_deducciones',
        'nomina_id',
    ];
}
