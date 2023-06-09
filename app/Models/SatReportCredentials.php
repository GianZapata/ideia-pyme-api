<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatReportCredentials extends Model
{
    use HasFactory;

    protected $fillable = [
        'sat_report_id',
        'credentials_type',
        'rfc',
        'password',
        'cer_attachment_id',
        'key_attachment_id',
    ];
}
