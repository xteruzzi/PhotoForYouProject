<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PhotoForYou')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary fs-4" href="{{ route('home') }}">
            <i class="bi bi-camera2"></i> PhotoForYou
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('catalogue') ? 'active' : '' }}" href="{{ route('catalogue') }}">Catalogue</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center">
                @auth
                    {{-- Affiche le solde de crédits --}}
                    @if(auth()->user()->isClient())
                        <li class="nav-item me-2">
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="bi bi-coin"></i> {{ auth()->user()->credits }} crédit(s)
                            </span>
                        </li>
                    @elseif(auth()->user()->isPhotographe())
                        <li class="nav-item me-2">
                            <span class="badge bg-success fs-6">
                                <i class="bi bi-coin"></i> {{ auth()->user()->credits }} crédit(s)
                            </span>
                        </li>
                    @endif

                    {{-- Menu selon le rôle --}}
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-shield-lock"></i> Admin
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.photos') }}"><i class="bi bi-image"></i> Valider les photos</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.users') }}"><i class="bi bi-people"></i> Utilisateurs</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.categories') }}"><i class="bi bi-tags"></i> Catégories</a></li>
                            </ul>
                        </li>
                    @elseif(auth()->user()->isPhotographe())
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('photographe.*') ? 'active' : '' }}" href="{{ route('photographe.dashboard') }}">
                                <i class="bi bi-person-badge"></i> Mon espace
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('photographe.vendre') }}">
                                <i class="bi bi-cloud-upload"></i> Déposer une photo
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('client.*') ? 'active' : '' }}" href="{{ route('client.dashboard') }}">
                                <i class="bi bi-person-circle"></i> Mon espace
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('client.credits') }}">
                                <i class="bi bi-plus-circle"></i> Acheter des crédits
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <span class="nav-link text-muted small">{{ auth()->user()->prenom }}</span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm ms-1">
                                <i class="bi bi-box-arrow-right"></i> Déconnexion
                            </button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm ms-2" href="{{ route('register') }}">Inscription</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="container my-4">
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

<footer class="bg-dark text-white text-center py-3 mt-5">
    <small>&copy; {{ date('Y') }} PhotoForYou — AtelierLumière, Paris 14ème</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
