<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'total_tasks',
        'total_tasks_completed',
        'with_partners',
        'credentials_type',

        'name',
        'street',
        'house_number',
        'neighborhood',
        'municipality',
        'state',
        'postal_code',
        'country',
        'city',

    ];
}
