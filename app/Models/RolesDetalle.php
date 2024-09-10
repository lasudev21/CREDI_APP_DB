<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RolesDetalle
 *
 * @property int $id
 * @property int|null $rol_permiso_id
 * @property int|null $user_id
 * @property bool|null $ver
 * @property bool|null $editar
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class RolesDetalle extends Model
{
	protected $table = 'roles_detalles';

	protected $casts = [
		'rol_permiso_id' => 'int',
		'user_id' => 'int',
		'ver' => 'bool',
		'editar' => 'bool'
	];

	protected $fillable = [
		'rol_permiso_id',
		'user_id',
		'ver',
		'editar'
	];

    public function roles_permiso()
	{
		return $this->belongsTo(RolesPermiso::class, 'rol_permiso_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
