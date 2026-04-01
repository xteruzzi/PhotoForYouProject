<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('utilisateur', 'pseudo')) {
            // Ajouter la colonne sans contrainte UNIQUE d'abord
            Schema::table('utilisateur', function (Blueprint $table) {
                $table->string('pseudo', 50)->nullable()->after('prenom');
            });
        }

        // Générer un pseudo temporaire unique pour les comptes existants sans pseudo
        $users = DB::table('utilisateur')->whereNull('pseudo')->orWhere('pseudo', '')->get();
        foreach ($users as $user) {
            DB::table('utilisateur')
                ->where('id_utilisateur', $user->id_utilisateur)
                ->update(['pseudo' => 'user_' . $user->id_utilisateur]);
        }

        // Ajouter la contrainte NOT NULL et UNIQUE maintenant que toutes les valeurs sont remplies
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->string('pseudo', 50)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->dropUnique('utilisateur_pseudo_unique');
            $table->dropColumn('pseudo');
        });
    }
};
