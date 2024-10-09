<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vale extends Model
{
    protected $table = 'vales';

    protected $casts = [
        'valor' => 'float',
    ];

    public $timestamps = false;

    protected $dates = [
        'fecha'
    ];

    protected $fillable = [
        'id',
        'nominaid_cobrador_id',
        'descripcion',
    ];

    public function nomina_cobrador()
    {
        return $this->belongsTo(NominaCobrador::class);
    }
}
