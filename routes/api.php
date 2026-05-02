<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\WatermarkService;

/*
|--------------------------------------------------------------------------
| API PhotoForYou — Routes pour l'application desktop JavaFX
|--------------------------------------------------------------------------
*/

const CLE_API = 'BTS-SIO-PHOTOFORYOU-2024';

// ── Colonnes plates retournées pour une photo ─────────────────────────
function colonnesPhoto(): array
{
    return [
        'photo.id_photo as id',
        'photo.description',
        'photo.prix',
        'photo.nom_fichier',
        DB::raw("CONCAT('http://127.0.0.1:8000/images/', photo.nom_fichier_filigrane) as url_apercu"),
        'categorie.libelle as categorie',
        'utilisateur.pseudo as photographe',
        'photo.est_validee',
        'photo.en_vente',
    ];
}

// ── Requête de base joignant photo + categorie + utilisateur ──────────
function queryPhotos()
{
    return DB::table('photo')
        ->leftJoin('categorie',   'photo.id_categorie',   '=', 'categorie.id_categorie')
        ->leftJoin('utilisateur', 'photo.id_utilisateur', '=', 'utilisateur.id_utilisateur')
        ->select(colonnesPhoto())
        ->orderBy('photo.date_depot', 'desc');
}

// ── ROUTE PUBLIQUE : test de connexion (sans clé API) ─────────────────
Route::get('/test', function () {
    return response()->json(['statut' => 'ok', 'message' => 'API PhotoForYou opérationnelle']);
});

// ── ROUTE : Connexion ──────────────────────────────────────────────────
Route::post('/connexion', function (Request $request) {

    if ($request->header('X-API-KEY') !== CLE_API) {
        return response()->json(['erreur' => 'Clé API invalide'], 401);
    }

    $utilisateur = DB::table('utilisateur')->where('pseudo', $request->input('pseudo'))->first();

    if (!$utilisateur) return response()->json(['erreur' => 'Pseudo introuvable'], 401);
    if (!$utilisateur->actif) return response()->json(['erreur' => 'Compte désactivé'], 403);
    if (!Hash::check($request->input('password'), $utilisateur->password))
        return response()->json(['erreur' => 'Mot de passe incorrect'], 401);

    return response()->json([
        'id'      => $utilisateur->id_utilisateur,
        'pseudo'  => $utilisateur->pseudo,
        'nom'     => $utilisateur->nom,
        'prenom'  => $utilisateur->prenom,
        'role'    => $utilisateur->role,
        'credits' => $utilisateur->credits,
    ]);
});

// ── ROUTE : Télécharger un original (photographes, via clé API) ───────
Route::get('/images/{nomFichier}', function (Request $request, $nomFichier) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $chemin = storage_path('app/originals/' . $nomFichier);
    if (!file_exists($chemin)) return response()->json(['erreur' => 'Fichier introuvable'], 404);
    return response()->file($chemin);
});

// ── ROUTE : Détail d'une photo ─────────────────────────────────────────
Route::get('/photos/{id}', function (Request $request, $id) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $photo = queryPhotos()->where('photo.id_photo', $id)->first();
    if (!$photo) return response()->json(['erreur' => 'Photo introuvable'], 404);
    return response()->json($photo);
});

// ════════════════════════════════════════════════════════════════════
//  CATALOGUE ET PHOTOS
// ════════════════════════════════════════════════════════════════════

// ── Catalogue public (validées et en vente) ───────────────────────────
Route::get('/catalogue', function (Request $request) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    return response()->json(
        queryPhotos()->where('photo.est_validee', 1)->where('photo.en_vente', 1)->get()
    );
});

// ── Photos d'un photographe (toutes, validées ou non) ─────────────────
Route::get('/mes-photos', function (Request $request) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $uid = $request->query('utilisateur_id');
    if (!$uid) return response()->json(['erreur' => 'utilisateur_id requis'], 400);

    return response()->json(queryPhotos()->where('photo.id_utilisateur', $uid)->get());
});

// ── Photos achetées par un client ─────────────────────────────────────
Route::get('/mes-achats', function (Request $request) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $uid = $request->query('utilisateur_id');
    if (!$uid) return response()->json(['erreur' => 'utilisateur_id requis'], 400);

    $photos = DB::table('commande')
        ->join('photo',       'commande.id_photo',       '=', 'photo.id_photo')
        ->leftJoin('categorie',   'photo.id_categorie',   '=', 'categorie.id_categorie')
        ->leftJoin('utilisateur', 'photo.id_utilisateur', '=', 'utilisateur.id_utilisateur')
        ->where('commande.id_acheteur', $uid)
        ->select(colonnesPhoto())
        ->orderBy('commande.date_achat', 'desc')
        ->get();

    return response()->json($photos);
});

// ── Catégories ────────────────────────────────────────────────────────
Route::get('/categories', function (Request $request) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    return response()->json(
        DB::table('categorie')->select('id_categorie as id', 'libelle as nom')->orderBy('libelle')->get()
    );
});

// ── Modifier le prix d'une photo ──────────────────────────────────────
Route::post('/photos/{id}/prix', function (Request $request, $id) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $prix = (int) $request->input('prix');
    if ($prix < 2 || $prix > 100) return response()->json(['erreur' => 'Prix invalide (2-100 crédits)'], 422);

    $photo = DB::table('photo')
        ->where('id_photo', $id)
        ->where('id_utilisateur', $request->input('utilisateur_id'))
        ->first();
    if (!$photo) return response()->json(['erreur' => 'Photo introuvable ou non autorisée'], 403);

    DB::table('photo')->where('id_photo', $id)->update(['prix' => $prix]);
    return response()->json(['statut' => 'ok', 'prix' => $prix]);
});

// ── Supprimer une photo (photographe propriétaire) ────────────────────
Route::delete('/photos/{id}', function (Request $request, $id) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $photo = DB::table('photo')
        ->where('id_photo', $id)
        ->where('id_utilisateur', $request->query('utilisateur_id'))
        ->first();
    if (!$photo) return response()->json(['erreur' => 'Photo introuvable ou non autorisée'], 403);

    if (file_exists(public_path('images/' . $photo->nom_fichier)))
        unlink(public_path('images/' . $photo->nom_fichier));
    if ($photo->nom_fichier_filigrane !== $photo->nom_fichier
        && file_exists(public_path('images/' . $photo->nom_fichier_filigrane)))
        unlink(public_path('images/' . $photo->nom_fichier_filigrane));
    if (file_exists(storage_path('app/originals/' . $photo->nom_fichier)))
        unlink(storage_path('app/originals/' . $photo->nom_fichier));

    DB::table('photo')->where('id_photo', $id)->delete();
    return response()->json(['statut' => 'ok']);
});

// ── Demande de virement (photographe) ────────────────────────────────
Route::post('/paiement', function (Request $request) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $uid  = $request->input('utilisateur_id');
    $user = DB::table('utilisateur')->where('id_utilisateur', $uid)->first();
    if (!$user) return response()->json(['erreur' => 'Utilisateur introuvable'], 404);
    if ($user->credits < 10) return response()->json(['erreur' => 'Minimum 10 crédits requis'], 400);

    $montant = $user->credits * 5;
    DB::table('utilisateur')->where('id_utilisateur', $uid)->update(['credits' => 0]);
    return response()->json(['statut' => 'ok', 'montant' => $montant]);
});

// ── Dépôt d'une photo (multipart/form-data) ───────────────────────────
Route::post('/deposer', function (Request $request) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $uid  = $request->input('utilisateur_id');
    $user = DB::table('utilisateur')->where('id_utilisateur', $uid)->first();
    if (!$user || !in_array($user->role, ['photographe', 'admin']))
        return response()->json(['erreur' => 'Seuls les photographes peuvent déposer des photos'], 403);

    $description = trim($request->input('description', ''));
    $prix        = (int) $request->input('prix');
    $categorieId = $request->input('categorie_id');

    if (empty($description)) return response()->json(['erreur' => 'Description requise'], 422);
    if ($prix < 2 || $prix > 100) return response()->json(['erreur' => 'Prix invalide (2-100)'], 422);
    if (!$request->hasFile('photo')) return response()->json(['erreur' => 'Fichier photo requis'], 422);
    if (!DB::table('categorie')->where('id_categorie', $categorieId)->exists())
        return response()->json(['erreur' => 'Catégorie invalide'], 422);

    $file     = $request->file('photo');
    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

    $tempContent = file_get_contents($file->getRealPath());
    $file->move(public_path('images'), $filename);
    Storage::disk('local')->put('originals/' . $filename, $tempContent);
    $filigraneName = (new WatermarkService())->apply($filename);

    DB::table('photo')->insert([
        'description'           => $description,
        'nom_fichier'           => $filename,
        'nom_fichier_filigrane' => $filigraneName,
        'prix'                  => $prix,
        'id_categorie'          => $categorieId,
        'id_utilisateur'        => $uid,
        'est_validee'           => false,
        'en_vente'              => true,
        'date_depot'            => now(),
    ]);

    return response()->json(['statut' => 'ok', 'message' => 'Photo déposée, en attente de validation']);
});

// ── Télécharger l'original après achat (clients) ──────────────────────
Route::get('/telecharger/{id}', function (Request $request, $id) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $uid = $request->query('utilisateur_id');
    if (!DB::table('commande')->where('id_photo', $id)->where('id_acheteur', $uid)->exists())
        return response()->json(['erreur' => 'Vous n\'avez pas acheté cette photo'], 403);

    $photo = DB::table('photo')->where('id_photo', $id)->first();
    if (!$photo) return response()->json(['erreur' => 'Photo introuvable'], 404);

    $filePath = storage_path('app/originals/' . $photo->nom_fichier);
    if (!file_exists($filePath)) $filePath = public_path('images/' . $photo->nom_fichier);
    if (!file_exists($filePath)) return response()->json(['erreur' => 'Fichier introuvable'], 404);

    $ext = pathinfo($photo->nom_fichier, PATHINFO_EXTENSION);
    return response()->download($filePath, $photo->description . '_original.' . $ext);
});

// ════════════════════════════════════════════════════════════════════
//  ADMINISTRATION
// ════════════════════════════════════════════════════════════════════

// ── Admin : photos en attente de validation ───────────────────────────
Route::get('/admin/photos/attente', function (Request $request) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    return response()->json(queryPhotos()->where('photo.est_validee', 0)->get());
});

// ── Admin : toutes les photos ─────────────────────────────────────────
Route::get('/admin/photos', function (Request $request) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    return response()->json(queryPhotos()->get());
});

// ── Admin : valider une photo ─────────────────────────────────────────
Route::post('/admin/photos/{id}/valider', function (Request $request, $id) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $adminId = $request->input('admin_id');
    if (!DB::table('utilisateur')->where('id_utilisateur', $adminId)->where('role', 'admin')->exists())
        return response()->json(['erreur' => 'Accès réservé aux administrateurs'], 403);

    if (!DB::table('photo')->where('id_photo', $id)->update(['est_validee' => true]))
        return response()->json(['erreur' => 'Photo introuvable'], 404);

    return response()->json(['statut' => 'ok']);
});

// ── Admin : supprimer une photo ───────────────────────────────────────
Route::delete('/admin/photos/{id}', function (Request $request, $id) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $adminId = $request->query('admin_id');
    if (!DB::table('utilisateur')->where('id_utilisateur', $adminId)->where('role', 'admin')->exists())
        return response()->json(['erreur' => 'Accès réservé aux administrateurs'], 403);

    $photo = DB::table('photo')->where('id_photo', $id)->first();
    if (!$photo) return response()->json(['erreur' => 'Photo introuvable'], 404);

    if (file_exists(public_path('images/' . $photo->nom_fichier)))
        unlink(public_path('images/' . $photo->nom_fichier));
    if ($photo->nom_fichier_filigrane !== $photo->nom_fichier
        && file_exists(public_path('images/' . $photo->nom_fichier_filigrane)))
        unlink(public_path('images/' . $photo->nom_fichier_filigrane));
    if (file_exists(storage_path('app/originals/' . $photo->nom_fichier)))
        unlink(storage_path('app/originals/' . $photo->nom_fichier));

    DB::table('photo')->where('id_photo', $id)->delete();
    return response()->json(['statut' => 'ok']);
});

// ── Admin : liste des utilisateurs ───────────────────────────────────
Route::get('/admin/utilisateurs', function (Request $request) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    return response()->json(
        DB::table('utilisateur')
            ->select('id_utilisateur as id', 'pseudo', 'nom', 'prenom', 'role', 'credits', 'actif')
            ->orderBy('date_inscription', 'desc')
            ->get()
    );
});

// ── Admin : suspendre / réactiver un utilisateur ──────────────────────
Route::post('/admin/utilisateurs/{id}/suspendre', function (Request $request, $id) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $adminId = $request->input('admin_id');
    if (!DB::table('utilisateur')->where('id_utilisateur', $adminId)->where('role', 'admin')->exists())
        return response()->json(['erreur' => 'Accès réservé aux administrateurs'], 403);

    $user = DB::table('utilisateur')->where('id_utilisateur', $id)->first();
    if (!$user) return response()->json(['erreur' => 'Utilisateur introuvable'], 404);
    if ($user->role === 'admin') return response()->json(['erreur' => 'Impossible de modifier un administrateur'], 403);

    $newActif = !$user->actif;
    DB::table('utilisateur')->where('id_utilisateur', $id)->update(['actif' => $newActif]);
    return response()->json(['statut' => 'ok', 'actif' => $newActif]);
});

// ── Admin : supprimer un utilisateur ─────────────────────────────────
Route::delete('/admin/utilisateurs/{id}', function (Request $request, $id) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $adminId = $request->query('admin_id');
    if (!DB::table('utilisateur')->where('id_utilisateur', $adminId)->where('role', 'admin')->exists())
        return response()->json(['erreur' => 'Accès réservé aux administrateurs'], 403);

    $user = DB::table('utilisateur')->where('id_utilisateur', $id)->first();
    if (!$user) return response()->json(['erreur' => 'Utilisateur introuvable'], 404);
    if ($user->role === 'admin') return response()->json(['erreur' => 'Impossible de supprimer un administrateur'], 403);

    DB::table('utilisateur')->where('id_utilisateur', $id)->delete();
    return response()->json(['statut' => 'ok']);
});

// ── Admin : liste des catégories ──────────────────────────────────────
Route::get('/admin/categories', function (Request $request) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    return response()->json(
        DB::table('categorie')->select('id_categorie as id', 'libelle as nom')->orderBy('libelle')->get()
    );
});

// ── Admin : ajouter une catégorie ────────────────────────────────────
Route::post('/admin/categories', function (Request $request) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $adminId = $request->input('admin_id');
    if (!DB::table('utilisateur')->where('id_utilisateur', $adminId)->where('role', 'admin')->exists())
        return response()->json(['erreur' => 'Accès réservé aux administrateurs'], 403);

    $nom = trim($request->input('nom', ''));
    if (empty($nom)) return response()->json(['erreur' => 'Nom de catégorie requis'], 422);
    if (DB::table('categorie')->where('libelle', $nom)->exists())
        return response()->json(['erreur' => 'Cette catégorie existe déjà'], 400);

    $newId = DB::table('categorie')->insertGetId(['libelle' => $nom, 'description' => null]);
    return response()->json(['statut' => 'ok', 'id' => $newId, 'nom' => $nom]);
});

// ── Admin : supprimer une catégorie ──────────────────────────────────
Route::delete('/admin/categories/{id}', function (Request $request, $id) {
    if ($request->header('X-API-KEY') !== CLE_API)
        return response()->json(['erreur' => 'Clé API invalide'], 401);

    $adminId = $request->query('admin_id');
    if (!DB::table('utilisateur')->where('id_utilisateur', $adminId)->where('role', 'admin')->exists())
        return response()->json(['erreur' => 'Accès réservé aux administrateurs'], 403);

    if (!DB::table('categorie')->where('id_categorie', $id)->exists())
        return response()->json(['erreur' => 'Catégorie introuvable'], 404);
    if (DB::table('photo')->where('id_categorie', $id)->count() > 0)
        return response()->json(['erreur' => 'Impossible de supprimer une catégorie contenant des photos'], 400);

    DB::table('categorie')->where('id_categorie', $id)->delete();
    return response()->json(['statut' => 'ok']);
});
