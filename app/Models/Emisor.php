<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emisor extends Model
{
    use HasFactory;

    protected $table = 'emisores';

    protected $fillable = [
        'rfc',
        'nombre',
        'regimen_fiscal',
    ];
}
