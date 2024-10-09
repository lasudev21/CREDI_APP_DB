<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\CreditosDetalle;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\CreditosRenovacione;
use App\Models\User;
use App\Models\FlujoCaja;
use App\Models\FlujoUtilidade;
use App\Models\Coteo;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CreditosController extends Controller
{
    public function getCredito($id)
    {
        $creditos = Credito::getCreditos($id);
        $cobrador = User::where([['ruta', $id], ['login', false]])->first();
        return response()->json(['data' => $creditos, 'cobrador' => $cobrador]);
    }

    public function getClientes()
    {
        $clientes = Cliente::where('estado', true)->orderBy('created_at', 'DESC')->get();
        return response()->json(['data' => $clientes]);
    }

    public function postCredito(Request $request)
    {
        $inputs = $request->all();
        $id = $inputs[0]['RutaId'];
        $ids = array_column($inputs, 'ClienteId');

        $status = false;
        if (Credito::CreditosUsuarioActivo($ids)) {
            return response()->json(['Error' => 'Uno de los clientes tiene actualmente un crédito activo'], 423);
        } else {
            $status = true;
        }

        return response()->json(['status' => $status]);
    }

    public function postRenovaciones($id)
    {
        $credito = Credito::with(['creditos_detalles' => function ($v) {
            $v->where('estado', true);
        }])->where('id', $id)->get();

        $valor_total = $credito[0]->mod_cuota * $credito[0]->mod_dias;
        $interes = $valor_total - $credito[0]->valor_prestamo;
        $total_pago_fecha = $credito[0]->creditos_detalles->sum('abono');

        if ($total_pago_fecha >= $interes) {
            return response()->json(['data' => true]);
        } else {
            return response()->json(['Error' => "El crédito no cumple con los requisitos minímos para renovar"], 424);
        }
    }

    public function postAbonos(Requests\addCuotasRequest $request)
    {
        $input = $request->all();
        $utilidad = 0;
        $orden = 1;

        error_log("Consulta créditos");
        $creditos = Credito::where([['ruta_id', $input['IdRuta']], ['activo', true]])
            ->with(['creditos_detalles' => function ($v) {
                $v->where('estado', true)
                    ->orderBy('fecha_abono', 'asc');
            }])
            ->get();

        DB::beginTransaction();

        //Eliminamos los creditos que requieran
        foreach ($input['Eliminar'] as $value) {
            $credito = Credito::find($value['Id']);
            $credito->activo = false;
            $credito->orden = null;
            $credito->eliminado = true;
            $credito->save();
        }

        //Agregamos los abonos
        error_log("Empieza abonos");
        foreach ($input['Abonos'] as $cuotas) {
            $estado = true;
            if ($cuotas['Nuevo']) {
                $credito = new Credito();
                $credito->cliente_id = $cuotas['Nuevo']['ClienteId'];
                $credito->ruta_id = $cuotas['Nuevo']['RutaId'];
                $credito->inicio_credito = $cuotas['Nuevo']['InicioCredito'];
                $credito->valor_prestamo = $cuotas['Nuevo']['ValorPrestamo'];
                $credito->mod_cuota = $cuotas['Nuevo']['ModCuota'];
                $credito->mod_dias = $cuotas['Nuevo']['ModDias'];
                $credito->observaciones = $cuotas['Nuevo']['Observaciones'];
                $credito->modalidad = $cuotas['Nuevo']['modalidad'];
                $credito->obs_dia = $cuotas['Nuevo']['ObsDia'];
                $credito->orden = $cuotas['Nuevo']['Orden'];
                $credito->mora = 0;
                $credito->save();
            } else {
                $credito = $creditos->find($cuotas['Id']);
            }

            //Eliminamos la ultima cuota si el usuario lo decide
            if ($cuotas["ReversarCuota"]) {
                $ultimoDetalle = $credito["creditos_detalles"]->last();
                if ($ultimoDetalle) {
                    $ultimoDetalle->delete();
                }
            }

            //Eliminamos la ultima cuota si el usuario lo decide
            if ($cuotas["Modificado"]) {
                $credito->valor_prestamo = $cuotas['ModificadoData']['valor_prestamo'];
                $credito->mod_cuota = $cuotas['ModificadoData']['mod_cuota'];
                $credito->mod_dias = $cuotas['ModificadoData']['mod_dias'];
            }

            //Agregamos las cuotas cuando el valor viene
            if ($cuotas['Cuota'] > 0) {
                $moraDias = ($cuotas['Cuota'] / $credito['mod_cuota']) - 1;
                $contar = $moraDias >= $credito['mora'] ? true : false;
                $cd = new CreditosDetalle;
                $cd->credito_id = $cuotas['Id'];
                $cd->abono = $cuotas['Cuota'];
                $cd->Fecha_abono = Carbon::now()->toDateString();
                $cd->usuario_id = $input['User'];
                $cd->estado = true;
                $cd->contar = $contar;
                $cd->save();
            }

            $cdS = $credito["creditos_detalles"];
            $VT = $credito["mod_cuota"] * $credito["mod_dias"];
            $VTF = array_sum(array_column($cdS->toArray(), 'abono')) + ($cuotas["Congelar"] == 0 ? $cuotas["Cuota"] : 0);

            if ($VT == $VTF)
                $estado = false;

            $credito['obs_dia'] = $cuotas['Obs'];
            $credito['observaciones'] = $cuotas['Observaciones'];

            $credito['activo'] = $estado;

            if (!$estado) {
                $credito['mora'] = 0;
                $credito['orden'] = null;
                $utilidad = $utilidad + ($VT - $credito['ValorPrestamo']);
            } else {
                $credito['orden'] = $orden;
                $orden++;

                if (!$cuotas['Nuevo']) {
                    if ($input['CalculoMoras']) {
                        if ($cuotas['Mora'] !== null) {
                            $credito['mora'] = $cuotas['Mora'];
                            $credito['congelar'] = $credito['congelar'] <= 0 ? 0 : $credito['congelar'] - 1;
                        } else {
                            if ($cuotas['Cuota'] == null) {
                                $credito['mora'] = $credito['congelar'] > 0 ? $credito['mora'] : $credito['mora'] + 1;
                                $credito['congelar'] = $credito['congelar'] <= 0 ? 0 : $credito['congelar'] - 1;
                            } else {
                                switch ($credito['modalidad']) {
                                    case 1:
                                        if ($cuotas['Cuota'] < $credito['mod_cuota'])
                                            $credito['mora'] = $credito['mora'];
                                        else {
                                            $moraDias = $cuotas['Cuota'] / $credito['mod_cuota'];
                                            if ($moraDias >= 2) {
                                                if ($credito['mora'] > 0) {
                                                    $credito['congelar'] = $credito['congelar'] > 0 ? $credito['congelar'] - ($moraDias - 1) : ($credito['mora'] - ($moraDias - 1));
                                                    $credito['congelar'] = $credito['congelar'] < 0 ? 0 : $credito['congelar'];
                                                    $credito['mora'] = (int)($credito['mora'] - ($moraDias - 1));
                                                } else {
                                                    $credito['mora'] = (int)($credito['mora'] - ($moraDias - 1));
                                                }
                                            }
                                        }
                                        break;
                                    case 2:
                                        if ($cuotas['Cuota'] < $credito['mod_cuota'])
                                            $credito['mora'] = $credito['mora'] + 1;
                                        else
                                            $credito['mora'] = 0;
                                        break;
                                }
                            }
                        }
                    }
                }
            }

            $credito->save();
        }

        //Si hay renovaciones reseteamos el credito
        foreach ($input['Renovaciones'] as $renovacion) {
            //Cambiamos de estado todas las renovaciones
            $lcr = CreditosRenovacione::where([['credito_id', $renovacion['Id']], ['estado', true]]);
            foreach ($lcr as $cr) {
                $cr['estado'] = false;
                $cr->save();
            }

            //Cambiamos el estado a los detalles
            $lcd = CreditosDetalle::where([['credito_id', $renovacion['Id']],  ['estado', true]]);
            foreach ($lcd as $cd) {
                $cd['estado'] = false;
                $cd['contar'] = false;
                $cd->save();
            }


            $newCR = new CreditosRenovacione;
            $newCR->credito_id = $renovacion['Id'];
            $newCR->excedente = $renovacion['Excedente'];
            $newCR->observaciones = $renovacion['Observaciones'];
            $newCR->estado = true;
            $newCR->fecha = Carbon::now()->toDateString();
            $newCR->save();

            $uC = $creditos->find($renovacion['Id']);
            $uC->modalidad = $renovacion['Modalidad'];
            $uC->mod_dias = $renovacion['Dias'];
            $uC->mod_cuota = $renovacion['Cuota'];
            $uC->valor_prestamo = $renovacion['ValorPrestamo'];
            $uC->mora = 0;
            $uC->save();
        }

        //Agregamos EL FLUJO DE CAJA
        if ($request['FlujoCaja']['Entrada'] > 0) {
            $fc = new FlujoCaja;
            $fc->descripcion = "Cobros ruta " . $request['IdRuta'];
            $fc->tipo = 1;
            $fc->valor = $request['FlujoCaja']['Entrada'];
            $fc->fecha = Carbon::now()->toDateString();
            $fc->save();
        }

        //Agregamos EL FLUJO DE CAJA
        if ($request['FlujoCaja']['Salida'] > 0) {
            $fc = new FlujoCaja;
            $fc->descripcion = "Prestamos ruta " . $request['IdRuta'];
            $fc->tipo = 2;
            $fc->valor = $request['FlujoCaja']['Salida'];
            $fc->fecha = Carbon::now()->toDateString();
            $fc->save();
        }

        //Agregamos la UTILIDAD
        $sumUtil = $request['FlujoCaja']['Utilidad'] + $utilidad;
        if ($sumUtil > 0) {
            $fu = new FlujoUtilidade;
            $fu->descripcion = "Utilidad ruta " . $request['IdRuta'];
            $fu->tipo = 1;
            $fu->valor = $sumUtil;
            $fu->fecha = Carbon::now()->toDateString();
            $fu->save();
        }


        //Agregamos los coteos
        if ($request['FlujoCaja']['Coteos'] > 0) {
            $user = User::select(DB::raw('id'))->where([['ruta', $input['IdRuta']], ['login', false]])->first();
            // $user = DB::table('users')->select(DB::raw('id'))->where([['ruta', $input['IdRuta']], ['login', false]])->first();
            // var_dump($user);

            $coteo = new Coteo();
            $coteo->coteos_dia = $request['FlujoCaja']['Coteos'];
            $coteo->id_ruta = $request['IdRuta'];
            $coteo->fecha = Carbon::now()->toDateString();
            $coteo->total_creditos_dia = Credito::where([['ruta_id', $request['IdRuta']], ['activo', true], ['modalidad', 1]])->count();
            $coteo->total_creditos_sem = Credito::where([['ruta_id', $request['IdRuta']], ['activo', true], ['modalidad', 2]])->count();
            $coteo->id_usuario = $user->id;
            $coteo->save();
        }

        DB::commit();

        $creditos = Credito::getCreditos($input['IdRuta']);
        $cobrador = User::where([['ruta', $input['IdRuta']], ['login', false]])->first();
        return response()->json(['data' => $creditos, 'cobrador' => $cobrador]);
    }

    public function __invoke(Request $request)
    {
        //
    }
}
