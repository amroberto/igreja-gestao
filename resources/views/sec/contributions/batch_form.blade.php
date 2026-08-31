@extends('adminlte::page')

@section('title', 'Geração de Contribuições em Lote')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-file-invoice-dollar text-primary mr-2"></i>
        Geração em Lote de Cartas de Contribuições (Impressão/Correios)
    </h1>
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('sec.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sec.contributions.index') }}">Contribuições</a></li>
        <li class="breadcrumb-item active">Geração em Lote</li>
    </ol>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">

        {{-- Alerta de Erro --}}
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="icon fas fa-ban mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        {{-- Card Principal AdminLTE --}}
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-1"></i> Parâmetros para Processamento
                </h3>
            </div>

            <form action="{{ route('sec.contributions.batch-process') }}" method="POST">
                @csrf

                <div class="card-body">
                    <p class="text-muted">
                        Selecione o mês, ano e a comunidade desejada. O sistema criará as contribuições faltantes,
                        gerará os boletos no Asaas para quem utiliza esta forma de pagamento e empacotará os arquivos
                        <strong>PDF (Carta + Boleto)</strong> dos membros configurados para recebimento via
                        <strong>Correio</strong> em um arquivo <strong>ZIP</strong>.
                    </p>

                    <div class="row">
                        {{-- Mês Referência --}}
                        <div class="col-md-4 form-group">
                            <label for="month">Mês Referência <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                @php
                                $meses = [
                                1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                                5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                                9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                                ];
                                @endphp

                                <select name="month" id="month"
                                    class="form-control select2 @error('month') is-invalid @enderror" required>
                                    @foreach ($meses as $numero => $nome)
                                    <option value="{{ $numero }}" {{ date('n')==$numero ? 'selected' : '' }}>
                                        {{ sprintf('%02d', $numero) }} - {{ $nome }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('month')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Ano Referência --}}
                        <div class="col-md-4 form-group">
                            <label for="year">Ano Referência <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                                <input type="number" name="year" id="year"
                                    class="form-control @error('year') is-invalid @enderror" value="{{ date('Y') }}"
                                    min="2020" max="2099" required>
                            </div>
                            @error('year')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Comunidade --}}
                        <div class="col-md-4 form-group">
                            <label for="community_id">Comunidade <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-church"></i></span>
                                </div>
                                <select name="community_id" id="community_id"
                                    class="form-control select2 @error('community_id') is-invalid @enderror" required>
                                    <option value="">Selecione uma comunidade...</option>
                                    @foreach($communities as $community)
                                    <option value="{{ $community->id }}">{{ $community->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('community_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('sec.contributions.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-archive mr-1"></i> Processar Lote e Baixar ZIP
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Inicialização opcional de componentes como Select2 se você utilizar no painel
        if ($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }
    });
</script>
@stop