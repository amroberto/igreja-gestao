@extends('adminlte::page')

@section('title', 'Visualizar Configuração Mensal')

@section('content_header')
    <h1>Visualizar Configuração Mensal</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detalhes da Configuração Mensal</h3>
            <div class="card-tools">
                <a href="{{ route('sec.monthly-configs.preview', $monthlyConfig) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-pdf"></i> Visualizar PDF
                </a>
                <a href="{{ route('sec.monthly-configs.edit', $monthlyConfig) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="{{ route('sec.monthly-configs.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Comunidade:</strong> {{ $monthlyConfig->community->name ?? 'Comunidade não encontrada' }}</p>
                    <p><strong>Data de Referência:</strong> {{ $monthlyConfig->reference }}</p>
                    <p><strong>Lema Bíblico:</strong> {{ $monthlyConfig->motto }}</p>
                </div>
                <div class="col-md-6">
                    @if ($monthlyConfig->image_path)
                        <p><strong>Imagem:</strong></p>
                        <img src="{{ $monthlyConfig->image_url }}" alt="Imagem da configuração" style="max-width: 300px;">
                    @endif
                </div>
            </div>
            <div class="mt-3">
                <p><strong>Observações:</strong></p>
                <div>{!! $monthlyConfig->observations !!}</div>
            </div>
        </div>
    </div>
@endsection
