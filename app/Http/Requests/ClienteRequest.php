<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ClienteRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'titular' => 'required',
            'cc_titular' => 'required',
            'fiador' => 'required',
            'cc_fiador' => 'required',
            'neg_titular' => 'required',
            'neg_fiador' => 'required',
            'dir_cobro' => 'required',
            'barrio_cobro' => 'required',
            'tel_cobro' => 'required',
            'dir_casa' => 'required',
            'barrio_casa' => 'required',
            'tel_casa' => 'required',
            'dir_fiador' => 'required',
            'barrio_fiador' => 'required',
            'tel_fiador' => 'required',
            'clientes_referencias' => ''
        ];
    }
}
