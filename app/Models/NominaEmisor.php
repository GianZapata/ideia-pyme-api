<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NominaEmisor extends Model
{
    use HasFactory;

    protected $table = 'nomina_emisores';

    protected $fillable = [
        'nomina_id',
        'registro_patronal',
    ];

}
