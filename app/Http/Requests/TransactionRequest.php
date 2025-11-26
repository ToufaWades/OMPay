<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'type' => 'required|in:transfert,paiement',
            'montant' => 'required|numeric|min:1',
            'numero_destinataire' => 'required_if:type,transfert|nullable|string',
            'code_marchand' => 'required_if:type,paiement|nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'numero_destinataire.required_if' => 'Le numéro destinataire est requis pour un transfert.',
            'code_marchand.required_if' => 'Le code marchand est requis pour un paiement.',
        ];
    }
}