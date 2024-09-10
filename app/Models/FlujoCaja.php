<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FlujoCaja
 * 
 * @property int $id
 * @property string|null $descripcion
 * @property int|null $tipo
 * @property float|null $valor
 * @property Carbon|null $fecha
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class FlujoCaja extends Model
{
	protected $table = 'flujo_caja';

	protected $casts = [
		'tipo' => 'int',
		'valor' => 'float'
	];

	protected $dates = [
		'fecha'
	];

	protected $fillable = [
		'descripcion',
		'tipo',
		'valor',
		'fecha'
	];
}
