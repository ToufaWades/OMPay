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
        // Si le type est client, code_pin requis
        if ($this->input('type', null) === 'client' || $this->routeIs('client.*')) {
            $rules['code_pin'] = 'required|string';
        }
        return $rules;
    }
}
