<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
               $table->id();
        $table->string('nom');
        $table->string('prenom');
        $table->string('telephone')->unique();
        $table->string('password');
        $table->enum('type', ['client', 'distributeur'])->default('client');
        $table->timestamps();
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
