<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle AchatCredits (table : achat_credits)
 *
 * Représente un achat de crédits effectué par un client.
 * Sert d'historique des transactions financières côté client.
 *
 * Règle tarifaire : 1 crédit = 5 €
 * Le montant en euros est calculé automatiquement : nb_credits × 5
 *
 * Moyens de paiement disponibles :
 *  - carte   : paiement par carte bancaire
 *  - paypal  : paiement via PayPal
 *
 * Note : dans cette version, le paiement est simulé.
 * Une intégration Stripe ou PayPal remplacerait cette logique en production.
 *
 * @property int         $id_achat
 * @property int         $id_utilisateur
 * @property int         $nb_credits       Nombre de crédits achetés
 * @property float       $montant_euros    Montant payé en euros
 * @property string      $moyen_paiement   'carte' ou 'paypal'
 * @property \Carbon\Carbon $date_achat
 *
 * @package App\Models
 */
class AchatCredits extends Model
{
    protected $table      = 'achat_credits';
    protected $primaryKey = 'id_achat';
    public $timestamps    = false;

    protected $fillable = [
        'id_utilisateur', 'nb_credits', 'montant_euros', 'moyen_paiement',
    ];

    protected function casts(): array
    {
        return [
            'date_achat' => 'datetime',
        ];
    }

    /**
     * L'utilisateur qui a effectué cet achat de crédits.
     */
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
