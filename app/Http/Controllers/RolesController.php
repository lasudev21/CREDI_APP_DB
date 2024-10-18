<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\RolesPermiso;
use App\Models\RolesDetalle;
use Illuminate\Support\Facades\DB;


class RolesController extends Controller
{

    public function getPermisoByRol($id)
    {
        $roles = RolesPermiso::where('rol_id', $id)->get();
        return response()->json(['data' => $roles]);
    }

    public function putPermisos(Request $request)
    {
        foreach ($request->all()['data'] as $input) {
            $rol = RolesPermiso::find($input['id']);
            $rol->ver = $input['ver'];
            $rol->save();
        }
        return response()->json(['data' => "Ok"]);
    }

    public function getAllViewsRole(Requests\ViewsRequest $request)
    {
        $input = $request->all();
        $roles = RolesPermiso::where([['rol_id', $input["idRol"]], ['ver', true]])->get();
        $permisos = RolesDetalle::where('user_id', $input["idUser"])->get();
        return response()->json(['data' => $roles, 'permisos' => $permisos]);
    }

    public function postRolesView(Requests\SavePermissionRequest $request)
    {
        $input = $request->all();
        DB::beginTransaction();
        foreach ($input['data'] as $row) {
            if ($row['idPermmision']) {
                $rolDet = RolesDetalle::find($row['idPermmision']);
                $rolDet->ver = $row['ver'];
                $rolDet->editar = $row['editar'];
                $rolDet->especial = $row['especial'];
                $rolDet->save();
            } else {
                $newRolDet = new RolesDetalle();
                $newRolDet->rol_permiso_id = $row['id_view'];
                $newRolDet->user_id = $input["idUser"];
                $newRolDet->ver = $row['ver'];
                $newRolDet->editar = $row['editar'];
                $newRolDet->especial = $row['especial'];
                $newRolDet->save();
            }
        }
        DB::commit();

        $permisos = RolesDetalle::where('user_id', $input["idUser"])->get();

        return response()->json(['data' => $permisos]);
    }

    public function __invoke(Request $request)
    {
        //
    }
}
