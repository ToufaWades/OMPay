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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compte_id')->constrained('comptes')->onDelete('cascade');
            $table->enum('type', ['paiement', 'transfert', 'depot', 'retrait']);
            $table->decimal('montant', 15, 2);
            $table->string('status')->default('terminé');
            $table->timestamp('date_transaction')->useCurrent();
            $table->string('code_marchand')->nullable();
            $table->string('code_distributeur')->nullable()->after('code_marchand');
            $table->string('numero_destinataire')->nullable();
            $table->string('code_validation')->nullable();
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('transactions');
    }
};