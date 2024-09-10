<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FlujoUtilidade
 * 
 * @property int $id
 * @property string|null $descripcion
 * @property float|null $valor
 * @property int|null $tipo
 * @property Carbon|null $fecha
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class FlujoUtilidade extends Model
{
	protected $table = 'flujo_utilidades';

	protected $casts = [
		'valor' => 'float',
		'tipo' => 'int'
	];

	protected $dates = [
		'fecha'
	];

	protected $fillable = [
		'descripcion',
		'valor',
		'tipo',
		'fecha'
	];
}
