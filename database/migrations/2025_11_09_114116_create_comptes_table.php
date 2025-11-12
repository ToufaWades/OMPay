<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('comptes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('numero_compte')->unique();
            $table->decimal('solde', 15, 2)->default(0);
            $table->string('devise')->default('FCFA');
            $table->string('qr_code');
            $table->string('code_pin')->nullable();
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('comptes');
    }
};