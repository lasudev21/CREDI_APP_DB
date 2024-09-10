<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ParametrosDetalle
 *
 * @property int $id
 * @property int|null $parametro_id
 * @property int|null $id_interno
 * @property string|null $valor
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Parametro|null $parametro
 *
 * @package App\Models
 */
class ParametrosDetalle extends Model
{
	protected $table = 'parametros_detalles';

	protected $casts = [
		'parametro_id' => 'int',
		'id_interno' => 'int',
        'estado' => 'bool'
	];

	protected $fillable = [
		'parametro_id',
		'id_interno',
		'valor'
	];

	public function parametro()
	{
		return $this->belongsTo(Parametro::class);
	}
}
