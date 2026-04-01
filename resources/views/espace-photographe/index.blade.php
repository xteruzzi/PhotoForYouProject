@extends('layouts.app')

@section('title', 'Mon espace photographe - PhotoForYou')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-badge"></i> Espace photographe — {{ $user->prenom }} {{ $user->nom }}</h2>
    <a href="{{ route('photographe.vendre') }}" class="btn btn-success">
        <i class="bi bi-cloud-upload"></i> Déposer une photo
    </a>
</div>

{{-- Solde --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-center border-success">
            <div class="card-body">
                <i class="bi bi-coin fs-2 text-success"></i>
                <h3 class="mt-2">{{ number_format($user->credits, 2) }}</h3>
                <p class="text-muted mb-0">Crédits disponibles ({{ number_format($user->credits * 5, 2) }} €)</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-primary">
            <div class="card-body">
                <i class="bi bi-images fs-2 text-primary"></i>
                <h3 class="mt-2">{{ $photos->count() }}</h3>
                <p class="text-muted mb-0">Photos déposées</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-warning">
            <div class="card-body">
                <i class="bi bi-clock-history fs-2 text-warning"></i>
                <h3 class="mt-2">{{ $photos->where('est_validee', false)->count() }}</h3>
                <p class="text-muted mb-0">En attente de validation</p>
            </div>
        </div>
    </div>
</div>

{{-- Demande de paiement --}}
@if($user->credits >= 10)
<div class="card border-success mb-4">
    <div class="card-body">
        <h5 class="text-success"><i class="bi bi-cash-coin"></i> Demander un paiement</h5>
        <p>Vous avez <strong>{{ $user->credits }} crédit(s)</strong> soit <strong>{{ $user->credits * 5 }} €</strong>.</p>
        <form method="POST" action="{{ route('photographe.paiement') }}" class="d-flex gap-3 align-items-center">
            @csrf
            <select name="moyen" class="form-select w-auto">
                <option value="cheque">Par chèque</option>
                <option value="paypal">Via PayPal</option>
            </select>
            <button type="submit" class="btn btn-success"
                    onclick="return confirm('Confirmer la demande de paiement de {{ $user->credits * 5 }} € ?')">
                <i class="bi bi-send"></i> Demander le paiement
            </button>
        </form>
    </div>
</div>
@else
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Vous pourrez demander un paiement dès que vous aurez plus de <strong>10 crédits</strong>
    (actuellement : {{ $user->credits }} crédit(s)).
</div>
@endif

{{-- Mes photos --}}
<h4 class="mb-3"><i class="bi bi-grid"></i> Mes photos</h4>

@if($photos->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-image fs-1"></i>
        <p class="mt-2">Vous n'avez encore déposé aucune photo.</p>
        <a href="{{ route('photographe.vendre') }}" class="btn btn-primary">Déposer ma première photo</a>
    </div>
@else
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Aperçu</th>
                <th>Description</th>
                <th>Catégorie</th>
                <th>Prix</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($photos as $photo)
            <tr>
                <td>
                    <img src="{{ asset('images/' . $photo->nom_fichier_filigrane) }}"
                         style="height:60px; width:80px; object-fit:cover;" class="rounded">
                </td>
                <td>{{ Str::limit($photo->description, 40) }}</td>
                <td><span class="badge bg-secondary">{{ $photo->categorie->libelle ?? '—' }}</span></td>
                <td>
                    <form method="POST" action="{{ route('photographe.photo.prix', $photo->id_photo) }}"
                          class="d-flex gap-1 align-items-center">
                        @csrf @method('PATCH')
                        <input type="number" name="prix" value="{{ $photo->prix }}" min="2" max="100"
                               class="form-control form-control-sm" style="width:75px">
                        <button type="submit" class="btn btn-outline-secondary btn-sm" title="Modifier le prix">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </form>
                </td>
                <td>
                    @if(!$photo->est_validee)
                        <span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> En attente</span>
                    @elseif(!$photo->en_vente)
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Vendue</span>
                    @else
                        <span class="badge bg-primary"><i class="bi bi-cart"></i> En vente</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('photo.show', $photo->id_photo) }}" class="btn btn-outline-info btn-sm" title="Voir">
                        <i class="bi bi-eye"></i>
                    </a>
                    @if(!$photo->commandes()->exists())
                    <form method="POST" action="{{ route('photographe.photo.destroy', $photo->id_photo) }}"
                          class="d-inline" onsubmit="return confirm('Supprimer définitivement cette photo ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
