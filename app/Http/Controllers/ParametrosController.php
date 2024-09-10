<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Parametro;
use App\Models\ParametrosDetalle;
use Illuminate\Support\Facades\DB;


class ParametrosController extends Controller
{

    public function getParametros()
    {
        $parametros = Parametro::with('parametros_detalles')->get();

        return response()->json(['data' => $parametros]);
    }

    public function getListaParametros($nombre)
    {
        $parametros = Parametro::with(['parametros_detalles' => function ($v) {
            $v->where('estado', true);
        }])->where('nombre', $nombre)->get();

        $parametrosL = array();
        foreach ($parametros as $key => $value) {
            foreach ($value->parametros_detalles as $key1 => $valueP) {
                $parametrosL[] = array(
                    'value' => $valueP['id_interno'],
                    'label' => $valueP['valor']
                );
            }
        }

        return response()->json($parametrosL);
    }



    public function getPeriodos()
    {
        $parametros = Parametro::with('parametros_detalles')->where('nombre', 'Modos de pago')->get();

        $rutas = array();
        foreach ($parametros as $key => $value) {
            foreach ($value->parametros_detalles as $key1 => $valueP) {
                $rutas[] = array(
                    'label' => $valueP['valor'],
                    'value' => $valueP['id_interno']
                );
            }
        }

        $obs_dias = Parametro::with(['parametros_detalles' => function ($v) {
            $v->where('estado', true);
        }])->where('nombre', 'Modalidades')->get();

        $dias = array();
        foreach ($obs_dias as $key => $value) {
            foreach ($value->parametros_detalles as $key1 => $valueP) {
                $dias[] = array(
                    'label' => $valueP['valor'],
                    'value' => $valueP['id_interno']
                );
            }
        }

        return response()->json(['data' => $rutas, 'dias' => $dias]);
    }

    public function postParametros(Requests\ParametroRequest $request)
    {
        $input = $request->all();

        $parametro = new ParametrosDetalle();
        $parametro->id_interno = $input['id_interno'];
        $parametro->valor = $input['valor'];
        $parametro->parametro_id = $input['parametro_id'];
        $parametro->estado = true;

        $parametro->save();
        return response()->json(['data' => $parametro]);
    }

    public function putParametros(Requests\ParametroPutRequest $request)
    {
        $input = $request->all();
        DB::beginTransaction();

        foreach ($input['cambios'] as $key => $value) {
            $parametro = ParametrosDetalle::find($value['id']);
            $parametro->valor = $value['valor'];
            $parametro->estado = $value['estado'];
            $parametro->save();
        }

        DB::commit();

        return response()->json(['data' => 'OK']);
    }

    public function __invoke(Request $request) {}
}
