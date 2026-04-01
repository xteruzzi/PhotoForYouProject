@extends('layouts.app')

@section('title', 'Gestion des utilisateurs - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> Gestion des utilisateurs</h2>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Tableau de bord
    </a>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Crédits</th>
                <th>Inscription</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr class="{{ !$user->actif ? 'table-secondary text-muted' : '' }}">
                <td>{{ $user->id_utilisateur }}</td>
                <td>{{ $user->prenom }} {{ $user->nom }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->isAdmin())
                        <span class="badge bg-danger"><i class="bi bi-shield-lock"></i> Admin</span>
                    @elseif($user->isPhotographe())
                        <span class="badge bg-success"><i class="bi bi-camera"></i> Photographe</span>
                    @else
                        <span class="badge bg-primary"><i class="bi bi-person"></i> Client</span>
                    @endif
                </td>
                <td><i class="bi bi-coin"></i> {{ $user->credits }}</td>
                <td class="small text-muted">{{ \Carbon\Carbon::parse($user->date_inscription)->format('d/m/Y') }}</td>
                <td>
                    @if($user->actif)
                        <span class="badge bg-success">Actif</span>
                    @else
                        <span class="badge bg-secondary">Stand-by</span>
                    @endif
                </td>
                <td>
                    @if(!$user->isAdmin())
                    <div class="d-flex gap-1">
                        <form method="POST" action="{{ route('admin.users.toggle', $user->id_utilisateur) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $user->actif ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    title="{{ $user->actif ? 'Mettre en stand-by' : 'Réactiver' }}">
                                <i class="bi bi-{{ $user->actif ? 'pause-circle' : 'play-circle' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $user->id_utilisateur) }}"
                              onsubmit="return confirm('Supprimer cet utilisateur et toutes ses données ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </td>
            </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Aucun utilisateur.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
