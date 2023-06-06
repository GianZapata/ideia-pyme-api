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
        'fecha',
        'lugar_expedicion',
        'moneda',
        'no_certificado',
        'sello',
        'sub_total',
        'tipo_comprobante',
        'total',
        'version',
        'folio',
        'forma_pago',
        'metodo_pago',
        'serie',
        'tipo_cambio',
        'exportacion',
        'condiciones_de_pago',
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
