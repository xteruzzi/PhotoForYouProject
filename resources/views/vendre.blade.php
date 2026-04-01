@extends('layouts.app')

@section('title', 'Déposer une photo - PhotoForYou')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-success text-white py-3">
                <h4 class="mb-0"><i class="bi bi-cloud-upload"></i> Déposer une photo à vendre</h4>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Exigences techniques :</strong> Format JPEG ou PNG, résolution minimale 2400×1600 px, taille maximale 30 Mo.<br>
                    Un filigrane sera automatiquement ajouté à l'aperçu public. Votre photo sera publiée après validation par un administrateur.
                </div>

                <form action="{{ route('photographe.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Titre / Description</label>
                        <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                               placeholder="Ex: Coucher de soleil sur la mer Méditerranée"
                               value="{{ old('description') }}" required>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prix en crédits <small class="text-muted">(2 à 100 — 1 crédit = 5 €)</small></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-coin"></i></span>
                                <input type="number" name="prix" min="2" max="100"
                                       class="form-control @error('prix') is-invalid @enderror"
                                       value="{{ old('prix', 10) }}" required>
                                <span class="input-group-text">crédit(s)</span>
                            </div>
                            @error('prix')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Catégorie</label>
                            <select name="id_categorie" class="form-select @error('id_categorie') is-invalid @enderror" required>
                                <option value="">-- Choisir une catégorie --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id_categorie }}" {{ old('id_categorie') == $cat->id_categorie ? 'selected' : '' }}>
                                        {{ $cat->libelle }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_categorie')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Fichier image (JPEG / PNG)</label>
                        <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror"
                               accept=".jpg,.jpeg,.png" required>
                        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2">
                        <i class="bi bi-send"></i> Envoyer pour validation
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
