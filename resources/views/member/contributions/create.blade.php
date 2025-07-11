@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="mb-0">Registrar Contribuição para <b><i>{{ $member->name }} ({{ formatCpf($member->cpf) }})</i></b></h4>
        </div>
        <div class="card-body">
          @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          @endif
          @if(session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          @endif

          <form action="{{ route('member.contributions.store') }}" method="POST" id="contribution-form" target="_self">
            @csrf

            {{-- Comunidade e Data --}}
            <div class="row mb-4">
              <h5 class="mb-3">Detalhes da Contribuição</h5>
              <div class="col-md-6 mb-3">
                <label for="community_id" class="form-label">Comunidade</label>
                <select id="community_id" name="community_id" class="form-select" required>
                  <option value="">Selecione uma Comunidade</option>
                  @foreach($communities as $community)
                  <option value="{{ $community->id }}" {{ old('community_id', $member->community_id) == $community->id ? 'selected' : '' }}>
                    {{ $community->name }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Data da Contribuição</label>
                <input type="text" class="form-control" value="{{ now()->format('d/m/Y') }}" readonly>
                <input type="hidden" name="contribution_date" value="{{ now()->format('Y-m-d') }}">
              </div>
            </div>

            {{-- Período --}}
            <div class="row mb-4">
              <h5 class="mb-3">Tipo de Período</h5>
              <div class="col-md-12">
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="period_type" id="period_monthly" value="monthly" checked>
                  <label class="form-check-label" for="period_monthly">Mensal</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="period_type" id="period_range" value="range">
                  <label class="form-check-label" for="period_range">Período Específico</label>
                </div>
              </div>
            </div>

            <div id="monthly-period" class="row mb-4">
              <div class="col-md-4">
                <label for="start_date_monthly" class="form-label">Mês/Ano</label>
                <input type="month" id="start_date_monthly" name="start_date" class="form-control" value="{{ old('start_date', now()->format('Y-m')) }}">
              </div>
            </div>

            <div id="range-period" class="row mb-4 d-none">
              <div class="col-md-4">
                <label for="start_date_range" class="form-label">Início</label>
                <input type="month" id="start_date_range" class="form-control" name="start_date">
              </div>
              <div class="col-md-4">
                <label for="end_date" class="form-label">Fim</label>
                <input type="month" id="end_date" class="form-control" name="end_date">
              </div>
            </div>

            {{-- Valor --}}
            <div class="row mb-4">
              <div class="col-md-4">
                <label for="amount" class="form-label">Valor (R$)</label>
                <input type="text" name="amount" id="amount" class="form-control" required>
              </div>
            </div>

            {{-- payment_method oculto --}}
            <input type="hidden" name="payment_method" id="payment_method" value="">

            {{-- Botões --}}
            <div class="d-flex justify-content-center gap-3 mt-4">
              <button type="button" class="btn btn-success" onclick="submitForm('pix')">
                <i class="bi bi-qr-code"></i> Contribuir via Pix
              </button>
              <button type="button" class="btn btn-secondary" onclick="submitForm('boleto_bancario')">
                <i class="bi bi-receipt"></i> Gerar Boleto
              </button>
              <button type="button" class="btn btn-primary" onclick="submitForm('creditcard')">
                <i class="bi bi-credit-card"></i> Cartão de Crédito
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  function togglePeriodFields() {
    const isMonthly = document.querySelector('#period_monthly').checked;
    document.getElementById('monthly-period').classList.toggle('d-none', !isMonthly);
    document.getElementById('range-period').classList.toggle('d-none', isMonthly);
  }

  document.addEventListener('DOMContentLoaded', function () {
    $('#amount').mask('000.000.000,00', {reverse:true});

    document.querySelectorAll('input[name="period_type"]').forEach(el => {
      el.addEventListener('change', togglePeriodFields);
    });
    togglePeriodFields();
  });

  function submitForm(paymentMethod) {
    const form = document.getElementById('contribution-form');
    const amountField = document.getElementById('amount');

    // Ajusta valor para formato numérico (ex: 1000.50)
    let val = amountField.value.replace(/\./g, '').replace(',', '.');
    amountField.value = val;

    // Set payment method hidden
    document.getElementById('payment_method').value = paymentMethod;

    if(paymentMethod === 'boleto_bancario') {
      // abrir em nova aba
      form.target = '_blank';
      form.submit();
      form.target = '_self'; // reset
    } else if(paymentMethod === 'pix') {
      // abrir popup para pix
      const popup = window.open('', 'PixQRCode', 'width=400,height=450');
      form.target = 'PixQRCode';
      form.submit();
      form.target = '_self';
      // o backend deve retornar uma view que exiba o QR code e info para pix neste popup
    } else {
      // cartão ou outros: submit normal
      form.target = '_self';
      form.submit();
    }
  }
</script>
@endsection
