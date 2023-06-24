<?php

namespace App\Models;

use App\Models\Client;
use App\Models\SatReportPartners;
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
    ];

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function partners () {
        return $this->hasMany(SatReportPartners::class);
    }

}
