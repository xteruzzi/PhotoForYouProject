@extends('layouts.app')

@section('title', 'Administration - PhotoForYou')

@section('content')
<h2><i class="bi bi-shield-lock"></i> Tableau de bord — Administration</h2>

<div class="row g-4 mt-2 mb-4">
    <div class="col-md-2-4 col-sm-6">
        <div class="card text-center border-primary shadow-sm">
            <div class="card-body">
                <i class="bi bi-people fs-2 text-primary"></i>
                <h3 class="mt-1">{{ $stats['users'] }}</h3>
                <p class="text-muted mb-0">Utilisateurs</p>
                <a href="{{ route('admin.users') }}" class="btn btn-outline-primary btn-sm mt-2">Gérer</a>
            </div>
        </div>
    </div>
    <div class="col-md-2-4 col-sm-6">
        <div class="card text-center border-success shadow-sm">
            <div class="card-body">
                <i class="bi bi-images fs-2 text-success"></i>
                <h3 class="mt-1">{{ $stats['photos'] }}</h3>
                <p class="text-muted mb-0">Photos totales</p>
                <a href="{{ route('admin.photos') }}" class="btn btn-outline-success btn-sm mt-2">Gérer</a>
            </div>
        </div>
    </div>
    <div class="col-md-2-4 col-sm-6">
        <div class="card text-center border-warning shadow-sm">
            <div class="card-body">
                <i class="bi bi-hourglass-split fs-2 text-warning"></i>
                <h3 class="mt-1">{{ $stats['en_attente'] }}</h3>
                <p class="text-muted mb-0">En attente</p>
                <a href="{{ route('admin.photos') }}" class="btn btn-outline-warning btn-sm mt-2">Valider</a>
            </div>
        </div>
    </div>
    <div class="col-md-2-4 col-sm-6">
        <div class="card text-center border-info shadow-sm">
            <div class="card-body">
                <i class="bi bi-cart-check fs-2 text-info"></i>
                <h3 class="mt-1">{{ $stats['commandes'] }}</h3>
                <p class="text-muted mb-0">Commandes</p>
            </div>
        </div>
    </div>
    <div class="col-md-2-4 col-sm-6">
        <div class="card text-center border-secondary shadow-sm">
            <div class="card-body">
                <i class="bi bi-tags fs-2 text-secondary"></i>
                <h3 class="mt-1">{{ $stats['categories'] }}</h3>
                <p class="text-muted mb-0">Catégories</p>
                <a href="{{ route('admin.categories') }}" class="btn btn-outline-secondary btn-sm mt-2">Gérer</a>
            </div>
        </div>
    </div>
</div>

@if($stats['en_attente'] > 0)
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle"></i>
    <strong>{{ $stats['en_attente'] }} photo(s)</strong> en attente de validation.
    <a href="{{ route('admin.photos') }}" class="alert-link ms-2">Valider maintenant</a>
</div>
@endif

<div class="list-group mt-3">
    <a href="{{ route('admin.photos') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
        <i class="bi bi-image fs-4 text-success"></i>
        <div>
            <strong>Valider / gérer les photos</strong>
            <div class="text-muted small">Approuver, refuser ou supprimer les photos du catalogue</div>
        </div>
    </a>
    <a href="{{ route('admin.users') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
        <i class="bi bi-people fs-4 text-primary"></i>
        <div>
            <strong>Gérer les utilisateurs</strong>
            <div class="text-muted small">Activer/désactiver ou supprimer des comptes</div>
        </div>
    </a>
    <a href="{{ route('admin.categories') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
        <i class="bi bi-tags fs-4 text-secondary"></i>
        <div>
            <strong>Gérer les catégories</strong>
            <div class="text-muted small">Ajouter, modifier ou supprimer des catégories</div>
        </div>
    </a>
</div>
@endsection
