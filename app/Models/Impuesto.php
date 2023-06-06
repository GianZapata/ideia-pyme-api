<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Impuesto extends Model
{
    use HasFactory;

    protected $fillable = [
        'base',
        'impuesto',
        'tipo_factor',
        'tasa_o_cuota',
        'importe',
        'impuestoable_id',
        'impuestoable_type',
    ];

    public function impuestoable()
    {
        return $this->morphTo();
    }

}
