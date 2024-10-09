<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaCobrador extends Model
{
    protected $table = 'nomina_cobrador';

    public $timestamps = false;

    protected $casts = [
        'salario' => 'float',
        'dias_laborados' => 'int',
        'eps' => 'float',
        'ahorro' => 'float',
    ];

    protected $fillable = [
        'id',
        'cobrador_id',
        'nominaid_id',
    ];

    public function nomina()
    {
        return $this->belongsTo(Nomina::class);
    }

    public function cobrador()
    {
        return $this->belongsTo(User::class);
    }

    public function vales()
    {
        return $this->hasMany(Vale::class);
    }
}
