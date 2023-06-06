<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complemento extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'fecha_timbrado',
        'no_certificado_sat',
        'sello_cfd',
        'sello_sat',
        'version',
        'factura_id',
        'rfc_prov_certif',
        'complemento_id'
    ];
}
