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
    ];

    public function doctosRelacionados()
    {
        return $this->hasMany(DoctoRelacionado::class);
    }
}
