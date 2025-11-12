<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransfertRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'numero' => 'required|string',
            'montant' => 'required|numeric|min:1',
        ];
    }
}
