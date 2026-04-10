<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware CheckRole
 *
 * Vérifie que l'utilisateur connecté possède bien l'un des rôles
 * autorisés pour accéder à la route demandée.
 *
 * Contrôles effectués dans l'ordre :
 *  1. L'utilisateur est-il connecté ? (sinon redirection vers /login)
 *  2. Son rôle est-il dans la liste des rôles autorisés ? (sinon 403)
 *  3. Son compte est-il actif ? (sinon déconnexion forcée)
 *
 * Utilisation dans les routes :
 *  Route::middleware(['auth', 'role:client'])->group(...)
 *  Route::middleware(['auth', 'role:photographe,admin'])->group(...)
 *
 * @package App\Http\Middleware
 */
class CheckRole
{
    /**
     * Traite la requête entrante.
     *
     * @param  Request  $request  La requête HTTP entrante
     * @param  Closure  $next     Le prochain middleware ou contrôleur
     * @param  string   ...$roles Les rôles autorisés pour cette route.
     *                            Le "..." signifie qu'on peut passer plusieurs rôles :
     *                            middleware('role:client')  → $roles = ['client']
     *                            middleware('role:admin,photographe') → $roles = ['admin', 'photographe']
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        if (!in_array(Auth::user()->role, $roles)) {
            abort(403, 'Accès interdit. Vous n\'avez pas les droits nécessaires.');
        }

        // Vérification du statut du compte à chaque requête
        if (!Auth::user()->actif) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Votre compte a été désactivé. Contactez un administrateur.');
        }

        return $next($request);
    }
}
