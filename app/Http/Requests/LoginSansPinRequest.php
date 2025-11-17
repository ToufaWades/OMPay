<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginSansPinRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'telephone' => 'required|string',
            'password' => 'required|string',
        ];
    }
}
