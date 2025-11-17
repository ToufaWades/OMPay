<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(){ return true; }
    public function rules()
    {
        return [
            'telephone' => 'required|string|exists:users,telephone',
            'password' => 'required|string',
            'code_pin' => 'required|string',
        ];
    }
}
