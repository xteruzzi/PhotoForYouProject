@extends('layouts.app')

@section('title', 'Inscription - PhotoForYou')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-7">
        <div class="card shadow">
            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0"><i class="bi bi-person-plus"></i> Créer un compte</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf

                    {{-- Nom / Prénom --}}
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label fw-semibold">Nom</label>
                            <input type="text" name="nom"
                                   class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom') }}" placeholder="Dupont" required>
                            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col">
                            <label class="form-label fw-semibold">Prénom</label>
                            <input type="text" name="prenom"
                                   class="form-control @error('prenom') is-invalid @enderror"
                                   value="{{ old('prenom') }}" placeholder="Marie" required>
                            @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Pseudo --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pseudo <small class="text-muted">(visible publiquement)</small></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-at"></i></span>
                            <input type="text" name="pseudo" id="pseudo"
                                   class="form-control @error('pseudo') is-invalid @enderror"
                                   value="{{ old('pseudo') }}"
                                   placeholder="marie_dupont"
                                   oninput="checkPseudo(this)"
                                   autocomplete="username" required>
                            <span class="input-group-text" id="pseudoStatus"></span>
                            @error('pseudo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text text-muted">Lettres, chiffres, tirets ( - ), points ( . ), underscores ( _ ) uniquement.</div>
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <small class="text-muted">(privé, non affiché)</small></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="votre@email.fr"
                                   autocomplete="email" required>
                        </div>
                        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Mot de passe --}}
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="••••••••"
                                   oninput="checkPassword()"
                                   autocomplete="new-password" required>
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePassword('password', this)" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Barre de force du mot de passe --}}
                    <div class="mb-1">
                        <div class="progress" style="height: 8px;">
                            <div id="strengthBar" class="progress-bar" role="progressbar"
                                 style="width: 0%; transition: width 0.3s, background-color 0.3s;"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small id="strengthLabel" class="text-muted">Entrez un mot de passe</small>
                        </div>
                    </div>

                    {{-- Critères visuels --}}
                    <div class="card bg-light border-0 mb-3 px-3 py-2">
                        <div class="row row-cols-2 g-1 small">
                            <div class="col" id="crit-length">
                                <i class="bi bi-x-circle-fill text-danger me-1"></i>8 caractères minimum
                            </div>
                            <div class="col" id="crit-upper">
                                <i class="bi bi-x-circle-fill text-danger me-1"></i>Une lettre majuscule
                            </div>
                            <div class="col" id="crit-lower">
                                <i class="bi bi-x-circle-fill text-danger me-1"></i>Une lettre minuscule
                            </div>
                            <div class="col" id="crit-number">
                                <i class="bi bi-x-circle-fill text-danger me-1"></i>Un chiffre (0–9)
                            </div>
                            <div class="col" id="crit-symbol">
                                <i class="bi bi-x-circle-fill text-danger me-1"></i>Un symbole (@$!%*?&#_-.)
                            </div>
                            <div class="col" id="crit-match" style="display:none">
                                <i class="bi bi-x-circle-fill text-danger me-1"></i>Mots de passe identiques
                            </div>
                        </div>
                    </div>

                    {{-- Confirmation --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control"
                                   placeholder="••••••••"
                                   oninput="checkConfirm()"
                                   autocomplete="new-password" required>
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePassword('password_confirmation', this)" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Rôle --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Je m'inscris en tant que</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="role" id="role_client" value="client"
                                       {{ old('role', 'client') === 'client' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary w-100 py-3" for="role_client">
                                    <i class="bi bi-person-circle fs-4 d-block mb-1"></i>
                                    <strong>Client</strong><br>
                                    <small class="text-muted">J'achète des photos</small>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="role" id="role_photographe" value="photographe"
                                       {{ old('role') === 'photographe' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success w-100 py-3" for="role_photographe">
                                    <i class="bi bi-camera2 fs-4 d-block mb-1"></i>
                                    <strong>Photographe</strong><br>
                                    <small class="text-muted">Je vends mes photos</small>
                                </label>
                            </div>
                        </div>
                        @error('role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" id="submitBtn" class="btn btn-success w-100 py-2" disabled>
                        <i class="bi bi-person-check"></i> Créer mon compte
                    </button>
                </form>
                <hr>
                <p class="text-center mb-0">
                    Déjà inscrit ? <a href="{{ route('login') }}" class="fw-semibold">Connexion</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// ── Afficher / masquer le mot de passe ────────────────────────────────────────
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

// ── Validation du pseudo ──────────────────────────────────────────────────────
function checkPseudo(input) {
    const status = document.getElementById('pseudoStatus');
    const regex  = /^[a-zA-Z0-9_\-\.]+$/;
    if (input.value.length === 0) {
        status.innerHTML = '';
    } else if (!regex.test(input.value)) {
        status.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
    } else if (input.value.length < 3) {
        status.innerHTML = '<i class="bi bi-exclamation-circle-fill text-warning"></i>';
        input.classList.remove('is-invalid', 'is-valid');
    } else {
        status.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
    }
}

// ── Évaluation du mot de passe ────────────────────────────────────────────────
const criteria = {
    length:  { regex: /.{8,}/,                         id: 'crit-length' },
    upper:   { regex: /[A-Z]/,                          id: 'crit-upper'  },
    lower:   { regex: /[a-z]/,                          id: 'crit-lower'  },
    number:  { regex: /[0-9]/,                          id: 'crit-number' },
    symbol:  { regex: /[@$!%*?&#_\-\.]/,               id: 'crit-symbol' },
};

function updateCriterion(id, passed) {
    const el   = document.getElementById(id);
    const icon = el.querySelector('i');
    if (passed) {
        icon.className = 'bi bi-check-circle-fill text-success me-1';
        el.classList.add('text-success');
        el.classList.remove('text-muted');
    } else {
        icon.className = 'bi bi-x-circle-fill text-danger me-1';
        el.classList.remove('text-success');
        el.classList.add('text-muted');
    }
}

function checkPassword() {
    const pwd    = document.getElementById('password').value;
    const bar    = document.getElementById('strengthBar');
    const label  = document.getElementById('strengthLabel');

    let score = 0;
    for (const key in criteria) {
        const ok = criteria[key].regex.test(pwd);
        if (ok) score++;
        updateCriterion(criteria[key].id, ok);
    }

    const pct = (score / 5) * 100;
    bar.style.width = pct + '%';

    const levels = [
        { max: 0,  color: '#dc3545', label: '' },
        { max: 1,  color: '#dc3545', label: 'Très faible' },
        { max: 2,  color: '#fd7e14', label: 'Faible' },
        { max: 3,  color: '#ffc107', label: 'Moyen' },
        { max: 4,  color: '#20c997', label: 'Fort' },
        { max: 5,  color: '#198754', label: 'Très fort' },
    ];
    const lvl = levels[score];
    bar.style.backgroundColor = lvl.color;
    label.textContent  = lvl.label;
    label.style.color  = lvl.color;

    // Afficher le critère "correspondance" dès qu'on tape dans confirmation
    const confirmEl = document.getElementById('crit-match');
    if (document.getElementById('password_confirmation').value.length > 0) {
        confirmEl.style.display = 'block';
    }

    checkConfirm();
    updateSubmitBtn(score);
}

function checkConfirm() {
    const pwd     = document.getElementById('password').value;
    const confirm = document.getElementById('password_confirmation').value;
    const el      = document.getElementById('crit-match');

    if (confirm.length === 0) {
        el.style.display = 'none';
        return;
    }

    el.style.display = 'block';
    updateCriterion('crit-match', pwd === confirm);
    updateSubmitBtn();
}

function updateSubmitBtn() {
    const pwd     = document.getElementById('password').value;
    const confirm = document.getElementById('password_confirmation').value;
    const pseudo  = document.getElementById('pseudo').value;
    const btn     = document.getElementById('submitBtn');

    const allCriteriaMet = Object.values(criteria).every(c => c.regex.test(pwd));
    const pseudoOk = /^[a-zA-Z0-9_\-\.]{3,}$/.test(pseudo);
    const match    = pwd === confirm && confirm.length > 0;

    btn.disabled = !(allCriteriaMet && match && pseudoOk);
}
</script>
@endsection
