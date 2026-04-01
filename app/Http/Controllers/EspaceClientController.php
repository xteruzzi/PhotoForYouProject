<?php

namespace App\Http\Controllers;

use App\Models\AchatCredits;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur EspaceClientController
 *
 * Gère les fonctionnalités de l'espace client :
 *  - Tableau de bord (historique des commandes)
 *  - Achat de crédits (simulation de paiement)
 *
 * @package App\Http\Controllers
 */
class EspaceClientController extends Controller
{
    /**
     * Affiche le tableau de bord du client.
     *
     * Liste toutes les photos achetées par le client connecté,
     * triées par date d'achat décroissante.
     *
     * @return View
     */
    public function index(): View
    {
        $user      = Auth::user();
        $commandes = Commande::with(['photo.categorie', 'photo.utilisateur'])
            ->where('id_acheteur', $user->id_utilisateur)
            ->orderBy('date_achat', 'desc')
            ->get();

        return view('espace-client.index', compact('user', 'commandes'));
    }

    /**
     * Affiche le formulaire d'achat de crédits.
     *
     * @return View
     */
    public function showAchatCredits(): View
    {
        $user = Auth::user();
        return view('credits.acheter', compact('user'));
    }

    /**
     * Traite l'achat de crédits par le client.
     *
     * L'opération est atomique (transaction MySQL) pour garantir
     * la cohérence entre le solde mis à jour et l'historique d'achat.
     *
     * Règle tarifaire : 1 crédit = 5 €
     *
     * Note : dans un vrai projet, l'intégration d'une API de paiement
     * (Stripe, PayPal) remplacerait la simulation actuelle.
     *
     * @param  Request  $request  La requête HTTP avec : nb_credits, moyen_paiement
     * @return RedirectResponse
     */
    public function acheterCredits(Request $request): RedirectResponse
    {
        $request->validate([
            'nb_credits'     => 'required|integer|min:1|max:500',
            'moyen_paiement' => 'required|in:carte,paypal',
        ], [
            'nb_credits.min' => 'Vous devez acheter au moins 1 crédit.',
            'nb_credits.max' => 'Vous ne pouvez pas acheter plus de 500 crédits à la fois.',
        ]);

        $user    = Auth::user();
        $nb      = (int) $request->nb_credits;
        $montant = $nb * 5; // 1 crédit = 5 €

        // Transaction atomique : mise à jour du solde + historique en même temps
        DB::transaction(function () use ($user, $nb, $montant, $request) {
            // Simulation du paiement (à remplacer par Stripe/PayPal en production)
            $user->credits += $nb;
            $user->save();

            // Enregistrement dans l'historique des achats de crédits
            AchatCredits::create([
                'id_utilisateur'  => $user->id_utilisateur,
                'nb_credits'      => $nb,
                'montant_euros'   => $montant,
                'moyen_paiement'  => $request->moyen_paiement,
            ]);
        });

        return redirect()->route('client.dashboard')
            ->with('status', "{$nb} crédit(s) ajouté(s) à votre compte ({$montant} €). Bonne navigation !");
    }
}
