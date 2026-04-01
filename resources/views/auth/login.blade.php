@extends('layouts.app')

@section('title', 'Connexion - PhotoForYou')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0"><i class="bi bi-box-arrow-in-right"></i> Connexion</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pseudo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-at"></i></span>
                            <input type="text" name="pseudo"
                                   class="form-control @error('pseudo') is-invalid @enderror"
                                   value="{{ old('pseudo') }}"
                                   placeholder="votre_pseudo"
                                   autocomplete="username"
                                   required autofocus>
                            @error('pseudo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="loginPassword"
                                   class="form-control" placeholder="••••••••"
                                   autocomplete="current-password" required>
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePassword('loginPassword', this)" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-box-arrow-in-right"></i> Se connecter
                    </button>
                </form>
                <hr>
                <p class="text-center mb-0">
                    Pas encore de compte ?
                    <a href="{{ route('register') }}" class="fw-semibold">Inscription</a>
                </p>
            </div>
        </div>

        <div class="card mt-3 border-info">
            <div class="card-header bg-info bg-opacity-10 text-info fw-semibold small">
                <i class="bi bi-info-circle"></i> Comptes de démonstration
            </div>
            <div class="card-body py-2 small">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Rôle</th><th>Pseudo</th><th>Mot de passe</th></tr></thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-danger">Admin</span></td>
                            <td><code>superadmin</code></td>
                            <td><code>Admin@1234</code></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">Photographe</span></td>
                            <td><code>flippo_photo</code></td>
                            <td><code>Photo@1234</code></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-primary">Client</span></td>
                            <td><code>marie_d</code></td>
                            <td><code>Client@1234</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
@endsection
