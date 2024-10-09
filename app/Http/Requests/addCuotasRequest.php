<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class addCuotasRequest extends Request
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'CalculoMoras' => '',
            'IdRuta' => 'required',
            'Renovaciones' => '',
            'Eliminados' => '',
            'Nuevos' => '',
            'FlujoCaja' => 'required',
            'Abonos' => '',
            'User' => 'required'
        ];
    }
}
