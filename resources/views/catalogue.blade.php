@extends('layouts.app')

@section('title', 'Catalogue - PhotoForYou')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h2><i class="bi bi-images"></i> Catalogue</h2>
    <small class="text-muted">{{ $photos->total() }} photo(s) disponible(s)</small>
</div>

{{-- Filtres --}}
<form action="{{ route('catalogue') }}" method="GET" class="row g-2 mb-4">
    <div class="col-md-4">
        <input type="text" name="recherche" class="form-control" placeholder="Rechercher une photo..."
               value="{{ request('recherche') }}">
    </div>
    <div class="col-md-3">
        <select name="categorie" class="form-select" onchange="this.form.submit()">
            <option value="">Toutes les catégories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id_categorie }}"
                    {{ request('categorie') == $cat->id_categorie ? 'selected' : '' }}>
                    {{ $cat->libelle }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Rechercher</button>
    </div>
    @if(request('categorie') || request('recherche'))
        <div class="col-auto">
            <a href="{{ route('catalogue') }}" class="btn btn-outline-secondary">Réinitialiser</a>
        </div>
    @endif
</form>

<div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
    @forelse($photos as $photo)
        <div class="col">
            <div class="card h-100 shadow-sm">
                <a href="{{ route('photo.show', $photo->id_photo) }}">
                    <img src="{{ asset('images/' . $photo->nom_fichier_filigrane) }}"
                         class="card-img-top"
                         alt="{{ $photo->description }}"
                         style="height: 200px; object-fit: cover;">
                </a>
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title">{{ Str::limit($photo->description, 50) }}</h6>
                    <p class="mb-1">
                        <span class="badge bg-secondary">{{ $photo->categorie->libelle ?? 'Non classé' }}</span>
                    </p>
                    <p class="small text-muted mb-2">
                        Par : <a href="{{ route('profil.photographe', $photo->utilisateur->id_utilisateur) }}" class="text-decoration-none">
                            {{ $photo->utilisateur->prenom ?? '' }} {{ $photo->utilisateur->nom ?? 'Inconnu' }}
                        </a>
                    </p>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-primary fs-5">
                            <i class="bi bi-coin"></i> {{ $photo->prix }} crédit(s)
                        </span>
                        @auth
                            @if(auth()->user()->isClient())
                                <form method="POST" action="{{ route('client.acheter', $photo->id_photo) }}"
                                      onsubmit="return confirm('Acheter cette photo pour {{ $photo->prix }} crédit(s) ?')">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-cart-plus"></i> Acheter
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">Non disponible</span>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-box-arrow-in-right"></i> Connexion
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-image fs-1 text-muted"></i>
            <p class="lead text-muted mt-2">Aucune photo ne correspond à votre recherche.</p>
            <a href="{{ route('catalogue') }}" class="btn btn-primary">Voir tout le catalogue</a>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $photos->appends(request()->query())->links() }}
</div>
@endsection
