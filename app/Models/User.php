<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

	protected $table = 'users';

	protected $casts = [
		'login' => 'bool',
		'ruta' => 'int',
		'rol' => 'int'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'nombres',
		'apellidos',
		'telefono1',
		'telefono2',
		'login',
		'username',
		'password',
		'ruta',
		'email',
		'rol'
	];

	public function coteos()
	{
		return $this->hasMany(Coteo::class, 'id_usuario');
	}

	public function creditos_detalles()
	{
		return $this->hasMany(CreditosDetalle::class, 'usuario_id');
	}

    public function roles_detalles()
	{
		return $this->hasMany(RolesDetalle::class);
	}

    public function getJWTIdentifier()
    {
    	return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
    	return [];
    }
}
