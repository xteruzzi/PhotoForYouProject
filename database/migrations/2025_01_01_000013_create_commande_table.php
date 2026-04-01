<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commande')) return;
        Schema::create('commande', function (Blueprint $table) {
            $table->id('id_commande');
            $table->foreignId('id_photo')->constrained('photo', 'id_photo')->onDelete('restrict');
            $table->foreignId('id_acheteur')->constrained('utilisateur', 'id_utilisateur')->onDelete('restrict');
            $table->unsignedInteger('credits_debites');        // crédits retirés au client
            $table->decimal('credits_photographe', 8, 2);     // 50% au photographe
            $table->timestamp('date_achat')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commande');
    }
};
