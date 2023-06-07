<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubsidioEmpleo extends Model
{
    use HasFactory;

    protected $table = 'subsidio_empleos';

    protected $fillable = [
        'subsidio_causado',
        'nomina_otro_pago_id',
    ];
}
