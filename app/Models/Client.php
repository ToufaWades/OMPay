<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Compte;

class Client extends Model
{
    use HasFactory;

  protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'telephone',
        'pays',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comptes()
    {
        return $this->hasMany(Compte::class, 'user_id', 'user_id');
    }
}
