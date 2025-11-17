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
            'code_marchand' => 'required_without:telephone|string|nullable',
            'telephone' => 'required_without:code_marchand|string|nullable',
            'montant' => 'required|numeric|min:1',
        ];
    }
}
