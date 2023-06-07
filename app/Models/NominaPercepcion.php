<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NominaPercepcion extends Model
{
    use HasFactory;

    protected $table = 'nomina_percepcion';

    protected $fillable = [
        'clave',
        'concepto',
        'importe_exento',
        'importe_gravado',
        'tipo_percepcion',
        'nomina_percepciones_id',
    ];
}
