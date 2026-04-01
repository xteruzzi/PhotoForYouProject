@extends('layouts.app')

@section('title', 'Accueil - PhotoForYou')

@section('content')

{{-- ═══════════════════════════════════════════════════════════════════════════
     CARROUSEL DE PRÉSENTATION — AtelierLumière
═══════════════════════════════════════════════════════════════════════════ --}}
<div id="heroCarousel" class="carousel slide carousel-fade mb-5 rounded-4 overflow-hidden shadow-lg"
     data-bs-ride="carousel" data-bs-interval="4500">

    {{-- Indicateurs --}}
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3"></button>
    </div>

    <div class="carousel-inner">

        {{-- Slide 1 — Présentation générale --}}
        <div class="carousel-item active">
            <div class="carousel-img"
                 style="background-image: url('https://picsum.photos/seed/landscape1/1400/600');"></div>
            <div class="carousel-overlay"></div>
            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center h-100 text-center">
                <div class="carousel-caption-inner">
                    <span class="badge bg-warning text-dark mb-3 fs-6 px-3 py-2">
                        <i class="bi bi-camera2"></i> AtelierLumière — Paris 14ème
                    </span>
                    <h2 class="display-5 fw-bold text-white mb-3">
                        La photographie d'auteur,<br>disponible en ligne
                    </h2>
                    <p class="lead text-white-75 mb-4">
                        Découvrez des œuvres exclusives de photographes professionnels,<br>
                        disponibles en haute résolution pour tous vos projets.
                    </p>
                    <a href="{{ route('catalogue') }}" class="btn btn-primary btn-lg px-5 me-3">
                        <i class="bi bi-images"></i> Voir le catalogue
                    </a>
                    @guest
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg px-5">
                        <i class="bi bi-person-plus"></i> Rejoindre la communauté
                    </a>
                    @endguest
                </div>
            </div>
        </div>

        {{-- Slide 2 — Pour les clients --}}
        <div class="carousel-item">
            <div class="carousel-img"
                 style="background-image: url('https://picsum.photos/seed/office2/1400/600');"></div>
            <div class="carousel-overlay" style="background: linear-gradient(135deg, rgba(13,110,253,0.75) 0%, rgba(0,0,0,0.5) 100%);"></div>
            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center h-100 text-center">
                <div class="carousel-caption-inner">
                    <span class="badge bg-primary mb-3 fs-6 px-3 py-2">
                        <i class="bi bi-person-circle"></i> Pour les acheteurs
                    </span>
                    <h2 class="display-5 fw-bold text-white mb-3">
                        Des photos exclusives,<br>achetées une seule fois
                    </h2>
                    <p class="lead text-white-75 mb-4">
                        Chaque photographie est vendue en exemplaire unique.<br>
                        Vous êtes le seul propriétaire — en toute légalité.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <div class="bg-white bg-opacity-15 rounded-3 px-4 py-2 text-white">
                            <i class="bi bi-coin fs-4 text-warning"></i>
                            <div class="fw-bold mt-1">1 crédit = 5 €</div>
                        </div>
                        <div class="bg-white bg-opacity-15 rounded-3 px-4 py-2 text-white">
                            <i class="bi bi-arrows-fullscreen fs-4 text-info"></i>
                            <div class="fw-bold mt-1">1920×1280 px mini</div>
                        </div>
                        <div class="bg-white bg-opacity-15 rounded-3 px-4 py-2 text-white">
                            <i class="bi bi-download fs-4 text-success"></i>
                            <div class="fw-bold mt-1">Téléchargement immédiat</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Slide 3 — Pour les photographes --}}
        <div class="carousel-item">
            <div class="carousel-img"
                 style="background-image: url('https://picsum.photos/seed/camera3/1400/600');"></div>
            <div class="carousel-overlay" style="background: linear-gradient(135deg, rgba(25,135,84,0.8) 0%, rgba(0,0,0,0.5) 100%);"></div>
            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center h-100 text-center">
                <div class="carousel-caption-inner">
                    <span class="badge bg-success mb-3 fs-6 px-3 py-2">
                        <i class="bi bi-camera"></i> Pour les photographes
                    </span>
                    <h2 class="display-5 fw-bold text-white mb-3">
                        Valorisez votre travail,<br>monétisez votre art
                    </h2>
                    <p class="lead text-white-75 mb-4">
                        Déposez vos photos, fixez votre prix (2 à 100 crédits).<br>
                        Recevez 50 % du prix de vente à chaque achat.
                    </p>
                    @guest
                    <a href="{{ route('register') }}" class="btn btn-success btn-lg px-5">
                        <i class="bi bi-cloud-upload"></i> Devenir photographe partenaire
                    </a>
                    @else
                        @if(auth()->user()->isPhotographe())
                        <a href="{{ route('photographe.vendre') }}" class="btn btn-success btn-lg px-5">
                            <i class="bi bi-cloud-upload"></i> Déposer une photo
                        </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>

        {{-- Slide 4 — Studio AtelierLumière --}}
        <div class="carousel-item">
            <div class="carousel-img"
                 style="background-image: url('https://picsum.photos/seed/studio4/1400/600');"></div>
            <div class="carousel-overlay" style="background: linear-gradient(135deg, rgba(102,16,242,0.75) 0%, rgba(0,0,0,0.5) 100%);"></div>
            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center h-100 text-center">
                <div class="carousel-caption-inner">
                    <span class="badge bg-purple mb-3 fs-6 px-3 py-2" style="background-color:#6610f2!important">
                        <i class="bi bi-building"></i> Notre studio
                    </span>
                    <h2 class="display-5 fw-bold text-white mb-3">
                        AtelierLumière,<br>20 ans d'expertise photographique
                    </h2>
                    <p class="lead text-white-75 mb-4">
                        Tirage jet d'encre, développement RAW, retouche créative…<br>
                        Notre savoir-faire artisanal au service de votre image.
                    </p>
                    <div class="d-flex gap-4 justify-content-center text-white">
                        <div><div class="fs-1 fw-bold text-warning">2006</div><div class="small">Fondé en</div></div>
                        <div class="border-start border-white border-opacity-25 mx-2"></div>
                        <div><div class="fs-1 fw-bold text-warning">4</div><div class="small">Experts</div></div>
                        <div class="border-start border-white border-opacity-25 mx-2"></div>
                        <div><div class="fs-1 fw-bold text-warning">Paris</div><div class="small">14ème arr.</div></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Contrôles précédent / suivant --}}
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     STATS RAPIDES
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="row g-0 mb-5 rounded-3 overflow-hidden border shadow-sm">
    <div class="col-4 text-center py-3 bg-primary text-white">
        <div class="fs-2 fw-bold">{{ \App\Models\Photo::where('est_validee',true)->where('en_vente',true)->count() }}</div>
        <div class="small opacity-75">Photos disponibles</div>
    </div>
    <div class="col-4 text-center py-3 bg-success text-white border-start border-end border-white border-opacity-25">
        <div class="fs-2 fw-bold">{{ \App\Models\User::where('role','photographe')->count() }}</div>
        <div class="small opacity-75">Photographes</div>
    </div>
    <div class="col-4 text-center py-3 bg-warning text-dark">
        <div class="fs-2 fw-bold">{{ \App\Models\Categorie::count() }}</div>
        <div class="small opacity-75">Catégories</div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     CATÉGORIES
═══════════════════════════════════════════════════════════════════════════ --}}
<h3 class="mb-3 fw-bold"><i class="bi bi-tags"></i> Parcourir par catégorie</h3>
<div class="row row-cols-2 row-cols-md-4 g-3 mb-5">
    @forelse($categories as $cat)
        <div class="col">
            <a href="{{ route('catalogue', ['categorie' => $cat->id_categorie]) }}" class="text-decoration-none">
                <div class="card h-100 text-center category-card shadow-sm border-0">
                    <div class="card-body py-4">
                        <i class="bi bi-image fs-2 text-primary mb-2 d-block"></i>
                        <h6 class="card-title fw-bold mb-1">{{ $cat->libelle }}</h6>
                        @if($cat->photos_count > 0)
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                {{ $cat->photos_count }} photo(s)
                            </span>
                        @else
                            <span class="badge bg-light text-muted">Bientôt disponible</span>
                        @endif
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12"><p class="text-muted">Aucune catégorie disponible.</p></div>
    @endforelse
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     DERNIÈRES PHOTOS AJOUTÉES
═══════════════════════════════════════════════════════════════════════════ --}}
@php
    $dernieres = \App\Models\Photo::with(['utilisateur','categorie'])
        ->where('est_validee', true)->where('en_vente', true)
        ->orderBy('date_depot','desc')->take(4)->get();
@endphp

@if($dernieres->isNotEmpty())
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold mb-0"><i class="bi bi-clock-history"></i> Dernières photos</h3>
    <a href="{{ route('catalogue') }}" class="btn btn-outline-primary btn-sm">
        Voir tout le catalogue <i class="bi bi-arrow-right"></i>
    </a>
</div>
<div class="row row-cols-1 row-cols-md-4 g-3 mb-5">
    @foreach($dernieres as $photo)
    <div class="col">
        <div class="card h-100 shadow-sm border-0">
            <a href="{{ route('photo.show', $photo->id_photo) }}">
                <img src="{{ asset('images/' . $photo->nom_fichier_filigrane) }}"
                     class="card-img-top" style="height:160px; object-fit:cover;"
                     alt="{{ $photo->description }}">
            </a>
            <div class="card-body p-2">
                <p class="small fw-semibold mb-1">{{ Str::limit($photo->description, 40) }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-secondary small">{{ $photo->categorie->libelle }}</span>
                    <span class="text-primary fw-bold small">
                        <i class="bi bi-coin"></i> {{ $photo->prix }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════
     AVANTAGES
═══════════════════════════════════════════════════════════════════════════ --}}
<h3 class="fw-bold mb-3"><i class="bi bi-star"></i> Pourquoi PhotoForYou ?</h3>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm text-center p-3">
            <i class="bi bi-shield-check fs-1 text-success mb-2"></i>
            <h6 class="fw-bold">Exclusivité totale</h6>
            <p class="text-muted small mb-0">Chaque photo vendue une seule fois. Vous en êtes l'unique propriétaire.</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm text-center p-3">
            <i class="bi bi-arrows-fullscreen fs-1 text-primary mb-2"></i>
            <h6 class="fw-bold">Haute résolution</h6>
            <p class="text-muted small mb-0">Format JPEG, résolution minimale garantie pour tous usages pro.</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm text-center p-3">
            <i class="bi bi-coin fs-1 text-warning mb-2"></i>
            <h6 class="fw-bold">Crédits flexibles</h6>
            <p class="text-muted small mb-0">1 crédit = 5 €. Achetez le nombre qui vous convient, sans abonnement.</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm text-center p-3">
            <i class="bi bi-patch-check fs-1 text-info mb-2"></i>
            <h6 class="fw-bold">Photos validées</h6>
            <p class="text-muted small mb-0">Chaque photo est vérifiée par notre équipe avant publication.</p>
        </div>
    </div>
</div>

@guest
<div class="alert alert-primary text-center border-0 shadow-sm">
    <i class="bi bi-info-circle"></i>
    <a href="{{ route('register') }}" class="fw-bold">Inscrivez-vous gratuitement</a>
    pour acheter des photos ou déposer les vôtres en tant que photographe.
</div>
@endguest

@endsection
