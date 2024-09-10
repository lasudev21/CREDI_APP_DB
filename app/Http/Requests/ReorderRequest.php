<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ReorderRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'idRuta' => 'required',
            'data' => 'required'            
        ];
    }
}
