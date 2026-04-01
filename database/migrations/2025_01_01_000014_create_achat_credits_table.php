<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('achat_credits')) return;
        Schema::create('achat_credits', function (Blueprint $table) {
            $table->id('id_achat');
            $table->foreignId('id_utilisateur')->constrained('utilisateur', 'id_utilisateur')->onDelete('cascade');
            $table->unsignedInteger('nb_credits');
            $table->decimal('montant_euros', 8, 2);  // nb_credits * 5
            $table->enum('moyen_paiement', ['carte', 'paypal']);
            $table->timestamp('date_achat')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achat_credits');
    }
};
