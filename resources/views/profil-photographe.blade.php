@extends('layouts.app')

@section('title', $photographe->prenom . ' ' . $photographe->nom . ' - Photographe')

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-body d-flex align-items-center gap-4">
        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white"
             style="width:80px; height:80px; font-size:2rem;">
            {{ strtoupper(substr($photographe->prenom, 0, 1)) }}{{ strtoupper(substr($photographe->nom, 0, 1)) }}
        </div>
        <div>
            <h3 class="mb-0">{{ $photographe->prenom }} {{ $photographe->nom }}</h3>
            <p class="text-muted mb-1"><i class="bi bi-camera"></i> Photographe — AtelierLumière</p>
            <span class="badge bg-success">{{ $photos->total() }} photo(s) en vente</span>
        </div>
    </div>
</div>

<h4 class="mb-3">Photos de {{ $photographe->prenom }}</h4>

<div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
    @forelse($photos as $photo)
        <div class="col">
            <div class="card h-100 shadow-sm">
                <a href="{{ route('photo.show', $photo->id_photo) }}">
                    <img src="{{ asset('images/' . $photo->nom_fichier_filigrane) }}"
                         class="card-img-top" style="height:180px; object-fit:cover;"
                         alt="{{ $photo->description }}">
                </a>
                <div class="card-body">
                    <h6>{{ Str::limit($photo->description, 40) }}</h6>
                    <p class="mb-0"><span class="badge bg-secondary">{{ $photo->categorie->libelle ?? '—' }}</span></p>
                    <p class="mt-2 fw-bold text-primary"><i class="bi bi-coin"></i> {{ $photo->prix }} crédit(s)</p>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-4">
            <p>Aucune photo disponible.</p>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $photos->links() }}
</div>
@endsection
