<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(){ return true; }

    protected function prepareForValidation()
    {
        // Format telephone number
        $telephone = $this->telephone;
        if (preg_match('/^(77|78)\d{7}$/', $telephone)) {
            $telephone = '+221' . $telephone;
        }
        $this->merge(['telephone' => $telephone]);
    }

    public function rules()
    {
        return [
            'telephone' => 'required|string|exists:users,telephone',
            'password' => 'required|string',
            'code_pin' => 'required|string',
        ];
    }
}
