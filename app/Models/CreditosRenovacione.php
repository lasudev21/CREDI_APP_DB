<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CreditosRenovacione
 * 
 * @property int $id
 * @property int|null $credito_id
 * @property string|null $observaciones
 * @property float|null $excedente
 * @property bool|null $estado
 * @property Carbon|null $fecha
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Credito|null $credito
 *
 * @package App\Models
 */
class CreditosRenovacione extends Model
{
	protected $table = 'creditos_renovaciones';

	protected $casts = [
		'credito_id' => 'int',
		'excedente' => 'float',
		'estado' => 'bool'
	];

	protected $dates = [
		'fecha'
	];

	protected $fillable = [
		'credito_id',
		'observaciones',
		'excedente',
		'estado',
		'fecha'
	];

	public function credito()
	{
		return $this->belongsTo(Credito::class);
	}
}
