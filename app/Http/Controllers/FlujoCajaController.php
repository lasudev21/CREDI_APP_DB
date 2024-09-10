<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\FlujoCaja;
use App\Models\FlujoUtilidade;


class FlujoCajaController extends Controller
{

    public function getFlujoCaja()
    {
        $flujoCaja = FlujoCaja::orderBy('fecha', 'desc')->paginate(1000);


        $entrada = FlujoCaja::where('tipo', 1)->sum('valor');
        $salidas = FlujoCaja::where('tipo', 2)->sum('valor');
        $sum = $entrada - $salidas;

        return response()->json(['data' => $flujoCaja, 'sum' => $sum]);
    }

    public function postSaveFlujo(Requests\FlujoCajaRequest $request)
    {
        $input = $request->all();

        $flujoCaja = new FlujoCaja;
        $flujoCaja->descripcion = $input["Descripcion"];
        $flujoCaja->tipo = $input["Tipo"];
        $flujoCaja->valor = $input["Valor"];
        $flujoCaja->fecha = $input["Fecha"];

        $flujoCaja->save();

        return response()->json(['data' => []]);
    }

    public function __invoke(Request $request) {}
}
