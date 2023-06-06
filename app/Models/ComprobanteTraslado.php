<?php

namespace App\Models;

use App\Models\Comprobante;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComprobanteTraslado extends Model
{
    use HasFactory;

    protected $fillable = [
        'comprobante_id',
        'base',
        'impuesto',
        'tipo_factor',
        'tasa_o_cuota',
        'importe',
    ];

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class);
    }
}
