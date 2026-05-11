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
