<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration : table de journalisation + triggers MySQL.
 *
 * Cette migration crée :
 *  - La table `log_activite` pour tracer les actions importantes
 *  - Les triggers MySQL qui sécurisent les données côté base de données
 *
 * Triggers créés :
 *  1. trg_credits_non_negatifs       – empêche les crédits négatifs
 *  2. trg_photo_prix_insert          – valide le prix (2–100) à l'insertion
 *  3. trg_photo_prix_update          – valide le prix (2–100) à la mise à jour
 *  4. trg_commande_credits_suffisants – vérifie le solde avant achat
 *  5. trg_commande_desactiver_photo  – retire la photo du catalogue après achat
 *  6. trg_log_achat_photo            – journalise chaque achat de photo
 *  7. trg_log_achat_credits          – journalise chaque achat de crédits
 *  8. trg_log_upload_photo           – journalise chaque dépôt de photo
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Table de journalisation ───────────────────────────────────────────
        if (!Schema::hasTable('log_activite')) {
            Schema::create('log_activite', function (Blueprint $table) {
                $table->id('id_log');
                $table->unsignedBigInteger('id_utilisateur')->nullable();
                $table->string('type_action', 50);   // ex: 'achat_photo', 'upload_photo'
                $table->text('description')->nullable();
                $table->timestamp('date_action')->useCurrent();

                $table->foreign('id_utilisateur')
                      ->references('id_utilisateur')
                      ->on('utilisateur')
                      ->onDelete('set null');
            });
        }

        // ── Trigger 1 : crédits non négatifs ─────────────────────────────────
        // Si une mise à jour tente de passer les crédits en dessous de 0,
        // la base de données refuse l'opération avec un message d'erreur clair.
        DB::unprepared("
            CREATE TRIGGER trg_credits_non_negatifs
            BEFORE UPDATE ON utilisateur
            FOR EACH ROW
            BEGIN
                IF NEW.credits < 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Erreur : les credits ne peuvent pas etre negatifs.';
                END IF;
            END
        ");

        // ── Trigger 2 : validation du prix à l'insertion ──────────────────────
        // Un prix de photo doit toujours être compris entre 2 et 100 crédits.
        DB::unprepared("
            CREATE TRIGGER trg_photo_prix_insert
            BEFORE INSERT ON photo
            FOR EACH ROW
            BEGIN
                IF NEW.prix < 2 OR NEW.prix > 100 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Erreur : le prix doit etre compris entre 2 et 100 credits.';
                END IF;
            END
        ");

        // ── Trigger 3 : validation du prix à la mise à jour ──────────────────
        DB::unprepared("
            CREATE TRIGGER trg_photo_prix_update
            BEFORE UPDATE ON photo
            FOR EACH ROW
            BEGIN
                IF NEW.prix < 2 OR NEW.prix > 100 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Erreur : le prix doit etre compris entre 2 et 100 credits.';
                END IF;
            END
        ");

        // ── Trigger 4 : vérification du solde avant commande ─────────────────
        // Filet de sécurité : même si le PHP ne vérifie pas, la BDD refuse
        // toute commande si le client n'a pas assez de crédits.
        DB::unprepared("
            CREATE TRIGGER trg_commande_credits_suffisants
            BEFORE INSERT ON commande
            FOR EACH ROW
            BEGIN
                DECLARE solde DECIMAL(8,2);
                SELECT credits INTO solde
                FROM utilisateur
                WHERE id_utilisateur = NEW.id_acheteur;

                IF solde < NEW.credits_debites THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Erreur : credits insuffisants pour effectuer cet achat.';
                END IF;
            END
        ");

        // ── Trigger 5 : désactiver la photo après achat ───────────────────────
        // Garantit qu'une photo vendue n'est plus disponible à la vente,
        // même si le code PHP ne l'a pas fait (cohérence base de données).
        DB::unprepared("
            CREATE TRIGGER trg_commande_desactiver_photo
            AFTER INSERT ON commande
            FOR EACH ROW
            BEGIN
                UPDATE photo
                SET en_vente = FALSE
                WHERE id_photo = NEW.id_photo;
            END
        ");

        // ── Trigger 6 : journaliser chaque achat de photo ────────────────────
        DB::unprepared("
            CREATE TRIGGER trg_log_achat_photo
            AFTER INSERT ON commande
            FOR EACH ROW
            BEGIN
                INSERT INTO log_activite (id_utilisateur, type_action, description)
                VALUES (
                    NEW.id_acheteur,
                    'achat_photo',
                    CONCAT('Achat photo #', NEW.id_photo, ' pour ', NEW.credits_debites, ' credit(s)')
                );
            END
        ");

        // ── Trigger 7 : journaliser chaque achat de crédits ──────────────────
        DB::unprepared("
            CREATE TRIGGER trg_log_achat_credits
            AFTER INSERT ON achat_credits
            FOR EACH ROW
            BEGIN
                INSERT INTO log_activite (id_utilisateur, type_action, description)
                VALUES (
                    NEW.id_utilisateur,
                    'achat_credits',
                    CONCAT(
                        'Achat de ', NEW.nb_credits, ' credit(s) pour ',
                        NEW.montant_euros, ' EUR via ', NEW.moyen_paiement
                    )
                );
            END
        ");

        // ── Trigger 8 : journaliser chaque dépôt de photo ────────────────────
        DB::unprepared("
            CREATE TRIGGER trg_log_upload_photo
            AFTER INSERT ON photo
            FOR EACH ROW
            BEGIN
                INSERT INTO log_activite (id_utilisateur, type_action, description)
                VALUES (
                    NEW.id_utilisateur,
                    'upload_photo',
                    CONCAT('Depot photo \"', NEW.description, '\" - Prix : ', NEW.prix, ' credit(s)')
                );
            END
        ");
    }

    public function down(): void
    {
        // Suppression des triggers dans l'ordre inverse de création
        DB::unprepared('DROP TRIGGER IF EXISTS trg_log_upload_photo');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_log_achat_credits');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_log_achat_photo');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_commande_desactiver_photo');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_commande_credits_suffisants');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_photo_prix_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_photo_prix_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_credits_non_negatifs');

        Schema::dropIfExists('log_activite');
    }
};
