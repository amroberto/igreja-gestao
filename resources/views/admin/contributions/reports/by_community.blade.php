@extends('adminlte::page')

@section('title', __('Relatório de Contribuições por Comunidade'))

@section('content_header')
<h1>{{ __('Relatório de Contribuições por Comunidade') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.contributions.index') }}">{{ __('Contribuições') }}</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Relatório por Comunidade') }}</li>
        </ol>
    </nav>

    @if (session('error'))
    <x-adminlte-alert theme="danger" title="{{ __('Erro') }}" dismissable>
        {{ session('error') }}
    </x-adminlte-alert>
    @endif

    <x-adminlte-card title="{{ __('Gerar Relatório') }}" theme="primary" collapsible>
        <form action="{{ route('admin.contributions.generateReportByCommunity') }}" method="POST">
            @csrf

            <div class="row">

                <!-- Comunidade -->
                <div class="mb-3 col-md-4 col-sm-12">
                    <label class="form-label">{{ __('Comunidade') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-users"></i></span>
                        <select class="form-select @error('community_id') is-invalid @enderror" id="community_id"
                            name="community_id" required>
                            <option value="">{{ __('Selecione uma comunidade') }}</option>
                            @foreach ($communities as $community)
                            <option value="{{ $community->id }}" {{ old('community_id')==$community->id ? 'selected' :
                                '' }}>
                                {{ $community->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('community_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Data Inicial -->
                <div class="mb-3 col-md-4 col-sm-12">
                    <label class="form-label">{{ __('Data Inicial') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                            id="start_date" name="start_date"
                            value="{{ old('start_date', now()->startOfMonth()->format('Y-m-d')) }}" required>
                        @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Data Final -->
                <div class="mb-3 col-md-4 col-sm-12">
                    <label class="form-label">{{ __('Data Final') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date"
                            name="end_date" value="{{ old('end_date', now()->endOfMonth()->format('Y-m-d')) }}"
                            required>
                        @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

            <div class="mt-4">
                <button type="submit" name="format" value="pdf" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> Gerar PDF
                </button>
                <button type="submit" name="format" value="excel" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
                <button type="submit" name="format" value="html" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-code"></i> Visualizar HTML
                </button>
                <a href="{{ route('admin.contributions.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
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

                if (endDate < startDate) {
                    e.preventDefault();
                    $('#end_date').addClass('is-invalid');
                    $('#end_date').siblings('.invalid-feedback').text('{{ __("A data final deve ser posterior ou igual à data inicial.") }}');
                }
            });
        });
</script>
@stop