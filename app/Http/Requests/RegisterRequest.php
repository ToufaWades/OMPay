<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(){ return true; }
    public function rules()
    {
        return [
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'telephone' => 'required|string|unique:users,telephone',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
            'type' => 'required|in:client,distributeur',
        ];
    }
}