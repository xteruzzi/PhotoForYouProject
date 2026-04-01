@extends('layouts.app')

@section('title', 'Acheter des crédits - PhotoForYou')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark py-3">
                <h4 class="mb-0"><i class="bi bi-coin"></i> Acheter des crédits</h4>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Votre solde actuel : <strong>{{ $user->credits }} crédit(s)</strong> ({{ $user->credits * 5 }} €)<br>
                    <strong>1 crédit = 5 €</strong>
                </div>

                <form method="POST" action="{{ route('client.credits.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre de crédits à acheter</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-coin"></i></span>
                            <input type="number" name="nb_credits" id="nb_credits" min="1" max="500"
                                   class="form-control @error('nb_credits') is-invalid @enderror"
                                   value="{{ old('nb_credits', 10) }}"
                                   oninput="updateTotal()" required>
                            <span class="input-group-text">crédit(s)</span>
                        </div>
                        @error('nb_credits')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Raccourcis rapides --}}
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setCredits(5)">5 crédits (25 €)</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setCredits(10)">10 crédits (50 €)</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setCredits(20)">20 crédits (100 €)</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setCredits(50)">50 crédits (250 €)</button>
                    </div>

                    <div class="alert alert-secondary" id="total_display">
                        Total : <strong id="total_txt">50 €</strong>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Moyen de paiement</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="moyen_paiement" id="paiement_carte" value="carte" checked>
                                <label class="form-check-label" for="paiement_carte">
                                    <i class="bi bi-credit-card"></i> Carte bancaire
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="moyen_paiement" id="paiement_paypal" value="paypal">
                                <label class="form-check-label" for="paiement_paypal">
                                    <i class="bi bi-paypal"></i> PayPal
                                </label>
                            </div>
                        </div>
                        @error('moyen_paiement')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Simulation de paiement</strong> — Les crédits sont ajoutés immédiatement (pas de vrai paiement en mode démo).
                    </div>

                    <button type="submit" class="btn btn-warning w-100 py-2 fw-bold">
                        <i class="bi bi-check-circle"></i> Confirmer l'achat
                    </button>
                </form>
            </div>
        </div>
        <div class="mt-3 text-center">
            <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Retour à mon espace
            </a>
        </div>
    </div>
</div>

<script>
function updateTotal() {
    const nb = parseInt(document.getElementById('nb_credits').value) || 0;
    document.getElementById('total_txt').textContent = (nb * 5) + ' €';
}
function setCredits(n) {
    document.getElementById('nb_credits').value = n;
    updateTotal();
}
</script>
@endsection
