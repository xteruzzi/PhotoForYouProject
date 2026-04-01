<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Commande;
use App\Models\LogActivite;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur AdminController
 *
 * Gère le panneau d'administration de la plateforme PhotoForYou.
 * Accessible uniquement aux utilisateurs ayant le rôle 'admin'.
 *
 * Fonctionnalités :
 *  - Tableau de bord avec les statistiques générales
 *  - Gestion des utilisateurs (activation/désactivation, suppression)
 *  - Gestion des photos (validation, refus, suppression)
 *  - Gestion des catégories (ajout, modification, suppression)
 *  - Consultation du journal d'activité
 *
 * @package App\Http\Controllers
 */
class AdminController extends Controller
{
    /**
     * Affiche le tableau de bord administrateur.
     *
     * Présente les statistiques globales de la plateforme :
     * nombre d'utilisateurs, de photos, de commandes, etc.
     *
     * @return View
     */
    public function index(): View
    {
        $stats = [
            'users'      => User::count(),
            'photos'     => Photo::count(),
            'en_attente' => Photo::where('est_validee', false)->count(),
            'commandes'  => Commande::count(),
            'categories' => Categorie::count(),
        ];

        return view('admin.index', compact('stats'));
    }

    /**
     * Affiche la liste de tous les utilisateurs inscrits.
     *
     * @return View
     */
    public function utilisateurs(): View
    {
        $users = User::orderBy('date_inscription', 'desc')->get();
        return view('admin.utilisateurs', compact('users'));
    }

    /**
     * Active ou désactive un compte utilisateur.
     *
     * Un administrateur ne peut pas être désactivé.
     * Un compte désactivé ne peut plus se connecter à la plateforme.
     *
     * @param  int  $id  L'identifiant de l'utilisateur
     * @return RedirectResponse
     */
    public function toggleActif(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return back()->with('error', 'Impossible de désactiver un administrateur.');
        }

        $user->actif = !$user->actif;
        $user->save();

        $msg = $user->actif
            ? 'Compte activé. L\'utilisateur peut de nouveau se connecter.'
            : 'Compte mis en stand-by. L\'utilisateur ne peut plus se connecter.';

        return back()->with('status', $msg);
    }

    /**
     * Supprime définitivement un compte utilisateur.
     *
     * Un administrateur ne peut pas être supprimé par cette interface.
     * La suppression entraîne également la suppression de ses données
     * liées (photos, commandes) selon les contraintes de clé étrangère.
     *
     * @param  int  $id  L'identifiant de l'utilisateur à supprimer
     * @return RedirectResponse
     */
    public function destroyUser(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return back()->with('error', 'Impossible de supprimer un administrateur.');
        }

        $user->delete();
        return back()->with('status', 'Utilisateur supprimé définitivement.');
    }

    /**
     * Affiche la liste de toutes les photos (en attente et validées).
     *
     * @return View
     */
    public function photos(): View
    {
        $photos = Photo::with(['utilisateur', 'categorie'])
            ->orderBy('date_depot', 'desc')
            ->get();

        return view('admin.photos', compact('photos'));
    }

    /**
     * Valide une photo et la publie dans le catalogue public.
     *
     * @param  int  $id  L'identifiant de la photo
     * @return RedirectResponse
     */
    public function validerPhoto(int $id): RedirectResponse
    {
        $photo = Photo::findOrFail($id);
        $photo->est_validee = true;
        $photo->save();

        return back()->with('status', 'Photo validée et publiée dans le catalogue.');
    }

    /**
     * Refuse une photo et la retire du catalogue (si elle était publiée).
     *
     * @param  int  $id  L'identifiant de la photo
     * @return RedirectResponse
     */
    public function refuserPhoto(int $id): RedirectResponse
    {
        $photo = Photo::findOrFail($id);
        $photo->est_validee = false;
        $photo->save();

        return back()->with('status', 'Photo refusée et retirée du catalogue.');
    }

    /**
     * Supprime définitivement une photo et ses fichiers associés.
     *
     * Supprime les fichiers physiques (original et version avec filigrane)
     * depuis le dossier public, puis la ligne en base de données.
     *
     * @param  int  $id  L'identifiant de la photo
     * @return RedirectResponse
     */
    public function destroyPhoto(int $id): RedirectResponse
    {
        $photo = Photo::findOrFail($id);

        $pub = public_path('images/' . $photo->nom_fichier);
        $wm  = public_path('images/' . $photo->nom_fichier_filigrane);

        if (file_exists($pub)) {
            unlink($pub);
        }
        if (file_exists($wm) && $photo->nom_fichier_filigrane !== $photo->nom_fichier) {
            unlink($wm);
        }

        $photo->delete();
        return back()->with('status', 'Photo supprimée définitivement.');
    }

    /**
     * Affiche la liste des catégories avec leur nombre de photos associées.
     *
     * @return View
     */
    public function categories(): View
    {
        $categories = Categorie::withCount('photos')->orderBy('libelle')->get();
        return view('admin.categories', compact('categories'));
    }

    /**
     * Ajoute une nouvelle catégorie.
     *
     * Le libellé doit être unique dans la base de données.
     *
     * @param  Request  $request  La requête HTTP avec : libelle, description (optionnel)
     * @return RedirectResponse
     */
    public function storeCategorie(Request $request): RedirectResponse
    {
        $request->validate([
            'libelle' => 'required|string|max:100|unique:categorie,libelle',
        ], [
            'libelle.unique' => 'Une catégorie avec ce nom existe déjà.',
        ]);

        Categorie::create([
            'libelle'     => $request->libelle,
            'description' => $request->description,
        ]);

        return back()->with('status', 'Catégorie "' . $request->libelle . '" ajoutée avec succès.');
    }

    /**
     * Modifie le libellé et/ou la description d'une catégorie existante.
     *
     * @param  Request  $request  La requête HTTP avec : libelle, description (optionnel)
     * @param  int      $id       L'identifiant de la catégorie
     * @return RedirectResponse
     */
    public function updateCategorie(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'libelle' => 'required|string|max:100',
        ]);

        $cat = Categorie::findOrFail($id);
        $cat->update([
            'libelle'     => $request->libelle,
            'description' => $request->description,
        ]);

        return back()->with('status', 'Catégorie modifiée avec succès.');
    }

    /**
     * Supprime une catégorie.
     *
     * Une catégorie ne peut être supprimée que si aucune photo
     * ne lui est rattachée.
     *
     * @param  int  $id  L'identifiant de la catégorie
     * @return RedirectResponse
     */
    public function destroyCategorie(int $id): RedirectResponse
    {
        $cat = Categorie::findOrFail($id);

        if ($cat->photos()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer une catégorie qui contient des photos.');
        }

        $cat->delete();
        return back()->with('status', 'Catégorie supprimée.');
    }

    /**
     * Affiche le journal d'activité de la plateforme.
     *
     * Permet de consulter les actions importantes enregistrées
     * automatiquement par les triggers MySQL.
     *
     * @return View
     */
    public function logs(): View
    {
        $logs = LogActivite::with('utilisateur')
            ->orderBy('date_action', 'desc')
            ->paginate(50);

        return view('admin.logs', compact('logs'));
    }
}
