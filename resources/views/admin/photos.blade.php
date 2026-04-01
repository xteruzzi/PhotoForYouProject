@extends('layouts.app')

@section('title', 'Gestion des photos - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-images"></i> Gestion des photos</h2>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Tableau de bord
    </a>
</div>

{{-- Filtres rapides --}}
<div class="btn-group mb-3">
    <a href="{{ route('admin.photos') }}" class="btn btn-outline-secondary {{ !request('filtre') ? 'active' : '' }}">Toutes</a>
    <a href="{{ route('admin.photos', ['filtre' => 'attente']) }}" class="btn btn-outline-warning {{ request('filtre') === 'attente' ? 'active' : '' }}">En attente</a>
    <a href="{{ route('admin.photos', ['filtre' => 'validee']) }}" class="btn btn-outline-success {{ request('filtre') === 'validee' ? 'active' : '' }}">Validées</a>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Aperçu</th>
                <th>Description</th>
                <th>Photographe</th>
                <th>Catégorie</th>
                <th>Prix</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($photos as $photo)
            <tr class="{{ !$photo->est_validee ? 'table-warning' : '' }}">
                <td>
                    <img src="{{ asset('images/' . $photo->nom_fichier_filigrane) }}"
                         style="height:55px; width:75px; object-fit:cover;" class="rounded">
                </td>
                <td>{{ Str::limit($photo->description, 35) }}</td>
                <td>{{ $photo->utilisateur->prenom ?? '—' }} {{ $photo->utilisateur->nom ?? '' }}</td>
                <td><span class="badge bg-secondary">{{ $photo->categorie->libelle ?? '—' }}</span></td>
                <td><i class="bi bi-coin"></i> {{ $photo->prix }}</td>
                <td class="small text-muted">{{ \Carbon\Carbon::parse($photo->date_depot)->format('d/m/Y') }}</td>
                <td>
                    @if(!$photo->est_validee)
                        <span class="badge bg-warning text-dark">En attente</span>
                    @elseif(!$photo->en_vente)
                        <span class="badge bg-success">Vendue</span>
                    @else
                        <span class="badge bg-primary">En vente</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-1 flex-wrap">
                        @if(!$photo->est_validee)
                            <form method="POST" action="{{ route('admin.photos.valider', $photo->id_photo) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm" title="Valider">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.photos.refuser', $photo->id_photo) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-warning btn-sm" title="Retirer">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('photo.show', $photo->id_photo) }}" class="btn btn-info btn-sm" title="Voir">
                            <i class="bi bi-eye"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.photos.destroy', $photo->id_photo) }}"
                              onsubmit="return confirm('Supprimer définitivement cette photo ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Aucune photo.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
