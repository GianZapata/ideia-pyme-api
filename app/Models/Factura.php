<?php

namespace App\Models;

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
        'month'
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
