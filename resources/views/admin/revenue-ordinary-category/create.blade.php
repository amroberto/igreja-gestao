@extends('adminlte::page')

@section('title', __('Cadastro de Entidade Financeira'))

@section('content_header')
<h1 class="m-0 text-dark">{{ __('Cadastro Outras Receitas Ordinárias') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('sec.dashboard') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sec.revenue-ordinary-categories.index') }}">{{ __('Categoria - Outras Receitas Ordinárias') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Cadastrar') }}</li>
        </ol>
    </nav>

    <!-- Alertas -->
    @if (session('success'))
    <x-adminlte-alert theme="success" title="{{ __('Sucesso') }}" dismissable>
        {{ session('success') }}
    </x-adminlte-alert>
    @elseif (session('error'))
    <x-adminlte-alert theme="danger" title="{{ __('Erro') }}" dismissable>
        {{ session('error') }}
    </x-adminlte-alert>
    @endif

    <!-- Formulário -->
    <form action="{{ route('admin.revenue-ordinary-categories.store') }}" method="POST">
        @csrf
        <x-adminlte-card title="{{ __('Cadastro de Categoria Outras Receitas Ordinarias') }}" theme="primary" collapsible>
            <!-- Informações Gerais -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>{{ __('Informações Gerais') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Nome') }}</label>
                            <div class="input-group">
                                <span class="input-group-textТекст

System: <i class=" fas fa-university"></i></span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="{{ __('Digite o nome da categoria') }}" required>
                            </div>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="mb-3">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i> {{ __('Salvar') }}</button>
                <a href="{{ route('admin.revenue-categories.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> {{ __('Voltar') }}</a>
            </div>
        </x-adminlte-card>
    </form>
</div>
@stop

@section('css')
<style>
    .card {
        transition: all 0.3s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .row div {
        margin-bottom: 10px;
    }

    .btn-sm {
        margin-right: 5px;
    }

</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('form').on('submit', function(e) {
            let name = $('#name').val().trim();

            if (name.length < 3) {
                e.preventDefault();
                $('#name').addClass('is-invalid');
                $('#name').siblings('.invalid-feedback').text('{{ __("O nome da entidade deve ter pelo menos 3 caracteres.") }}');
            }
        });
    });

</script>
@stop
