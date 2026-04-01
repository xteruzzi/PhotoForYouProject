<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('photo')) return;
        Schema::create('photo', function (Blueprint $table) {
            $table->id('id_photo');
            $table->string('description', 255);
            $table->string('nom_fichier', 255);           // original (storage privé)
            $table->string('nom_fichier_filigrane', 255); // version publique avec filigrane
            $table->unsignedInteger('prix');              // en crédits (2 à 100)
            $table->foreignId('id_categorie')->constrained('categorie', 'id_categorie')->onDelete('restrict');
            $table->foreignId('id_utilisateur')->constrained('utilisateur', 'id_utilisateur')->onDelete('cascade');
            $table->boolean('est_validee')->default(false);
            $table->boolean('en_vente')->default(true);   // false après achat (exclusivité)
            $table->timestamp('date_depot')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo');
    }
};
