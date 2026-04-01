<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Commande (table : commande)
 *
 * Représente l'achat d'une photo par un client.
 * Une commande est créée lors d'une transaction d'achat et constitue
 * la preuve que le client a bien acquis la photo.
 *
 * Répartition des crédits lors d'un achat :
 *  - 100% du prix est débité au client (credits_debites)
 *  - 50% du prix est crédité au photographe (credits_photographe)
 *  - Les 50% restants constituent la commission de la plateforme
 *
 * @property int         $id_commande
 * @property int         $id_photo
 * @property int         $id_acheteur         L'identifiant du client acheteur
 * @property int         $credits_debites     Crédits retirés au client
 * @property float       $credits_photographe Crédits versés au photographe (50%)
 * @property \Carbon\Carbon $date_achat
 *
 * @package App\Models
 */
class Commande extends Model
{
    protected $table      = 'commande';
    protected $primaryKey = 'id_commande';
    public $timestamps    = false;

    protected $fillable = [
        'id_photo', 'id_acheteur',
        'credits_debites', 'credits_photographe',
    ];

    protected function casts(): array
    {
        return [
            'date_achat' => 'datetime',
        ];
    }

    /**
     * La photo achetée dans cette commande.
     */
    public function photo()
    {
        return $this->belongsTo(Photo::class, 'id_photo', 'id_photo');
    }

    /**
     * Le client qui a passé cette commande.
     */
    public function acheteur()
    {
        return $this->belongsTo(User::class, 'id_acheteur', 'id_utilisateur');
    }
}
