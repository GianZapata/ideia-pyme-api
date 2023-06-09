<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NominaDeduccion extends Model
{
    use HasFactory;

    protected $table = 'nomina_deduccion';

    protected $fillable = [
        'clave',
        'concepto',
        'importe',
        'tipo_deduccion',
        'nomina_deducciones_id',
    ];

}
