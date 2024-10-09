<?php

namespace App\Http\Controllers;

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
        $nomina = Nomina::with('nomina_cobradores')->where([['anio', $inputs['year']], ['mes', $inputs['month']]])->get();
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
            $nomina->mes = $inputs['mes'] + 1;
            $nomina->save();

            foreach ($inputs['data'] as $nc) {
                $nominaCobrador = new NominaCobrador();
                $nominaCobrador->ahorro = $nc['ahorro'];
                $nominaCobrador->cobrador_id = $nc['cobrador_id'];
                $nominaCobrador->dias_laborados = $nc['dias_laborados'];
                $nominaCobrador->nomina_id = $nomina->id;
                $nominaCobrador->cobrador_id = $nc['cobrador_id'];
                $nominaCobrador->salario = $nc['salario'];
                $nominaCobrador->save();
                foreach ($nc['vales'] as $vales) {
                    $vale = new Vale();
                    $vale->valor = $vales['valor'];
                    $vale->descripcion = $vales['descripcion'];
                    $vale->fecha = $vales['fecha'];
                    $vale->nomina_cobrador_id = $nominaCobrador->id;
                    $vale->save();
                }
            }
        } else {
            $nomina = Nomina::find($inputs['nomina_id']);

            foreach ($inputs['data'] as $nc) {
                // var_dump($nc);
                if ($nc['id'] !== 0) {
                    $nominaCobrador = NominaCobrador::find($nc['id']);
                    $nominaCobrador->ahorro = $nc['ahorro'];
                    $nominaCobrador->cobrador_id = $nc['cobrador_id'];
                    $nominaCobrador->dias_laborados = $nc['dias_laborados'];
                    $nominaCobrador->salario = $nc['salario'];
                    $nominaCobrador->update();
                } else {
                    $nominaCobrador = new NominaCobrador();
                    $nominaCobrador->ahorro = $nc['ahorro'];
                    $nominaCobrador->cobrador_id = $nc['cobrador_id'];
                    $nominaCobrador->dias_laborados = $nc['dias_laborados'];
                    $nominaCobrador->nomina_id = $nomina->id;
                    $nominaCobrador->cobrador_id = $nc['cobrador_id'];
                    $nominaCobrador->salario = $nc['salario'];
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
                    } else {
                        $vale = new Vale();
                        $vale->valor = $vales['valor'];
                        $vale->descripcion = $vales['descripcion'];
                        $vale->fecha = $vales['fecha'];
                        $vale->nomina_cobrador_id = $nominaCobrador->id;
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
}
