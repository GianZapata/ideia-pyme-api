<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NominaPercepciones extends Model
{
    use HasFactory;

    protected $table = 'nomina_percepciones';

    protected $fillable = [
        'total_exento',
        'total_gravado',
        'total_sueldos',
        'nomina_id',
    ];
}
