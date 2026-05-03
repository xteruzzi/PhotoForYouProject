<?php

namespace App\Http\Controllers;

use App\Models\LogActivite;
use App\Models\Photo;
use App\Models\Categorie;
use App\Models\Commande;
use App\Services\WatermarkService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse; // type de réponse pour l'envoi d'un fichier

/**
 * Contrôleur PhotoController
 *
 * Gère toutes les opérations liées aux photos :
 *  - Affichage du catalogue public
 *  - Dépôt d'une photo par un photographe
 *  - Achat d'une photo par un client
 *  - Téléchargement de la photo originale après achat
 *  - Affichage du détail d'une photo
 *
 * @package App\Http\Controllers
 */
class PhotoController extends Controller
{
    /**
     * Affiche le catalogue public des photos disponibles à la vente.
     *
     * Seules les photos validées par un administrateur et encore en vente
     * sont affichées. Le résultat peut être filtré par catégorie ou par mot-clé.
     *
     * @param  Request  $request  La requête HTTP (peut contenir 'categorie' et 'recherche')
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Photo::with(['utilisateur', 'categorie'])
            ->where('est_validee', true)
            ->where('en_vente', true);

        if ($request->filled('categorie')) {
            $query->where('id_categorie', $request->categorie);
        }

        if ($request->filled('recherche')) {
            $query->where('description', 'like', '%' . $request->recherche . '%');
        }

        $photos     = $query->orderBy('date_depot', 'desc')->paginate(25);
        $categories = Categorie::orderBy('libelle')->get();

        return view('catalogue', compact('photos', 'categories'));
    }

    /**
     * Affiche le formulaire de dépôt d'une nouvelle photo (espace photographe).
     *
     * @return View
     */
    public function create(): View
    {
        $categories = Categorie::orderBy('libelle')->get();
        return view('vendre', compact('categories'));
    }

    /**
     * Enregistre une nouvelle photo déposée par un photographe.
     *
     * Le fichier uploadé est :
     *  1. Copié dans public/images/ pour la génération du filigrane
     *  2. Stocké dans storage/app/originals/ (accès protégé, réservé aux acheteurs)
     *  3. Un filigrane est appliqué sur la version publique
     *
     * La photo est enregistrée en base avec est_validee = false
     * et sera publiée uniquement après validation par un administrateur.
     *
     * @param  Request  $request  La requête HTTP avec les champs : description, prix, photo, id_categorie
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'description'  => 'required|string|max:255',
            'prix'         => 'required|integer|min:2|max:100',
            'photo'        => 'required|image|mimes:jpeg,jpg,png|max:30720', // 30 MB max
            'id_categorie' => 'required|exists:categorie,id_categorie',
        ], [
            'prix.min'  => 'Le prix minimum est 2 crédits.',
            'prix.max'  => 'Le prix maximum est 100 crédits.',
            'photo.max' => 'La photo ne doit pas dépasser 30 Mo.',
        ]);

        $file     = $request->file('photo');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // 1. Lecture du contenu temporaire avant déplacement
        $tempContent = file_get_contents($file->getRealPath());

        // 2. Déplacement vers public/images/ (utilisé pour générer le filigrane)
        $file->move(public_path('images'), $filename);

        // 3. Sauvegarde de l'original dans storage (accès protégé)
        Storage::disk('local')->put('originals/' . $filename, $tempContent);

        // 4. Génération du filigrane à partir du fichier public
        $filigraneName = (new WatermarkService())->apply($filename);

        Photo::create([
            'description'           => $request->description,
            'nom_fichier'           => $filename,
            'nom_fichier_filigrane' => $filigraneName,
            'prix'                  => $request->prix,
            'id_categorie'          => $request->id_categorie,
            'id_utilisateur'        => Auth::id(),
            'est_validee'           => false,
            'en_vente'              => true,
            'date_depot'            => now(),
        ]);

        return redirect()->route('photographe.dashboard')
            ->with('status', 'Photo envoyée ! Elle sera publiée après validation par un administrateur.');
    }

    /**
     * Traite l'achat d'une photo par un client.
     *
     * L'opération est entièrement atomique (transaction MySQL) :
     *  - Vérification que la photo est disponible à la vente
     *  - Vérification que le client n'a pas déjà acheté cette photo
     *  - Vérification du solde de crédits du client
     *  - Débit du client et crédit du photographe (50%)
     *  - Création de la commande
     *  - Retrait de la photo du catalogue (vente exclusive)
     *
     * En cas d'erreur à n'importe quelle étape, toutes les modifications
     * sont annulées (rollback automatique).
     *
     * @param  Request  $request  La requête HTTP
     * @param  int      $id       L'identifiant de la photo à acheter
     * @return RedirectResponse
     */
    public function acheter(Request $request, int $id): RedirectResponse
    {
        $photo = Photo::where('id_photo', $id)
            ->where('est_validee', true)
            ->where('en_vente', true)
            ->firstOrFail();

        $client = Auth::user();

        if ($client->isPhotographe() || $client->isAdmin()) {
            return back()->with('error', 'Seuls les clients peuvent acheter des photos.');
        }

        if ($photo->commandes()->where('id_acheteur', $client->id_utilisateur)->exists()) {
            return back()->with('error', 'Vous avez déjà acheté cette photo.');
        }

        if ($client->credits < $photo->prix) {
            return back()->with('error',
                'Crédits insuffisants. Vous avez ' . $client->credits .
                ' crédit(s), il en faut ' . $photo->prix . '.'
            );
        }

        // Transaction atomique : toutes les opérations ci-dessous forment un bloc.
        // Si une seule échoue (ex: erreur BDD), TOUTES sont annulées (rollback).
        // Le "use ($client, $photo)" permet d'utiliser ces variables à l'intérieur.
        DB::transaction(function () use ($client, $photo) {
            $creditsPhotographe = $photo->prix * 0.5;

            // Débit du client
            $client->credits -= $photo->prix;
            $client->save();

            // Crédit du photographe (50% de la vente)
            $photographe = $photo->utilisateur;
            $photographe->credits += $creditsPhotographe;
            $photographe->save();

            // Enregistrement de la commande
            Commande::create([
                'id_photo'            => $photo->id_photo,
                'id_acheteur'         => $client->id_utilisateur,
                'credits_debites'     => $photo->prix,
                'credits_photographe' => $creditsPhotographe,
            ]);

            // Retrait du catalogue (vente exclusive : une seule fois)
            $photo->en_vente = false;
            $photo->save();
        });

        return redirect()->route('client.dashboard')
            ->with('status', 'Achat réussi ! La photo "' . $photo->description . '" est disponible dans votre espace.');
    }

    /**
     * Affiche le détail d'une photo du catalogue.
     *
     * Seules les photos validées sont accessibles publiquement.
     *
     * @param  int  $id  L'identifiant de la photo
     * @return View
     */
    public function show(int $id): View
    {
        $photo = Photo::with(['utilisateur', 'categorie'])
            ->where('est_validee', true)
            ->findOrFail($id);

        return view('photo.show', compact('photo'));
    }

    /**
     * Permet à un client de télécharger la version originale d'une photo achetée.
     *
     * Vérifie que l'utilisateur connecté a bien acheté la photo avant de
     * lui donner accès au fichier original (sans filigrane).
     *
     * @param  int  $id  L'identifiant de la photo
     * @return BinaryFileResponse
     */
    public function telecharger(int $id): BinaryFileResponse
    {
        $photo = Photo::findOrFail($id);

        // Vérification que l'utilisateur a bien acheté cette photo
        Commande::where('id_photo', $id)
            ->where('id_acheteur', Auth::id())
            ->firstOrFail();

        $filePath = storage_path('app/originals/' . $photo->nom_fichier);

        if (!file_exists($filePath)) {
            // Fallback si l'original n'est pas dans le storage
            $filePath = public_path('images/' . $photo->nom_fichier);
        }

        $extension = pathinfo($photo->nom_fichier, PATHINFO_EXTENSION);

        LogActivite::enregistrer('telechargement_photo', 'Téléchargement original : "' . $photo->description . '" (ID : ' . $photo->id_photo . ')');
        return response()->download($filePath, $photo->description . '_original.' . $extension);
    }
}
