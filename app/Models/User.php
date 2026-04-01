<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Modèle User (table : utilisateur)
 *
 * Représente un utilisateur de la plateforme PhotoForYou.
 * Trois rôles sont possibles : client, photographe, admin.
 *
 * @property int         $id_utilisateur
 * @property string      $nom
 * @property string      $prenom
 * @property string      $pseudo           Identifiant de connexion unique
 * @property string      $email
 * @property string      $password          Mot de passe hashé (bcrypt)
 * @property string      $role             'client' | 'photographe' | 'admin'
 * @property float       $credits          Solde de crédits (ne peut pas être négatif)
 * @property bool        $actif            Compte actif ou suspendu
 * @property \Carbon\Carbon $date_inscription
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use Notifiable;

    protected $table      = 'utilisateur';
    protected $primaryKey = 'id_utilisateur';
    public $timestamps    = false;

    protected $fillable = [
        'nom', 'prenom', 'pseudo', 'email', 'password',
        'role', 'credits', 'actif',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'actif'    => 'boolean',
            'credits'  => 'float',
        ];
    }

    /**
     * Les photos déposées par cet utilisateur (role = photographe).
     */
    public function photos()
    {
        return $this->hasMany(Photo::class, 'id_utilisateur', 'id_utilisateur');
    }

    /**
     * Les commandes passées par cet utilisateur (role = client).
     */
    public function commandes()
    {
        return $this->hasMany(Commande::class, 'id_acheteur', 'id_utilisateur');
    }

    /**
     * Les achats de crédits effectués par cet utilisateur.
     */
    public function achatsCredits()
    {
        return $this->hasMany(AchatCredits::class, 'id_utilisateur', 'id_utilisateur');
    }

    /**
     * Vérifie si l'utilisateur est administrateur.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifie si l'utilisateur est photographe.
     *
     * @return bool
     */
    public function isPhotographe(): bool
    {
        return $this->role === 'photographe';
    }

    /**
     * Vérifie si l'utilisateur est client.
     *
     * @return bool
     */
    public function isClient(): bool
    {
        return $this->role === 'client';
    }
}
