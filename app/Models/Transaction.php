<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Compte;
use App\Models\User;

class Transaction extends Model
{
    use HasFactory;

     protected $fillable = [
        'compte_id',
        'montant',
        'numero_destinataire',
        'code_marchand', 
        'code_distributeur',      
        'type',
        'status',
        'date_transaction',
    ];

    protected $casts = [
        'date_transaction' => 'datetime',
    ];

    // 🔗 Relations
    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, Compte::class);
    }
}
