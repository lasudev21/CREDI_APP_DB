<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class PasswordResetRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'userId' => 'required',
            'password' => 'required',
        ];
    }
}
