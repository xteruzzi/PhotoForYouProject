<?php

namespace App\Http\Controllers;

use App\Models\LogActivite;
use App\Models\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Contrôleur EspacePhotographeController
 *
 * Gère les fonctionnalités de l'espace photographe :
 *  - Tableau de bord avec la liste de ses photos
 *  - Suppression d'une photo
 *  - Modification du prix d'une photo
 *  - Demande de paiement de ses gains en crédits
 *
 * @package App\Http\Controllers
 */
class EspacePhotographeController extends Controller
{
    /**
     * Affiche le tableau de bord du photographe.
     *
     * Liste toutes ses photos (validées ou en attente),
     * avec la catégorie associée, triées par date de dépôt.
     *
     * @return View
     */
    public function index(): View
    {
        $user   = Auth::user();
        $photos = Photo::with('categorie')
            ->where('id_utilisateur', $user->id_utilisateur)
            ->orderBy('date_depot', 'desc')
            ->get();

        return view('espace-photographe.index', compact('user', 'photos'));
    }

    /**
     * Supprime une photo appartenant au photographe connecté.
     *
     * Les fichiers image (original et filigrane) sont supprimés
     * du disque en plus de la suppression en base de données.
     * Un photographe ne peut supprimer que ses propres photos.
     *
     * @param  int  $id  L'identifiant de la photo à supprimer
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $photo = Photo::where('id_photo', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        // Suppression du fichier original
        $publicPath = public_path('images/' . $photo->nom_fichier);
        if (file_exists($publicPath)) {
            unlink($publicPath);
        }

        // Suppression du fichier avec filigrane (si différent de l'original)
        $filigrPath = public_path('images/' . $photo->nom_fichier_filigrane);
        if (file_exists($filigrPath) && $photo->nom_fichier_filigrane !== $photo->nom_fichier) {
            unlink($filigrPath);
        }

        $photo->delete();

        return back()->with('status', 'Photo supprimée avec succès.');
    }

    /**
     * Met à jour le prix d'une photo appartenant au photographe connecté.
     *
     * Le prix doit être compris entre 2 et 100 crédits.
     * Cette contrainte est vérifiée ici ET par un trigger MySQL côté base.
     *
     * @param  Request  $request  La requête HTTP avec le champ : prix
     * @param  int      $id       L'identifiant de la photo
     * @return RedirectResponse
     */
    public function updatePrix(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'prix' => 'required|integer|min:2|max:100',
        ], [
            'prix.min' => 'Le prix minimum est 2 crédits.',
            'prix.max' => 'Le prix maximum est 100 crédits.',
        ]);

        $photo = Photo::where('id_photo', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        $ancienPrix  = $photo->prix;
        $photo->prix = $request->prix;
        $photo->save();

        LogActivite::enregistrer('modification_prix', 'Prix modifié : "' . $photo->description . '" — ' . $ancienPrix . ' → ' . $request->prix . ' crédit(s)');
        return back()->with('status', 'Prix mis à jour avec succès.');
    }

    /**
     * Traite une demande de paiement des gains du photographe.
     *
     * Le photographe doit avoir au moins 10 crédits pour faire une demande.
     * Suite à la demande, son solde est remis à zéro et un message de confirmation
     * lui indique le montant à recevoir ainsi que le délai de traitement.
     *
     * Note : dans un vrai projet, cette action déclencherait un virement bancaire
     * ou un paiement PayPal automatisé.
     *
     * @param  Request  $request  La requête HTTP avec le champ : moyen (cheque|paypal)
     * @return RedirectResponse
     */
    public function demanderPaiement(Request $request): RedirectResponse
    {
        $request->validate([
            'moyen' => 'required|in:cheque,paypal',
        ]);

        $user = Auth::user();

        if ($user->credits < 10) {
            return back()->with('error', 'Vous devez avoir au moins 10 crédits pour demander un paiement.');
        }

        // Calcul du montant (1 crédit = 5 €)
        $montant       = $user->credits * 5;
        $user->credits = 0;
        $user->save();

        LogActivite::enregistrer('demande_paiement', 'Demande de paiement : ' . $montant . ' € par ' . $request->moyen);
        return back()->with('status',
            "Demande de paiement de {$montant} € par {$request->moyen} enregistrée. Vous serez contacté(e) sous 48h."
        );
    }
}
