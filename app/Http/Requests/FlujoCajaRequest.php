<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class FlujoCajaRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'Descripcion' => 'required',
            'Tipo' => 'required',
            'Valor' => 'required',
            'Fecha' => 'required'
        ];
    }
}
