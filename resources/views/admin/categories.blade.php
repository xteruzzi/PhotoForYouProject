@extends('layouts.app')

@section('title', 'Gestion des catégories - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tags"></i> Gestion des catégories</h2>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Tableau de bord
    </a>
</div>

<div class="row g-4">
    {{-- Formulaire d'ajout --}}
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Ajouter une catégorie</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.categories.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Libellé</label>
                        <input type="text" name="libelle" class="form-control @error('libelle') is-invalid @enderror"
                               value="{{ old('libelle') }}" placeholder="Ex: Architecture" required>
                        @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <small class="text-muted">(optionnelle)</small></label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Description de la catégorie">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Liste des catégories --}}
    <div class="col-md-8">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Libellé</th>
                        <th>Description</th>
                        <th>Photos</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td>{{ $cat->id_categorie }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.categories.update', $cat->id_categorie) }}"
                                  class="d-flex gap-2 align-items-center">
                                @csrf @method('PATCH')
                                <input type="text" name="libelle" class="form-control form-control-sm"
                                       value="{{ $cat->libelle }}" style="min-width:130px">
                                <button type="submit" class="btn btn-outline-secondary btn-sm" title="Sauvegarder">
                                    <i class="bi bi-floppy"></i>
                                </button>
                            </form>
                        </td>
                        <td class="small text-muted">{{ Str::limit($cat->description, 40) ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ $cat->photos_count }}</span></td>
                        <td>
                            @if($cat->photos_count === 0)
                                <form method="POST" action="{{ route('admin.categories.destroy', $cat->id_categorie) }}"
                                      onsubmit="return confirm('Supprimer la catégorie {{ $cat->libelle }} ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small" title="Non supprimable — contient des photos">
                                    <i class="bi bi-lock"></i>
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucune catégorie.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
