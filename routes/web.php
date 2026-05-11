<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\EspacePhotographeController;
use App\Http\Controllers\EspaceClientController;
use App\Http\Controllers\AdminController;
use App\Models\Categorie;

// ── Page d'accueil ────────────────────────────────────────────────────────────
Route::get('/', function () {
    $categories = Categorie::withCount(['photos' => fn($q) => $q->where('est_validee', true)->where('en_vente', true)])
        ->orderBy('libelle')
        ->get();
    return view('welcome', compact('categories'));
})->name('home');

// ── Catalogue (public) ────────────────────────────────────────────────────────
Route::get('/catalogue', [PhotoController::class, 'index'])->name('catalogue');
Route::get('/photo/{id}', [PhotoController::class, 'show'])->name('photo.show');

// ── Profil public d'un photographe ───────────────────────────────────────────
Route::get('/photographe/{id}', function (int $id) {
    $photographe = \App\Models\User::where('id_utilisateur', $id)
        ->where('role', 'photographe')
        ->where('actif', true)
        ->firstOrFail();
    $photos = \App\Models\Photo::with('categorie')
        ->where('id_utilisateur', $id)
        ->where('est_validee', true)
        ->where('en_vente', true)
        ->paginate(12);
    return view('profil-photographe', compact('photographe', 'photos'));
})->name('profil.photographe')->where('id', '[0-9]+');

// ── Authentification ──────────────────────────────────────────────────────────
// throttle:5,1 = 5 tentatives maximum par minute par IP (anti brute-force)
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Espace Client ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/',                [EspaceClientController::class, 'index'])->name('dashboard');
    Route::get('/credits',         [EspaceClientController::class, 'showAchatCredits'])->name('credits');
    Route::post('/credits',        [EspaceClientController::class, 'acheterCredits'])->name('credits.store');
    Route::post('/acheter/{id}',   [PhotoController::class, 'acheter'])->name('acheter');
    Route::get('/download/{id}',   [PhotoController::class, 'telecharger'])->name('download');
});

// ── Espace Photographe ────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:photographe'])->prefix('photographe')->name('photographe.')->group(function () {
    Route::get('/',                        [EspacePhotographeController::class, 'index'])->name('dashboard');
    Route::get('/vendre',                  [PhotoController::class, 'create'])->name('vendre');
    Route::post('/vendre',                 [PhotoController::class, 'store'])->name('store');
    Route::delete('/photo/{id}',           [EspacePhotographeController::class, 'destroy'])->name('photo.destroy');
    Route::patch('/photo/{id}/prix',       [EspacePhotographeController::class, 'updatePrix'])->name('photo.prix');
    Route::post('/paiement',               [EspacePhotographeController::class, 'demanderPaiement'])->name('paiement');
});

// ── Administration ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',                            [AdminController::class, 'index'])->name('dashboard');

    // Utilisateurs
    Route::get('/utilisateurs',                [AdminController::class, 'utilisateurs'])->name('users');
    Route::patch('/utilisateurs/{id}/actif',   [AdminController::class, 'toggleActif'])->name('users.toggle');
    Route::delete('/utilisateurs/{id}',        [AdminController::class, 'destroyUser'])->name('users.destroy');

    // Photos
    Route::get('/photos',                      [AdminController::class, 'photos'])->name('photos');
    Route::patch('/photos/{id}/valider',       [AdminController::class, 'validerPhoto'])->name('photos.valider');
    Route::patch('/photos/{id}/refuser',       [AdminController::class, 'refuserPhoto'])->name('photos.refuser');
    Route::delete('/photos/{id}',              [AdminController::class, 'destroyPhoto'])->name('photos.destroy');

    // Catégories
    Route::get('/categories',                  [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories',                 [AdminController::class, 'storeCategorie'])->name('categories.store');
    Route::patch('/categories/{id}',           [AdminController::class, 'updateCategorie'])->name('categories.update');
    Route::delete('/categories/{id}',          [AdminController::class, 'destroyCategorie'])->name('categories.destroy');

    // Journal d'activité (logs des triggers)
    Route::get('/logs',                        [AdminController::class, 'logs'])->name('logs');
});
