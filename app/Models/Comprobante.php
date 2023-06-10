<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    use HasFactory;

    protected $table = 'comprobantes';

    protected $fillable = [
        'certificado',
        'condiciones_de_pago',
        'exportacion',
        'fecha',
        'folio',
        'forma_pago',
        'lugar_expedicion',
        'metodo_pago',
        'descuento',
        'moneda',
        'no_certificado',
        'sello',
        'serie',
        'sub_total',
        'tipo_cambio',
        'tipo_comprobante',
        'total',
        'version',
        'factura_id',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }


    public function conceptos()
    {
        return $this->hasMany(Concepto::class);
    }

}
