<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nomina extends Model
{
    protected $table = 'nomina';

    protected $casts = [
        'anio' => 'int',
        'semana' => 'int'
    ];

    protected $fillable = [
        'id',
    ];

    public function nomina_cobradores()
    {
        return $this->hasMany(NominaCobrador::class);
    }
}
