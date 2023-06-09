<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctoRelacionado extends Model
{
    use HasFactory;

    protected $fillable = [
        'pago_id',
        'id_documento',
        'serie',
        'folio',
        'moneda_dr',
        'equivalencia_dr',
        'num_parcialidad',
        'imp_saldo_ant',
        'imp_pagado',
        'imp_saldo_insoluto',
        'objeto_imp_dr',
    ];

}
