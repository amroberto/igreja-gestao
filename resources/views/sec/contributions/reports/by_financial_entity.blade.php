@extends('adminlte::page')

@section('title', __('Relatório de Contribuições por Entidade Financeira'))

@section('content_header')
    <h1>{{ __('Relatório de Contribuições por Entidade Financeira') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('sec.dashboard') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sec.contributions.index') }}">{{ __('Contribuições') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Relatório por Entidade Financeira') }}</li>
        </ol>
    </nav>

    @if (session('error'))
        <x-adminlte-alert theme="danger" title="{{ __('Erro') }}" dismissable>
            {{ session('error') }}
        </x-adminlte-alert>
    @endif

    <x-adminlte-card title="{{ __('Gerar Relatório') }}" theme="primary" collapsible>
        <form action="{{ route('sec.contributions.generate-report-by-financial-entity') }}" method="POST">
            @csrf
            <div class="row">
                <!-- Entidade Financeira -->
                <div class="col-md-4 col-sm-12 mb-3">
                    <label class="form-label">{{ __('Entidade Financeira') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                        <select class="form-control @error('financial_entity_id') is-invalid @enderror" id="financial_entity_id" name="financial_entity_id" required>
                            @if ($financialEntities->isEmpty())
                                <option value="">{{ __('Nenhuma entidade disponível') }}</option>
                            @else
                                <option value="">{{ __('Selecione uma entidade') }}</option>
                                @foreach ($financialEntities as $entity)
                                    <option value="{{ $entity->id }}" {{ old('financial_entity_id') == $entity->id ? 'selected' : '' }}>
                                        {{ $entity->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @error('financial_entity_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <!-- Data Inicial -->
                <div class="col-md-4 col-sm-12 mb-3">
                    <label class="form-label">{{ __('Data Inicial') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', now()->startOfMonth()->format('Y-m-d')) }}" required>
                    </div>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <!-- Data Final -->
                <div class="col-md-4 col-sm-12 mb-3">
                    <label class="form-label">{{ __('Data Final') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', now()->endOfMonth()->format('Y-m-d')) }}" required>
                    </div>
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-file-pdf mr-1"></i> {{ __('Gerar PDF') }}</button>
            <a href="{{ route('sec.contributions.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> {{ __('Voltar') }}</a>
        </form>
    </x-adminlte-card>
</div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('form').on('submit', function(e) {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                const financialEntityId = $('#financial_entity_id').val();

                if (!financialEntityId && {{ $financialEntities->count() > 0 ? 'true' : 'false' }}) {
                    e.preventDefault();
                    $('#financial_entity_id').addClass('is-invalid');
                    $('#financial_entity_id').siblings('.invalid-feedback').text('{{ __("Selecione uma entidade financeira.") }}');
                }
                if (endDate < startDate) {
                    e.preventDefault();
                    $('#end_date').addClass('is-invalid');
                    $('#end_date').siblings('.invalid-feedback').text('{{ __("A data final deve ser posterior ou igual à data inicial.") }}');
                }
            });
        });
    </script>
@stop