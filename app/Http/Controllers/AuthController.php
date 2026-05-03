<?php

namespace App\Http\Controllers;

use App\Models\LogActivite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Contrôleur AuthController
 *
 * Gère l'authentification des utilisateurs :
 *  - Connexion avec pseudo et mot de passe
 *  - Inscription (client ou photographe)
 *  - Déconnexion
 *  - Redirection automatique selon le rôle
 *
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     *
     * Si l'utilisateur est déjà connecté, il est redirigé
     * directement vers son espace selon son rôle.
     *
     * @return View|RedirectResponse
     */
    // "View|RedirectResponse" signifie : la méthode retourne soit une vue, soit une redirection
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }
        return view('auth.login');
    }

    /**
     * Traite la tentative de connexion.
     *
     * Vérifie le pseudo, l'état du compte (actif/inactif)
     * puis tente l'authentification avec Laravel Auth.
     * En cas de succès, la session est régénérée pour éviter
     * la fixation de session.
     *
     * @param  Request  $request  La requête HTTP avec : pseudo, password
     * @return RedirectResponse
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'pseudo'   => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('pseudo', $request->pseudo)->first();

        if (!$user) {
            LogActivite::enregistrer('tentative_connexion', 'Pseudo inconnu : ' . $request->pseudo, null);
            return back()->withErrors(['pseudo' => 'Pseudo introuvable.'])->onlyInput('pseudo');
        }

        if (!$user->actif) {
            LogActivite::enregistrer('tentative_connexion', 'Compte désactivé : ' . $user->pseudo, $user->id_utilisateur);
            return back()->withErrors(['pseudo' => 'Ce compte est désactivé. Contactez un administrateur.'])->onlyInput('pseudo');
        }

        if (Auth::attempt(
            ['pseudo' => $request->pseudo, 'password' => $request->password],
            $request->boolean('remember')
        )) {
            $request->session()->regenerate();
            LogActivite::enregistrer('connexion', 'Connexion réussie — rôle : ' . $user->role);
            return $this->redirectByRole();
        }

        LogActivite::enregistrer('tentative_connexion', 'Mot de passe incorrect pour : ' . $user->pseudo, $user->id_utilisateur);
        return back()->withErrors(['pseudo' => 'Pseudo ou mot de passe incorrect.'])->onlyInput('pseudo');
    }

    /**
     * Affiche le formulaire d'inscription.
     *
     * Si l'utilisateur est déjà connecté, il est redirigé
     * vers son espace.
     *
     * @return View|RedirectResponse
     */
    // Même principe : soit on affiche le formulaire, soit on redirige si déjà connecté
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }
        return view('auth.register');
    }

    /**
     * Traite la création d'un nouveau compte utilisateur.
     *
     * Exigences du mot de passe :
     *  - Minimum 8 caractères
     *  - Au moins 1 majuscule
     *  - Au moins 1 minuscule
     *  - Au moins 1 chiffre
     *  - Au moins 1 caractère spécial
     *
     * Seuls les rôles 'client' et 'photographe' sont accessibles à l'inscription.
     * Le rôle 'admin' est réservé et ne peut être attribué que manuellement.
     *
     * @param  Request  $request  La requête HTTP avec : nom, prenom, pseudo, email, password, role
     * @return RedirectResponse
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'nom'      => 'required|string|max:100',
            'prenom'   => 'required|string|max:100',
            'pseudo'   => 'required|string|max:50|unique:utilisateur,pseudo|regex:/^[a-zA-Z0-9_\-\.]+$/',
            'email'    => 'required|email|unique:utilisateur,email',
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/[A-Z]/',              // au moins 1 majuscule
                'regex:/[a-z]/',              // au moins 1 minuscule
                'regex:/[0-9]/',              // au moins 1 chiffre
                'regex:/[@$!%*?&#_\-\.]/',    // au moins 1 caractère spécial
            ],
            'role'     => 'required|in:client,photographe',
        ], [
            'pseudo.unique'      => 'Ce pseudo est déjà utilisé. Choisissez-en un autre.',
            'pseudo.regex'       => 'Le pseudo ne peut contenir que des lettres, chiffres, tirets, points et underscores.',
            'email.unique'       => 'Cette adresse e-mail est déjà associée à un compte.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.regex'     => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
        ]);

        $user = User::create([
            'nom'      => $request->nom,
            'prenom'   => $request->prenom,
            'pseudo'   => $request->pseudo,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'credits'  => 0,
            'actif'    => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectByRole()->with('status', 'Bienvenue sur PhotoForYou, ' . $user->prenom . ' !');
    }

    /**
     * Déconnecte l'utilisateur.
     *
     * La session est invalidée et un nouveau token CSRF est généré
     * pour prévenir les attaques CSRF après déconnexion.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        LogActivite::enregistrer('deconnexion', 'Déconnexion : ' . Auth::user()->pseudo);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Vous êtes déconnecté(e). À bientôt !');
    }

    /**
     * Redirige l'utilisateur vers son tableau de bord selon son rôle.
     *
     * @return RedirectResponse
     */
    private function redirectByRole(): RedirectResponse
    {
        $role = Auth::user()->role;

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'photographe') {
            return redirect()->route('photographe.dashboard');
        } else {
            // client (rôle par défaut)
            return redirect()->route('client.dashboard');
        }
    }
}
