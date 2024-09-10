<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ParametroRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id_interno' => 'required',
            'valor' => 'required',
            'parametro_id' => 'required'            
        ];
    }
}
