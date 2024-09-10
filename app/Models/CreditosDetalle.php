<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CreditosDetalle
 * 
 * @property int $id
 * @property int|null $credito_id
 * @property int|null $usuario_id
 * @property float|null $abono
 * @property Carbon|null $fecha_abono
 * @property bool $estado
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Credito|null $credito
 * @property User|null $user
 *
 * @package App\Models
 */
class CreditosDetalle extends Model
{
	protected $table = 'creditos_detalles';

	protected $casts = [
		'credito_id' => 'int',
		'usuario_id' => 'int',
		'abono' => 'float',
		'estado' => 'bool'
	];

	protected $dates = [
		'fecha_abono'
	];

	protected $fillable = [
		'credito_id',
		'usuario_id',
		'abono',
		'fecha_abono',
		'estado'
	];

	public function credito()
	{
		return $this->belongsTo(Credito::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'usuario_id');
	}
}
