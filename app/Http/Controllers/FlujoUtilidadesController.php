<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\FlujoUtilidade;
use Carbon\Carbon;

class FlujoUtilidadesController extends Controller
{
    public function getFlujoUtilidades()
    {
        $FlujoUtilidades = FlujoUtilidade::orderBy('fecha', 'desc')->paginate(1000);

        $entrada = FlujoUtilidade::where('tipo', 1)->sum('valor');
        $salidas = FlujoUtilidade::where('tipo', 2)->sum('valor');
        $sum = $entrada - $salidas;

        return response()->json(['data' => $FlujoUtilidades, 'sum' => $sum]);
    }

    public function postSaveFlujoUtilidades(Requests\FlujoUtilidadesRequest $request)
    {
        $input = $request->all();

        $flujoCaja = new FlujoUtilidade;
        $flujoCaja->descripcion = $input["Descripcion"];
        $flujoCaja->valor = $input["Valor"];
        $flujoCaja->fecha = $input["Fecha"];
        $flujoCaja->tipo = $input["Tipo"];

        $flujoCaja->save();

        return response()->json(['data' => []]);
    }

    public function __invoke(Request $request) {}
}
