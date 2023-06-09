<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NominaOtroPago extends Model
{
    use HasFactory;

    protected $table = 'nomina_otro_pago';

    protected $fillable = [
        'clave',
        'concepto',
        'importe',
        'tipo_otro_pago',
        'nomina_id',
    ];
}
