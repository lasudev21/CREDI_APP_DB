<?php

namespace App\Http\Controllers;

use App\Models\FlujoCaja;
use App\Models\Nomina;
use App\Models\NominaCobrador;
use App\Models\Parametro;
use App\Models\User;
use App\Models\Vale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NominaController extends Controller
{
    public function getNomina(Request $request)
    {
        $inputs = $request->all();
        $nomina = Nomina::with('nomina_cobradores')->where([['anio', $inputs['year']], ['semana', $inputs['week']]])->get();
        if ($nomina->count() === 0)
            return response()->json(['data' => []]);
        else {
            $nominaCobrador = NominaCobrador::with(['cobrador', 'vales', 'nomina'])->where('nomina_id', $nomina->first()->id)->get();
            return response()->json(['data' => $nominaCobrador]);
        }
    }

    public function postNomina(Request $request)
    {
        DB::beginTransaction();

        $inputs = $request->all();

        if ($inputs['nomina_id'] === 0) {
            $nomina = new Nomina();
            $nomina->anio = $inputs['anio'];
            $nomina->semana = $inputs['semana'];
            $nomina->save();

            foreach ($inputs['data'] as $nc) {
                $nominaCobrador = new NominaCobrador();
                $nominaCobrador->ahorro = $nc['ahorro'];
                $nominaCobrador->cobrador_id = $nc['cobrador_id'];
                $nominaCobrador->dias_laborados = $nc['dias_laborados'];
                $nominaCobrador->nomina_id = $nomina->id;
                $nominaCobrador->cobrador_id = $nc['cobrador_id'];
                $nominaCobrador->salario = $nc['salario'];
                $nominaCobrador->eps = $nc['eps'];
                $nominaCobrador->save();

                foreach ($nc['vales'] as $vales) {
                    //Creamos primero el registro en el flujo de caja
                    $fc = new FlujoCaja();
                    $fc->descripcion =  $vales['descripcion'];
                    $fc->tipo = 2;
                    $fc->valor = $vales['valor'];
                    $fc->fecha = $vales['fecha'];
                    $fc->save();

                    //Luego lo creamos el registro y asociamos al flujo de caja
                    $vale = new Vale();
                    $vale->valor = $vales['valor'];
                    $vale->descripcion = $vales['descripcion'];
                    $vale->fecha = $vales['fecha'];
                    $vale->nomina_cobrador_id = $nominaCobrador->id;
                    $vale->flujo_caja_id = $fc->id;
                    $vale->save();
                }
            }
        } else {
            $nomina = Nomina::find($inputs['nomina_id']);

            foreach ($inputs['data'] as $nc) {
                if ($nc['id'] !== 0) {
                    $nominaCobrador = NominaCobrador::find($nc['id']);
                    $nominaCobrador->ahorro = $nc['ahorro'];
                    $nominaCobrador->dias_laborados = $nc['dias_laborados'];
                    $nominaCobrador->salario = $nc['salario'];
                    $nominaCobrador->eps = $nc['eps'];
                    $nominaCobrador->update();
                } else {
                    $nominaCobrador = new NominaCobrador();
                    $nominaCobrador->ahorro = $nc['ahorro'];
                    $nominaCobrador->cobrador_id = $nc['cobrador_id'];
                    $nominaCobrador->dias_laborados = $nc['dias_laborados'];
                    $nominaCobrador->nomina_id = $nomina->id;
                    $nominaCobrador->cobrador_id = $nc['cobrador_id'];
                    $nominaCobrador->salario = $nc['salario'];
                    $nominaCobrador->eps = $nc['eps'];
                    $nominaCobrador->save();
                }

                foreach ($nc['vales'] as $vales) {
                    if ($vales['id'] !== 0) {
                        $vale = Vale::find($vales['id']);
                        $vale->valor = $vales['valor'];
                        $vale->descripcion = $vales['descripcion'];
                        $vale->fecha = $vales['fecha'];
                        $vale->nomina_cobrador_id = $nominaCobrador->id;
                        $vale->save();

                        //Actualiza el registro de flujo de caja
                        $fc = FlujoCaja::find($vale->flujo_caja_id);
                        $fc->valor = $vales['valor'];
                        $fc->save();
                    } else {
                        //Creamos primero el registro en el flujo de caja
                        $fc = new FlujoCaja();
                        $fc->descripcion =  $vales['descripcion'];
                        $fc->tipo = 2;
                        $fc->valor = $vales['valor'];
                        $fc->fecha = $vales['fecha'];
                        $fc->save();

                        //Luego lo creamos el registro y asociamos al flujo de caja
                        $vale = new Vale();
                        $vale->valor = $vales['valor'];
                        $vale->descripcion = $vales['descripcion'];
                        $vale->fecha = $vales['fecha'];
                        $vale->nomina_cobrador_id = $nominaCobrador->id;
                        $vale->flujo_caja_id = $fc->id;
                        $vale->save();
                    }
                }
            }
        }

        DB::commit();

        $nominaCobrador = NominaCobrador::with(['cobrador', 'vales', 'nomina'])->where('nomina_id', $nomina->id)->get();
        return response()->json(['data' => $nominaCobrador]);
    }

    public function getCobradores()
    {
        $personas = User::whereNotNull('ruta')->where('ruta', '>', '0')->get();
        $cobradores = array();
        foreach ($personas as $key => $value) {
            $cobradores[] = array(
                'label' => $value['nombres'] . " " . $value['apellidos'],
                'value' => $value['id']
            );
        }

        $parametros = Parametro::with(['parametros_detalles' => function ($v) {
            $v->where('estado', true);
        }])->where('nombre', "Fechas Reporte")->get();

        $fechas = array();
        foreach ($parametros as $key => $value) {
            foreach ($value->parametros_detalles as $key1 => $valueP) {
                $fechas[] = array(
                    'value' => $valueP['valor'],
                    'label' => $valueP['valor']
                );
            }
        }

        return response()->json(['cobradores' => $cobradores, 'fechas' => $fechas]);
    }

    public function postCobrador(Request $request)
    {
        $inputs = $request->all();

        $nominaCobrador = NominaCobrador::where([['cobrador_id', $inputs['cobrador_id']], ['nomina_id', $inputs['nomina_id']]])->get();
        if ($nominaCobrador->count() > 0)
            return response()->json(['Error' => 'El cobrador ya está adicionado a la nómina'], 423);
        else {
            $user = User::find($inputs['cobrador_id']);
            return response()->json(['data' => $user]);
        }
    }

    public function deleteVale(Request $request)
    {
        $inputs = $request->all();

        $vale = Vale::find($inputs['id']);
        if ($vale) {
            $vale->delete();
            $fc = FlujoCaja::find($vale->flujo_caja_id);
            $fc->delete();
            return response()->json(['status' => true]);
        }
    }
}
