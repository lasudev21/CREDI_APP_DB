<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Credito
 *
 * @property int $id
 * @property int|null $orden
 * @property string|null $obs_dia
 * @property int|null $cliente_id
 * @property int $ruta_id
 * @property int|null $mora
 * @property float|null $cuotas_pagas
 * @property float $valor_prestamo
 * @property float|null $mod_cuota
 * @property string|null $mod_dias
 * @property string|null $observaciones
 * @property int $modalidad
 * @property bool|null $activo
 * @property bool|null $eliminado
 * @property Carbon|null $inicio_credito
 * @property int|null $congelar
 *
 * @property Cliente|null $cliente
 * @property Collection|CreditosDetalle[] $creditos_detalles
 * @property Collection|CreditosRenovacione[] $creditos_renovaciones
 *
 * @package App\Models
 */
class Credito extends Model
{
    protected $table = 'creditos';
    public $timestamps = false;

    protected $casts = [
        'orden' => 'int',
        'cliente_id' => 'int',
        'ruta_id' => 'int',
        'mora' => 'int',
        'cuotas_pagas' => 'float',
        'valor_prestamo' => 'float',
        'mod_cuota' => 'float',
        'modalidad' => 'int',
        'activo' => 'bool',
        'eliminado' => 'bool',
        'congelar' => 'int'
    ];

    protected $dates = [
        'inicio_credito'
    ];

    protected $fillable = [
        'orden',
        'obs_dia',
        'cliente_id',
        'ruta_id',
        'mora',
        'cuotas_pagas',
        'valor_prestamo',
        'mod_cuota',
        'mod_dias',
        'observaciones',
        'modalidad',
        'activo',
        'eliminado',
        'inicio_credito',
        'congelar'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function creditos_detalles()
    {
        return $this->hasMany(CreditosDetalle::class);
    }

    public function creditos_renovaciones()
    {
        return $this->hasMany(CreditosRenovacione::class);
    }

    public static function CreditosUsuarioActivo($id)
	{
		$res = false;
		$creditos = Credito::whereIn('cliente_id', $id)->where('activo', true)->get();
		if (count($creditos) > 0)
			$res = true;

		return $res;
	}

    public static function CreditoUsuarioActivo($id)
	{
		$res = false;
		$creditos = Credito::where('cliente_id', $id)->where('activo', true)->get();
		if (count($creditos) > 0)
			$res = true;

		return $res;
	}

    public static function getCreditos($id)
    {
        $creditos = Credito::with('cliente')
			->with('creditos_detalles.user')
            ->with(['creditos_detalles' => function ($v) {
                $v->where('estado', true);
            }])
            ->with(['creditos_renovaciones' => function ($c) {
                // $c->where('estado', true);
            }])
            ->where([['ruta_id', $id], ['activo', true]])->orderBy('orden', 'ASC')
            ->get();

        return $creditos;
    }
}
