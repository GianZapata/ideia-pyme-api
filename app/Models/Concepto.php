<?php

namespace App\Models;

use App\Models\Impuesto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    use HasFactory;

    protected $fillable = [
        'comprobante_id',
        'clave_unidad',
        'clave_prod_serv',
        'no_identificacion',
        'cantidad',
        'unidad',
        'descripcion',
        'valor_unitario',
        'importe',
    ];

}
