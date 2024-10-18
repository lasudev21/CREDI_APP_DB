<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use App\Models\FlujoUtilidade;
use App\Models\FlujoCaja;
use App\Models\Credito;
use App\Models\CreditosDetalle;
use App\Models\CreditosRenovacione;
use App\Models\Parametro;
use App\Models\SalidaCuenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportesController extends Controller
{
    public function getCoteos(Requests\CoteosRequest $request)
    {
        $input = $request->all();
        $FI = $input['fechaIni'];
        $FF = $input['fechaFin'];

        $cobradores = User::with(['coteos' => function ($query) use ($FI, $FF) {
            $query->whereBetween('fecha', [$FI, $FF]);
        }])
            ->where([["login", false]])->orderBy("ruta")->get();

        $utilidades = FlujoUtilidade::where('descripcion', 'like', '%Utilidad ruta %')
            ->whereBetween('fecha', [$FI, $FF])
            ->get();

        $recaudos = FlujoCaja::where('descripcion', 'like', '%Cobros ruta %')
            ->whereBetween('fecha', [$FI, $FF])
            ->get();

        $nuevos = Credito::whereBetween('inicio_credito', [$FI, $FF])->get();
        $renovaciones = CreditosRenovacione::with(['credito' => function ($query) {
            $query->select('id', 'ruta_id');
        }])->whereBetween('fecha', [$FI, $FF])->get();

        $parametros = Parametro::with(['parametros_detalles' => function ($v) {}])->where('nombre', 'Rutas')->get();

        $rutas = array();
        foreach ($parametros as $key => $value) {
            foreach ($value->parametros_detalles as $key1 => $valueP) {
                $rutas[] = array(
                    'label' => $valueP['valor'],
                    'value' => $valueP['id_interno']
                );
            }
        }

        return response()->json([
            'data' => $cobradores,
            'utilidades' => $utilidades,
            'recaudos' => $recaudos,
            'nuevos' => $nuevos,
            'renovaciones' => $renovaciones,
            'rutas' => $rutas
        ]);
    }

    public function getReporteCuentas(Request $request)
    {
        $inputs = $request->all();

        $anio = $inputs['year'];

        $primerDia = Carbon::createFromDate($anio, 1, 1)->startOfMonth();
        $ultimoDia = Carbon::createFromDate($anio, 12, 1)->endOfMonth();

        // Obtener los cobros agrupados por mes
        $cobros = CreditosDetalle::select(
            DB::raw('MONTH(fecha_abono) as mes'),
            DB::raw('SUM(abono) as total_cobros')
        )
            ->whereBetween('fecha_abono', [$primerDia->toDateString(), $ultimoDia->toDateString()])
            ->groupBy(DB::raw('MONTH(fecha_abono)'))
            ->get()
            ->keyBy('mes');

        // Obtener los préstamos agrupados por mes
        $creditos = Credito::select(
            DB::raw('MONTH(inicio_credito) as mes'),
            DB::raw('SUM(valor_prestamo) as total_creditos')
        )
            ->whereBetween('inicio_credito', [$primerDia->toDateString(), $ultimoDia->toDateString()])
            ->groupBy(DB::raw('MONTH(inicio_credito)'))
            ->get()
            ->keyBy('mes');

        // Obtener las renovaciones agrupadas por mes
        $renovaciones = CreditosRenovacione::select(
            DB::raw('MONTH(fecha) as mes'),
            DB::raw('SUM(excedente) as total_excedente')
        )
            ->whereBetween('fecha', [$primerDia->toDateString(), $ultimoDia->toDateString()])
            ->groupBy(DB::raw('MONTH(fecha)'))
            ->get()
            ->keyBy('mes');

        // Obtener las utilidades agrupadas por mes
        $utilidades = FlujoUtilidade::select(
            DB::raw('MONTH(fecha) as mes'),
            DB::raw('SUM(valor) as total_utilidad')
        )
            ->whereBetween('fecha', [$primerDia->toDateString(), $ultimoDia->toDateString()])
            ->where([['descripcion', 'like', '%Utilidad ruta%'], ['tipo', 1]])
            ->groupBy(DB::raw('MONTH(fecha)'))
            ->get()
            ->keyBy('mes');

        // Obtener los gastos agrupadas por mes
        $gastos = FlujoUtilidade::select(
            DB::raw('MONTH(fecha) as mes'),
            DB::raw('SUM(valor) as total_gastos')
        )
            ->whereBetween('fecha', [$primerDia->toDateString(), $ultimoDia->toDateString()])
            ->where([['descripcion', 'not like', '%SALIDA UTILIDAD%'], ['tipo', 2]])
            ->groupBy(DB::raw('MONTH(fecha)'))
            ->get()
            ->keyBy('mes');

        // Combinar los resultados
        $totalCierres = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            $total_creditos = $creditos->has($mes) ? $creditos[$mes]->total_creditos : 0;
            $total_excedente = $renovaciones->has($mes) ? $renovaciones[$mes]->total_excedente : 0;

            $totalCierres[] = [
                'id' => $mes,
                'mes' => ucfirst(Carbon::create()->locale('es')->month($mes)->translatedFormat('F')),
                'cobros' => $cobros->has($mes) ? $cobros[$mes]->total_cobros : 0,
                'prestamos' => $total_creditos + $total_excedente,
                'utilidad' => $utilidades->has($mes) ? $utilidades[$mes]->total_utilidad : 0,
                'gastos' => $gastos->has($mes) ? $gastos[$mes]->total_gastos : 0,
            ];
        }

        $totalCuentas = [];
        foreach ($totalCierres as $cierre) {
            $entradas = $cierre['utilidad'] - $cierre['gastos'];
            $salidas = SalidaCuenta::where([['anio', $anio], ['mes', $cierre["id"]]])->first();

            $totalCuentas[] = [
                'id' => $cierre["id"],
                'mes' => $cierre["mes"],
                'entradas' => $entradas,
                'salidas' => $salidas ? $salidas->salidas : 0,
            ];
        }

        return response()->json([
            'cierres' => $totalCierres,
            'cuentas' => $totalCuentas,
        ]);
    }

    public function postReporteCuentas(Request $request)
    {
        DB::beginTransaction();
        $inputs = $request->all();

        $salida_cuenta_total = SalidaCuenta::get();

        foreach ($inputs['data'] as $nc) {
            $existe = $salida_cuenta_total->filter(function ($item) use ($nc) {
                return $item->anio == $nc['anio'] && $item->mes == $nc['mes'];
            })->first();

            if ($existe) {
                $existe->salidas = $nc['salidas'];
                $existe->save();
            } else {
                $salidaCuenta = new SalidaCuenta();
                $salidaCuenta->anio = $nc['anio'];
                $salidaCuenta->mes = $nc['mes'];
                $salidaCuenta->salidas = $nc['salidas'];
                $salidaCuenta->save();
            }
        }

        DB::commit();

        return response()->json(['status' => true]);
    }

    public function __invoke(Request $request) {}
}
