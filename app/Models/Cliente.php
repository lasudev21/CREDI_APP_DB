<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Cliente
 * 
 * @property int $id
 * @property string|null $titular
 * @property string|null $cc_titular
 * @property string|null $fiador
 * @property string|null $cc_fiador
 * @property string|null $neg_titular
 * @property string|null $neg_fiador
 * @property string|null $dir_cobro
 * @property string|null $barrio_cobro
 * @property string|null $tel_cobro
 * @property string|null $dir_casa
 * @property string|null $barrio_casa
 * @property string|null $tel_casa
 * @property string|null $dir_fiador
 * @property string|null $barrio_fiador
 * @property string|null $tel_fiador
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property bool $estado
 * 
 * @property Collection|ClientesReferencia[] $clientes_referencias
 * @property Collection|Credito[] $creditos
 *
 * @package App\Models
 */
class Cliente extends Model
{
	protected $table = 'clientes';

	protected $casts = [
		'estado' => 'bool'
	];

	protected $fillable = [
		'titular',
		'cc_titular',
		'fiador',
		'cc_fiador',
		'neg_titular',
		'neg_fiador',
		'dir_cobro',
		'barrio_cobro',
		'tel_cobro',
		'dir_casa',
		'barrio_casa',
		'tel_casa',
		'dir_fiador',
		'barrio_fiador',
		'tel_fiador',
		'estado'
	];

	public function clientes_referencias()
	{
		return $this->hasMany(ClientesReferencia::class);
	}

	public function creditos()
	{
		return $this->hasMany(Credito::class);
	}
}
