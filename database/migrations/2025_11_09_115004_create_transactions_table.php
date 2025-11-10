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
        Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('compte_id')->constrained('comptes')->cascadeOnDelete();
        $table->decimal('montant', 20, 2);
        $table->string('numero_destinataire')->nullable(); 
        $table->string('code_distributeur')->unique()->nullable();           
        $table->string('code_marchand')->nullable();
        $table->enum('type', ['transfert', 'paiement']);
        $table->enum('status', ['en_attente', 'terminé', 'échoué'])->default('en_attente');
        $table->timestamp('date_transaction')->useCurrent();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
