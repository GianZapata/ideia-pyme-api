<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NominaReceptor extends Model
{
    use HasFactory;

    protected $table = 'nomina_receptores';

    protected $fillable = [
        'antiguedad',
        'clave_ent_fed',
        'curp',
        'departamento',
        'fecha_inicio_rel_laboral',
        'num_empleado',
        'num_seguridad_social',
        'periodicidad_pago',
        'puesto',
        'riesgo_puesto',
        'salario_base_cot_apor',
        'salario_diario_integrado',
        'sindicalizado',
        'tipo_contrato',
        'tipo_jornada',
        'tipo_regimen',
        'nomina_id',
    ];

}
