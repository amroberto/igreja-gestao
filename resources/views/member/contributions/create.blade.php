@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">{{ __('Registrar Contribuição para') }} <b><i>{{ $member->name }}</i></b></h4>
                </div>

                <div class="card-body">
                    @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <form action="{{ route('member.contributions.store') }}" method="POST" id="contribution-form">
                        @csrf

                        <!-- Informações do Membro -->
                        <div class="row mb-4">
                            <h5 class="mb-3">{{ __('Informações do Membro') }}</h5>
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">{{ __('Nome Completo') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input id="name" type="text" name="name" class="form-control" value="{{ old('name', $member->name) }}" readonly>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="cpf" class="form-label">{{ __('CPF') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-file-person"></i></span>
                                    <input id="cpf" type="text" name="cpf" class="form-control" value="{{ old('cpf', $member->cpf) }}" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Comunidade e Data da Contribuição -->
                        <div class="row mb-4">
                            <h5 class="mb-3">{{ __('Detalhes da Contribuição') }}</h5>
                            <div class="col-md-6 mb-3">
                                <label for="community_id" class="form-label">{{ __('Comunidade') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
                                    <select id="community_id" name="community_id" class="form-select" required>
                                        <option value="">{{ __('Selecione uma Comunidade') }}</option>
                                        @foreach ($communities as $community)
                                        <option value="{{ $community->id }}" {{ old('community_id', $member->community_id) == $community->id ? 'selected' : '' }}>
                                            {{ $community->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-input-error :messages="$errors->get('community_id')" class="text-danger small" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="contribution_date_display" class="form-label">{{ __('Data da Contribuição') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                    <input id="contribution_date_display" type="text" class="form-control" value="{{ now()->format('d/m/Y') }}" readonly>
                                    <input id="contribution_date" type="hidden" name="contribution_date" value="{{ now()->format('Y-m-d') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Período (Botões de Rádio) -->
                        <div class="row mb-4">
                            <h5 class="mb-3">{{ __('Tipo de Período') }}</h5>
                            <div class="col-md-12 mb-3">
                                <div class="d-flex gap-4 align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="period_type" id="period_monthly" value="monthly" {{ old('period_type', 'monthly') == 'monthly' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="period_monthly">{{ __('Mensal') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="period_type" id="period_range" value="range" {{ old('period_type') == 'range' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="period_range">{{ __('Período Específico') }}</label>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('period_type')" class="text-danger small" />
                            </div>
                        </div>

                        <!-- Período Mensal -->
                        <div id="monthly-period" class="row mb-4 {{ old('period_type', 'monthly') == 'monthly' ? '' : 'd-none' }}">
                            <div class="col-md-6 mb-3">
                                <label for="start_date_monthly" class="form-label">{{ __('Mês/Ano') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar-month"></i></span>
                                    <input id="start_date_monthly" type="month" name="start_date" class="form-control" value="{{ old('start_date', now()->format('Y-m')) }}">
                                </div>
                                <x-input-error :messages="$errors->get('start_date')" class="text-danger small" />
                            </div>
                        </div>

                        <!-- Período Específico -->
                        <div id="range-period" class="row mb-4 {{ old('period_type') == 'range' ? '' : 'd-none' }}">
                            <div class="col-md-6 mb-3">
                                <label for="start_date_range" class="form-label">{{ __('Mês/Ano Inicial') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar-month"></i></span>
                                    <input id="start_date_range" type="month" name="start_date" class="form-control" value="{{ old('start_date') }}">
                                </div>
                                <x-input-error :messages="$errors->get('start_date')" class="text-danger small" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">{{ __('Mês/Ano Final') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar-month"></i></span>
                                    <input id="end_date" type="month" name="end_date" class="form-control" value="{{ old('end_date') }}">
                                </div>
                                <x-input-error :messages="$errors->get('end_date')" class="text-danger small" />
                            </div>
                        </div>

                        <!-- Valor e Método de Pagamento -->
                        <div class="row mb-12">
                            <h5 class="mb-3">{{ __('Pagamento') }}</h5>
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">{{ __('Valor (R$)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input id="amount" type="text" name="amount" class="form-control" value="{{ old('amount') }}" required>
                                </div>
                                <x-input-error :messages="$errors->get('amount')" class="text-danger small" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">{{ __('Forma de Pagamento') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                    <select id="payment_method" name="payment_method" class="form-select" required>
                                        <option value="">{{ __('Selecione a Forma de Pagamento') }}</option>
                                        <option value="pix" {{ old('payment_method') == 'pix' ? 'selected' : '' }}>{{ __('Pix') }}</option>
                                        <option value="creditcard" {{ old('payment_method') == 'creditcard' ? 'selected' : '' }}>{{ __('Cartão de Crédito') }}</option>
                                        <option value="boleto_bancario" {{ old('payment_method') == 'boleto_bancario' ? 'selected' : '' }}>{{ __('Boleto Bancário') }}</option>
                                    </select>
                                </div>
                                <x-input-error :messages="$errors->get('payment_method')" class="text-danger small" />
                            </div>
                        </div>

                        <!-- Descrição -->
                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">{{ __('Descrição (Opcional)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-textarea"></i></span>
                                    <input id="description" type="text" name="description" class="form-control" value="{{ old('description') }}">
                                </div>
                                <x-input-error :messages="$errors->get('description')" class="text-danger small" />
                            </div>
                        </div>

                        <!-- Botão de Envio -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> {{ __('Registrar Contribuição') }}
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
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery não carregado.');
            return;
        }
        if (!$.fn.mask) {
            console.error('jQuery Mask Plugin não carregado.');
            return;
        }

        // Máscaras
        $('#cpf').mask('000.000.000-00');
        $('#amount').mask('000.000.000,00', {
            reverse: true,
            placeholder: 'R$ 0,00'
        });

        // Toggle entre período mensal e intervalo
        function togglePeriodFields() {
            const isMonthly = $('input[name="period_type"]:checked').val() === 'monthly';
            $('#monthly-period').toggleClass('d-none', !isMonthly);
            $('#range-period').toggleClass('d-none', isMonthly);

            // Gerenciar obrigatoriedade
            $('#start_date_monthly').prop('required', isMonthly);
            $('#start_date_range').prop('required', !isMonthly);
            $('#end_date').prop('required', !isMonthly);

            // Desabilitar campo não ativo para evitar envio
            $('#start_date_monthly').prop('disabled', !isMonthly);
            $('#start_date_range').prop('disabled', isMonthly);
            $('#end_date').prop('disabled', isMonthly);
        }

        // Inicializar estado dos campos
        togglePeriodFields();

        // Atualizar ao mudar seleção
        $('input[name="period_type"]').on('change', togglePeriodFields);

        // Normalizar valores antes do envio
        $('#contribution-form').on('submit', function(e) {
            const amount = $('#amount').val().replace(/[^\d,]/g, '').replace(',', '.');
            $('#amount').val(amount);
        });
    });
</script>

<style>
    .form-control,
    .form-select,
    .form-check-input {
        padding: 0.75rem;
    }

    .mb-3 {
        margin-bottom: 1.5rem !important;
    }

    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        margin-top: 0.25rem;
    }

    .form-control[readonly] {
        background-color: #e9ecef;
        cursor: not-allowed;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-check-label {
        cursor: pointer;
    }
</style>
@endsection
