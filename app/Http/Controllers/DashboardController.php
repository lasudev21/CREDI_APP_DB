<?php

namespace App\Http\Controllers;

// use App\Cliente;
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

        $clientesNew = DB::select('call sp_getnewclientsmonth(?,?)', array($ageFrom, $ageTo));
        $clientesRen = DB::select('call sp_getrenovacionmonth(?,?)', array($ageFrom, $ageTo));

        foreach ($clientesNew  as $key => $value) {
            $cantNew = $cantNew + $value->value;
        }

        foreach ($clientesRen  as $key => $value) {
            $cantRen = $cantRen + $value->value;
        }

        return response()->json(['clientesNew' => $clientesNew, 'cantNew' => $cantNew, 'clientesRen' => $clientesRen, 'cantRen' => $cantRen ]);
    }
}
