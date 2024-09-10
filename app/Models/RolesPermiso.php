<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RolesPermiso
 * 
 * @property int $id
 * @property int|null $rol_id
 * @property string|null $pantalla
 * @property bool|null $ver
 *
 * @package App\Models
 */
class RolesPermiso extends Model
{
	protected $table = 'roles_permiso';
	public $timestamps = false;

	protected $casts = [
		'rol_id' => 'int',
		'ver' => 'bool'
	];

	protected $fillable = [
		'rol_id',
		'pantalla',
		'ver'
	];
}
