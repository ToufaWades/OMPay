<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(){ return true; }
    public function rules()
    {
        $rules = [
            'telephone' => 'required|string|exists:users,telephone',
            'password' => 'required|string',
        ];
        if ($this->input('type', null) === 'client') {
            $rules['code_pin'] = 'required|string';
        }
        return $rules;
    }
}
