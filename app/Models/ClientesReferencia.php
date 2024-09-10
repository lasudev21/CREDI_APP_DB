<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ClientesReferencia
 * 
 * @property int $id
 * @property int|null $cliente_id
 * @property string|null $nombre
 * @property string|null $direccion
 * @property string|null $barrio
 * @property string|null $telefono
 * @property string|null $parentesco
 * @property string|null $tipo_referencia
 * 
 * @property Cliente|null $cliente
 *
 * @package App\Models
 */
class ClientesReferencia extends Model
{
	protected $table = 'clientes_referencias';
	public $timestamps = false;

	protected $casts = [
		'cliente_id' => 'int'
	];

	protected $fillable = [
		'cliente_id',
		'nombre',
		'direccion',
		'barrio',
		'telefono',
		'parentesco',
		'tipo_referencia'
	];

	public function cliente()
	{
		return $this->belongsTo(Cliente::class);
	}
}
