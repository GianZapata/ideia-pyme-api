<?php

namespace App\Models;

use App\Models\Concepto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConceptoTraslado extends Model
{
    use HasFactory;

    protected $fillable = [
        'concepto_id',
        'base',
        'impuesto',
        'tipo_factor',
        'tasa_o_cuota',
        'importe',
    ];

    public function concepto()
    {
        return $this->belongsTo(Concepto::class);
    }
}
