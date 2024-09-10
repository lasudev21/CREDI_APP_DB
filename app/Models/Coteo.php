<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Coteo
 * 
 * @property int $id
 * @property Carbon $fecha
 * @property int|null $coteos_dia
 * @property int|null $id_ruta
 * @property int|null $id_usuario
 * @property Carbon|null $created_at
 * @property int|null $total_creditos_dia
 * @property int|null $total_creditos_sem
 * 
 * @property User|null $user
 *
 * @package App\Models
 */
class Coteo extends Model
{
	protected $table = 'coteos';
	public $timestamps = false;

	protected $casts = [
		'coteos_dia' => 'int',
		'id_ruta' => 'int',
		'id_usuario' => 'int',
		'total_creditos_dia' => 'int',
		'total_creditos_sem' => 'int'
	];

	protected $dates = [
		'fecha'
	];

	protected $fillable = [
		'fecha',
		'coteos_dia',
		'id_ruta',
		'id_usuario',
		'total_creditos_dia',
		'total_creditos_sem'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'id_usuario');
	}
}
