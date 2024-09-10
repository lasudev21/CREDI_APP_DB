<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CoteosRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fechaIni' => 'required',            
            'fechaFin' => 'required',            
        ];
    }
}
