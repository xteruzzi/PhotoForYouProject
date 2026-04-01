@extends('layouts.app')

@section('title', 'Mon espace client - PhotoForYou')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-circle"></i> Mon espace — {{ $user->prenom }} {{ $user->nom }}</h2>
    <a href="{{ route('client.credits') }}" class="btn btn-warning">
        <i class="bi bi-plus-circle"></i> Acheter des crédits
    </a>
</div>

{{-- Solde --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-center border-warning">
            <div class="card-body">
                <i class="bi bi-coin fs-2 text-warning"></i>
                <h3 class="mt-2">{{ $user->credits }}</h3>
                <p class="text-muted mb-0">Crédits disponibles ({{ $user->credits * 5 }} €)</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-primary">
            <div class="card-body">
                <i class="bi bi-cart-check fs-2 text-primary"></i>
                <h3 class="mt-2">{{ $commandes->count() }}</h3>
                <p class="text-muted mb-0">Photos achetées</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-success">
            <div class="card-body">
                <i class="bi bi-download fs-2 text-success"></i>
                <h3 class="mt-2">{{ $commandes->count() }}</h3>
                <p class="text-muted mb-0">Téléchargements disponibles</p>
            </div>
        </div>
    </div>
</div>

<h4 class="mb-3"><i class="bi bi-archive"></i> Mes photos achetées</h4>

@if($commandes->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-cart fs-1"></i>
        <p class="mt-2">Vous n'avez encore acheté aucune photo.</p>
        <a href="{{ route('catalogue') }}" class="btn btn-primary">Parcourir le catalogue</a>
    </div>
@else
<div class="row row-cols-1 row-cols-md-3 g-4">
    @foreach($commandes as $commande)
        @if($commande->photo)
        <div class="col">
            <div class="card h-100 shadow-sm">
                <img src="{{ asset('images/' . $commande->photo->nom_fichier_filigrane) }}"
                     class="card-img-top" style="height:180px; object-fit:cover;"
                     alt="{{ $commande->photo->description }}">
                <div class="card-body">
                    <h6>{{ $commande->photo->description }}</h6>
                    <p class="text-muted small mb-1">
                        <i class="bi bi-tag"></i> {{ $commande->photo->categorie->libelle ?? '—' }}
                    </p>
                    <p class="text-muted small mb-1">
                        <i class="bi bi-person"></i>
                        {{ $commande->photo->utilisateur->prenom ?? '' }} {{ $commande->photo->utilisateur->nom ?? '' }}
                    </p>
                    <p class="text-muted small">
                        <i class="bi bi-calendar3"></i> Acheté le {{ \Carbon\Carbon::parse($commande->date_achat)->format('d/m/Y') }}
                        — <i class="bi bi-coin"></i> {{ $commande->credits_debites }} crédit(s)
                    </p>
                </div>
                <div class="card-footer">
                    <a href="{{ route('client.download', $commande->photo->id_photo) }}"
                       class="btn btn-success w-100">
                        <i class="bi bi-download"></i> Télécharger l'original
                    </a>
                </div>
            </div>
        </div>
        @endif
    @endforeach
</div>
@endif
@endsection
