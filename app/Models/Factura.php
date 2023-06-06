<?php

namespace App\Models;

use App\Models\Comprobante;
use App\Models\Emisor;
use App\Models\Receptor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $fillable = [
        'emisor_id',
        'receptor_id',
        'uuid',
        'year',
        'month',
        'tipo'
    ];

    public function emisor()
    {
        return $this->belongsTo(Emisor::class);
    }

    public function receptor()
    {
        return $this->belongsTo(Receptor::class);
    }

    public function comprobante()
    {
        return $this->hasOne(Comprobante::class);
    }
}
