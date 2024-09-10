<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class AddCreditoRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ClienteId' => 'required|numeric',
            'RutaId' => 'required|numeric',
            'InicioCredito' => 'required',
            'ValorPrestamo' => 'required',
            'ModCuota' => 'required',
            'ModDias' => 'required',
            'ObsDia' => '',
        ];
    }
}
