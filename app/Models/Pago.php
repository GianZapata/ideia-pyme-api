<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'monto_total_pagos',
        'fecha_pago',
        'forma_de_pago_p',
        'moneda_p',
        'tipo_cambio_p',
        'monto',
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
        'complemento_id'
    ];

    public function doctosRelacionados()
    {
        return $this->hasMany(DoctoRelacionado::class);
    }
}
