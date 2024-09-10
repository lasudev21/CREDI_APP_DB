<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class FlujoUtilidadesRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'Descripcion' => 'required',
            'Valor' => 'required',
            'Tipo' => 'required',
            'Fecha' => 'required'
        ];
    }
}
