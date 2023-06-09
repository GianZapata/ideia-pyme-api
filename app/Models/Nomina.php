<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nomina extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha_final_pago',
        'fecha_inicial_pago',
        'fecha_pago',
        'num_dias_pagados',
        'tipo_nomina',
        'total_deducciones',
        'total_otros_pagos',
        'total_percepciones',
        'version',
        'complemento_id',
    ];
}
