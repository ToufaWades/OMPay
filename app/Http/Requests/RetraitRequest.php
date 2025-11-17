<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RetraitRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'qr_code' => 'required|string',
            'montant' => 'required|numeric|min:1'        
        ];
    }
}
