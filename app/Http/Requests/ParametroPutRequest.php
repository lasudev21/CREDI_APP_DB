<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ParametroPutRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'cambios' => 'required',
        ];
    }
}
