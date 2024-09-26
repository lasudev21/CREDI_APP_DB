<?php

namespace App\Http\Controllers;

// use App\Cliente;

use App\Models\Cliente;
use App\Models\Coteo;
use App\Models\CreditosRenovacione;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getNewClientes()
    {
        $ageFrom = Carbon::now()->startOfMonth();
        $ageTo   = Carbon::now()->endOfMonth();
        $cantNew = 0;
        $cantRen = 0;

        $clientesNew = Cliente::select(DB::raw('DATE(created_at) as label'), DB::raw('COUNT(*) as value'))
            ->whereBetween('created_at', [$ageFrom, $ageTo])
            ->groupBy('label')
            ->get();
        $clientesRen = CreditosRenovacione::select(DB::raw('DATE(fecha)  as label'), DB::raw('COUNT(*) as value'))
            ->whereBetween('fecha', [$ageFrom, $ageTo])
            ->groupBy('label')
            ->get();

        $totalCoteo = Coteo::whereBetween('created_at', [$ageFrom, $ageTo])
            ->sum('coteos_dia');

        $coteosUsuarios = Coteo::select('id_usuario', User::raw('SUM(coteos_dia) as total_coteos'))
            ->whereBetween('fecha', [$ageFrom, $ageTo])  // Filtrar por fecha
            ->groupBy('id_usuario')  // Agrupar por id_usuario
            ->with(['user:id,nombres,apellidos']) // Relación con la tabla User (obtener nombre y apellidos)
            ->get();

        foreach ($clientesNew  as $key => $value) {
            $cantNew = $cantNew + $value->value;
        }

        foreach ($clientesRen  as $key => $value) {
            $cantRen = $cantRen + $value->value;
        }

        return response()->json(['clientesNew' => $clientesNew, 'cantNew' => $cantNew, 'clientesRen' => $clientesRen, 'cantRen' => $cantRen, 'totalCoteo' => $totalCoteo, 'coteosUsuarios' => $coteosUsuarios]);
    }
}
