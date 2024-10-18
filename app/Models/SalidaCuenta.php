<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalidaCuenta extends Model
{
    protected $table = 'salida_cuenta';

    public $timestamps = false;

    protected $casts = [
        'id' => 'int',
        'anio' => 'int',
        'mes' => 'int',
        'salida' => 'float',
    ];
}
