<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatReportPartners extends Model
{
    use HasFactory;

    protected $fillable = [
        'sat_report_id',
        'rfc',
        'name',
        'last_name',
        'second_last_name',
        'percentage',
    ];
}
