<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\ClientesReferencia;
use App\Models\CreditosDetalle;
use App\Models\ParametrosDetalle;
use Illuminate\Support\Facades\Date;

class ClientesController extends Controller
{
    public function getClientes()
    {
        $clientes = Cliente::with('clientes_referencias')->orderBy('created_at', 'DESC')->get();
        $ca = Credito::where('activo', 1)->count();
        return response()->json(['data' => $clientes, 'creditosActivos' => $ca]);
    }

    public function saveCliente(Requests\ClienteRequest $request)
    {
        $input = $request->all();

        $cliente = new Cliente;
        $cliente->titular = $input["titular"];
        $cliente->cc_titular = $input["cc_titular"];
        $cliente->fiador = $input["fiador"];
        $cliente->cc_fiador = $input["cc_fiador"];
        $cliente->neg_titular = $input["neg_titular"];
        $cliente->neg_fiador = $input["neg_fiador"];
        $cliente->dir_cobro = $input["dir_cobro"];
        $cliente->tel_cobro = $input["tel_cobro"];
        $cliente->barrio_cobro = $input["barrio_cobro"];
        $cliente->dir_casa = $input["dir_casa"];
        $cliente->barrio_casa = $input["barrio_casa"];
        $cliente->tel_casa = $input["tel_casa"];
        $cliente->dir_fiador = $input["dir_fiador"];
        $cliente->barrio_fiador = $input["barrio_fiador"];
        $cliente->tel_fiador = $input["tel_fiador"];

        $cliente->save();

        foreach ($input["clientes_referencias"] as $key => $value) {
            $clienteRef = new ClientesReferencia;
            if ($value['id'] !== 0) {
                $clienteRef = ClientesReferencia::find($value["id"]);
            }
            $clienteRef->cliente_id = $cliente->id;
            $clienteRef->nombre = $value['nombre'];
            $clienteRef->direccion = $value['direccion'];
            $clienteRef->barrio = $value['barrio'];
            $clienteRef->tipo_referencia = $value['tipo_referencia'];
            $clienteRef->telefono = $value['telefono'];
            $clienteRef->parentesco = $value['parentesco'];
            $clienteRef->save();
        }

        $clientes = Cliente::with('clientes_referencias')->orderBy('created_at', 'DESC')->get();
        $ca = Credito::where('activo', 1)->count();
        return response()->json(['data' => $clientes, 'creditosActivos' => $ca]);
    }

    public function updateCliente(Requests\ClienteRequest $request)
    {
        $input = $request->all();

        $cliente = Cliente::find($input["id"]);

        $cliente->titular = $input["titular"];
        $cliente->cc_titular = $input["cc_titular"];
        $cliente->fiador = $input["fiador"];
        $cliente->cc_fiador = $input["cc_fiador"];
        $cliente->neg_titular = $input["neg_titular"];
        $cliente->neg_fiador = $input["neg_fiador"];
        $cliente->dir_cobro = $input["dir_cobro"];
        $cliente->tel_cobro = $input["tel_cobro"];
        $cliente->barrio_cobro = $input["barrio_cobro"];
        $cliente->dir_casa = $input["dir_casa"];
        $cliente->barrio_casa = $input["barrio_casa"];
        $cliente->tel_casa = $input["tel_casa"];
        $cliente->dir_fiador = $input["dir_fiador"];
        $cliente->barrio_fiador = $input["barrio_fiador"];
        $cliente->tel_fiador = $input["tel_fiador"];

        foreach ($input["clientes_referencias"] as $key => $value) {
            $clienteRef = new ClientesReferencia;
            if ($value['id'] !== 0) {
                $clienteRef = ClientesReferencia::find($value["id"]);
            }
            $clienteRef->cliente_id = $cliente->id;
            $clienteRef->nombre = $value['nombre'];
            $clienteRef->direccion = $value['direccion'];
            $clienteRef->barrio = $value['barrio'];
            $clienteRef->tipo_referencia = $value['tipo_referencia'];
            $clienteRef->telefono = $value['telefono'];
            $clienteRef->parentesco = $value['parentesco'];
            $clienteRef->save();
        }

        $cliente->save();
        $clientes = Cliente::with('clientes_referencias')->orderBy('created_at', 'DESC')->get();
        $ca = Credito::where('activo', 1)->count();
        return response()->json(['data' => $clientes, 'creditosActivos' => $ca]);
    }

    public function changeState($id)
    {
        $cliente = Cliente::with('creditos')->find($id);
        if (!Credito::CreditoUsuarioActivo($cliente->id)) {
            $cliente->estado = !$cliente->estado;
            $cliente->save();
        } else {
            return response()->json(['Error' => 'El cliente tiene actualmente un crédito activo'], 423);
        }

        $clientes = Cliente::with('clientes_referencias')->orderBy('created_at', 'DESC')->get();
        $ca = Credito::where('activo', 1)->count();
        return response()->json(['data' => $clientes, 'creditosActivos' => $ca]);
    }

    public function getDetallesCredito($id)
    {
        $creditos = Credito::where('cliente_id', $id)->with('creditos_detalles.user')->with('creditos_renovaciones')->get();
        if ($creditos->count() > 0)
            $ruta = ParametrosDetalle::where([['parametro_id', 5], ['id_interno', $creditos->first()->ruta_id]])->get();

        $response = [];
        foreach ($creditos as $credito) {
            $fecha = new Date;
            if (!$credito->activo) {
                $consulta = CreditosDetalle::where('credito_id', $credito->id)->orderBy('fecha_abono', 'DESC')->get();
                $fecha = $consulta->first();
            }
            $add = [
                "Activo" => $credito['activo'],
                "Id" => $credito['id'],
                "InicioCredito" => $credito['inicio_credito'],
                "Modalidad" => $credito['modalidad'],
                "ModCuota" => $credito['mod_cuota'],
                "ModDias" => $credito['mod_dias'],
                "ObsDia" => $credito['obs_dia'],
                "RutaId" => $credito['ruta_id'],
                "Ruta" => $ruta->first()->valor,
                "ValorPrestamo" => $credito['valor_prestamo'],
                "Finalizacion" => $credito['activo'] ? null : $fecha->fecha_abono,
                "DetallesCredito" => $credito['creditos_detalles']
            ];
            array_push($response, $add);
        }
        return response()->json($response);
    }

    public function __invoke(Request $request) {}
}
