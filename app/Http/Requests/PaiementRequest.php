<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaiementRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code_marchand' => 'required|string',
            'montant' => 'required|numeric|min:1',
        ];
    }
}
