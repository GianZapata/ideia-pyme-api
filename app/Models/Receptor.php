<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receptor extends Model
{
    use HasFactory;

    protected $table = 'receptores';

    protected $fillable = [
        'rfc',
        'nombre',
        'uso_cfdi',
    ];
}
