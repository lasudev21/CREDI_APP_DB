<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Parametro
 * 
 * @property int $id
 * @property string|null $nombre
 * @property string|null $descripcion
 * @property bool|null $editable
 * @property string|null $icono
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|ParametrosDetalle[] $parametros_detalles
 *
 * @package App\Models
 */
class Parametro extends Model
{
	protected $table = 'parametros';

	protected $casts = [
		'editable' => 'bool'
	];

	protected $fillable = [
		'nombre',
		'descripcion',
		'editable',
		'icono'
	];

	public function parametros_detalles()
	{
		return $this->hasMany(ParametrosDetalle::class);
	}
}
