<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle LogActivite
 *
 * Représente une entrée dans le journal d'activité de la plateforme.
 * Ce journal est alimenté automatiquement par les triggers MySQL,
 * mais peut aussi être consulté via ce modèle Eloquent.
 *
 * @property int         $id_log
 * @property int|null    $id_utilisateur
 * @property string      $type_action     Ex : 'achat_photo', 'upload_photo', 'achat_credits'
 * @property string|null $description
 * @property \Carbon\Carbon $date_action
 *
 * @package App\Models
 */
class LogActivite extends Model
{
    protected $table      = 'log_activite';
    protected $primaryKey = 'id_log';
    public $timestamps    = false;

    protected $fillable = [
        'id_utilisateur',
        'type_action',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'date_action' => 'datetime',
        ];
    }

    /**
     * Enregistre une entrée dans le journal d'activité.
     *
     * @param  string    $typeAction     Ex : 'connexion', 'validation_photo'
     * @param  string    $description    Description lisible de l'action
     * @param  int|null  $idUtilisateur  Laissé null pour utiliser l'utilisateur connecté
     */
    public static function enregistrer(string $typeAction, string $description, ?int $idUtilisateur = null): void
    {
        static::create([
            'id_utilisateur' => $idUtilisateur ?? \Illuminate\Support\Facades\Auth::id(),
            'type_action'    => $typeAction,
            'description'    => $description,
        ]);
    }

    /**
     * L'utilisateur associé à cette entrée de journal.
     * Peut être null si l'utilisateur a été supprimé.
     */
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
