<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Responses\UsersResponse;
use App\Http\Responses\RolesPermisoResponse;
use App\Http\Responses\RolesDetallesResponse;
use App\Models\User;
use App\Models\ParametrosDetalle;
use App\Models\RolesPermiso;
use App\Models\RolesDetalle;
use Illuminate\Support\Facades\Hash as FacadesHash;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;

class UserController extends Controller
{

    public function postSignIn(Requests\PostSignInRequest $request)
    {
        $input = $request->all();
        $credentials = [
            'username' => $input['username'],
            'password' => $input['password'],
        ];

        $userDB = User::with('roles_detalles.roles_permiso')->where('Username', $credentials['username'])->first();

        if ($userDB) {
            $UserResponse = new UsersResponse;
            $UserResponse->Id = $userDB->id;
            $UserResponse->Nombres = $userDB->nombres;
            $UserResponse->Apellidos = $userDB->apellidos;
            $UserResponse->Telefono1 = $userDB->telefono1;
            $UserResponse->Telefono2 = $userDB->telefono2;
            $UserResponse->Login = $userDB->login;
            $UserResponse->Username = $userDB->username;
            $UserResponse->Rol = $userDB->rol;

            $rpr = [];
            //Buscamos los Roles Permisos
            $RolesPermisosDB = RolesPermiso::where([['rol_id', $userDB->rol], ['ver', true]])->get();
            foreach ($RolesPermisosDB as $rowRP) {
                $rdr = [];
                $RolesDetalleDB = RolesDetalle::where([['rol_permiso_id', $rowRP->id], ['user_id', $userDB->id]])->get();
                foreach ($RolesDetalleDB as $rowRD) {
                    $addRD = new RolesDetallesResponse;
                    $addRD->Editar = $rowRD->editar;
                    $addRD->Id = $rowRD->id;
                    $addRD->RolPermisoId = $rowRD->rol_permiso_id;
                    $addRD->UserId = $rowRD->user_id;
                    $addRD->Ver = $rowRD->ver;
                    array_push($rdr, $addRD);
                }

                $addRP = new RolesPermisoResponse;
                $addRP->Id = $rowRP->id;
                $addRP->Pantalla = $rowRP->pantalla;
                $addRP->RolId = $rowRP->rol_id;
                $addRP->Ver = $rowRP->ver;
                $addRP->roles_detalles = $rdr;
                array_push($rpr, $addRP);
            }

            $UserResponse->roles_permiso = $rpr;

            $rolesPermiso = RolesPermiso::where([['rol_id', $userDB->rol], ['ver', true]])->get();

            $hashed = FacadesHash::make($credentials['password']);
            if (FacadesHash::check($userDB->password, $hashed)) {
                $token = FacadesJWTAuth::fromUser($userDB);
                return response()->json(['token' => $token, 'id' => $userDB->id, 'rol' => $rolesPermiso, 'userData' => $UserResponse]);
            }
        }

        return response()->json(['Error' => 'Nombre de usuario y/o contraseña invalida'], 401);
    }

    public function getUsers()
	{
		$personas = User::get();
		return response()->json(['data' => $personas]);
	}

    public function saveUser(Requests\UserRequest $request)
	{
		$input = $request->all();
		$persona = new User;
		$persona->nombres = $input["nombres"];
		$persona->apellidos = $input["apellidos"];
		$persona->telefono1 = $input["telefono1"];
		$persona->telefono2 = $input["telefono2"];
		$persona->login = $input["login"];
		if ($input["login"]) {
			$persona->email = $input["email"];
			$persona->username = $input["username"];
			$persona->rol = $input["rol"];
			if ($request->has('password')) {
				$persona->password = $input["password"];
			}
		} else {
			$persona->ruta = $input["ruta"];
		}

		$persona->save();
		$personas = User::get();

		return response()->json(['data' => $personas]);
	}

	public function updateUser(Requests\UserRequest $request)
	{
		$input = $request->all();

		$persona = User::find($input["id"]);

		$persona->nombres = $input["nombres"];
		$persona->apellidos = $input["apellidos"];
		$persona->telefono1 = $input["telefono1"];
		$persona->telefono2 = $input["telefono2"];
		$persona->login = $input["login"];
		if ($input["login"]) {
			$persona->email = $input["email"];
			$persona->username = $input["username"];
			$persona->rol = $input["rol"];
			if ($request->has('password')) {
				$persona->password = $input["password"];
			}
		} else {
			$persona->ruta = $input["ruta"];
		}

		$persona->save();
		$personas = User::get();

		return response()->json(['data' => $personas]);
	}

    public function changePassword(Requests\PasswordResetRequest $request){
        $input = $request->all();

		$persona = User::find($input["userId"]);
		$persona->password = $input["password"];
		$persona->save();
    }

    public function __invoke(Request $request){}
}
