<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('utilisateur')) return;
        Schema::create('utilisateur', function (Blueprint $table) {
            $table->id('id_utilisateur');
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['client', 'photographe', 'admin'])->default('client');
            $table->decimal('credits', 8, 2)->default(0);
            $table->boolean('actif')->default(true);
            $table->rememberToken();
            $table->timestamp('date_inscription')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateur');
    }
};
