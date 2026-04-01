<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Photo (table : photo)
 *
 * Représente une photo mise en vente sur la plateforme par un photographe.
 * Chaque photo possède deux versions :
 *  - nom_fichier : l'original haute résolution (accès protégé, réservé à l'acheteur)
 *  - nom_fichier_filigrane : la version publique avec watermark PhotoForYou
 *
 * Une photo doit être validée par un administrateur avant d'apparaître
 * dans le catalogue. La vente est exclusive : une fois achetée, la photo
 * est retirée du catalogue (en_vente = false).
 *
 * Contraintes métier :
 *  - Prix : entre 2 et 100 crédits (validé côté PHP ET par trigger MySQL)
 *  - Une photo achetée ne peut plus être vendue à quelqu'un d'autre
 *
 * @property int         $id_photo
 * @property string      $description
 * @property string      $nom_fichier           Fichier original (storage/app/originals/)
 * @property string      $nom_fichier_filigrane  Fichier public (public/images/)
 * @property int         $prix                  En crédits (2 à 100)
 * @property int         $id_categorie
 * @property int         $id_utilisateur        Le photographe propriétaire
 * @property bool        $est_validee           True si validée par un admin
 * @property bool        $en_vente              False après achat (vente exclusive)
 * @property \Carbon\Carbon $date_depot
 *
 * @package App\Models
 */
class Photo extends Model
{
    protected $table      = 'photo';
    protected $primaryKey = 'id_photo';
    public $timestamps    = false;

    protected $fillable = [
        'description', 'nom_fichier', 'nom_fichier_filigrane',
        'prix', 'id_categorie', 'id_utilisateur',
        'est_validee', 'en_vente', 'date_depot',
    ];

    protected function casts(): array
    {
        return [
            'est_validee' => 'boolean',
            'en_vente'    => 'boolean',
            'date_depot'  => 'datetime',
        ];
    }

    /**
     * Le photographe qui a déposé cette photo.
     */
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }

    /**
     * La catégorie à laquelle appartient cette photo.
     */
    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'id_categorie', 'id_categorie');
    }

    /**
     * Les commandes liées à cette photo.
     * En principe, au maximum une commande (vente exclusive).
     */
    public function commandes()
    {
        return $this->hasMany(Commande::class, 'id_photo', 'id_photo');
    }
}
