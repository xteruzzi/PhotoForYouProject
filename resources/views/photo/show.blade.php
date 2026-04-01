@extends('layouts.app')

@section('title', $photo->description . ' - PhotoForYou')

@section('content')
<div class="row">
    <div class="col-md-8">
        <img src="{{ asset('images/' . $photo->nom_fichier_filigrane) }}"
             class="img-fluid rounded shadow w-100"
             alt="{{ $photo->description }}">
        <p class="text-muted small mt-1"><i class="bi bi-info-circle"></i> Aperçu avec filigrane — la version originale haute résolution est téléchargeable après achat.</p>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4>{{ $photo->description }}</h4>
                <p class="text-muted">
                    <i class="bi bi-tag"></i> {{ $photo->categorie->libelle ?? 'Non classé' }}
                </p>
                <p>
                    <i class="bi bi-person-badge"></i>
                    <a href="{{ route('profil.photographe', $photo->utilisateur->id_utilisateur) }}">
                        {{ $photo->utilisateur->prenom }} {{ $photo->utilisateur->nom }}
                    </a>
                </p>
                <p class="text-muted small"><i class="bi bi-calendar3"></i> Déposée le {{ $photo->date_depot->format('d/m/Y') }}</p>

                <hr>

                @if($photo->en_vente)
                    <div class="text-center mb-3">
                        <span class="display-6 fw-bold text-primary">
                            <i class="bi bi-coin"></i> {{ $photo->prix }} crédit(s)
                        </span>
                        <br><small class="text-muted">soit {{ $photo->prix * 5 }} €</small>
                    </div>

                    @auth
                        @if(auth()->user()->isClient())
                            <form method="POST" action="{{ route('client.acheter', $photo->id_photo) }}"
                                  onsubmit="return confirm('Confirmer l\'achat pour {{ $photo->prix }} crédit(s) ?')">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    <i class="bi bi-cart-check"></i> Acheter cette photo
                                </button>
                            </form>
                            <div class="mt-2 text-center small text-muted">
                                Votre solde : <strong>{{ auth()->user()->credits }} crédit(s)</strong>
                                @if(auth()->user()->credits < $photo->prix)
                                    <br><a href="{{ route('client.credits') }}" class="text-danger">Crédits insuffisants — en acheter</a>
                                @endif
                            </div>
                        @elseif(auth()->user()->isPhotographe())
                            <div class="alert alert-info small">Les photographes ne peuvent pas acheter de photos.</div>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Connectez-vous pour acheter
                        </a>
                    @endauth
                @else
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-lock"></i> Cette photo a déjà été vendue.
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-body small text-muted">
                <i class="bi bi-shield-check text-success"></i> Vente exclusive — vous serez l'unique propriétaire.<br>
                <i class="bi bi-arrows-fullscreen text-primary"></i> Résolution originale sans filigrane fournie après achat.
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('catalogue') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Retour au catalogue
    </a>
</div>
@endsection
