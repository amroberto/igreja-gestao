@extends('adminlte::page')

@section('title', __('Detalhes do Plano de Contas'))

@section('content_header')

<div class="row mb-2">

    <div class="col-sm-6">

        <h1 class="m-0 text-dark">{{ __('Detalhes do Plano de Contas') }}</h1>

    </div>

    <div class="col-sm-6">

        <ol class="breadcrumb float-sm-right">

            <li class="breadcrumb-item"><a href="{{ route('sec.account_plans.index') }}">{{ __('Planos de Conta') }}</a>
            </li>

            <li class="breadcrumb-item active">{{ __('Detalhes') }}</li>

        </ol>

    </div>

</div>

@stop

@section('content')

<div class="container-fluid">

    <div class="row">

        <div class="col-12">

            @if (session('success'))

            <x-adminlte-alert theme="success" title="{{ __('Sucesso') }}" dismissable>

                {{ session('success') }}

            </x-adminlte-alert>

            @endif

            @if (session('error'))

            <x-adminlte-alert theme="danger" title="{{ __('Erro') }}" dismissable>

                {{ session('error') }}

            </x-adminlte-alert>

            @endif

        </div>

    </div>

    <div class="card card-primary card-outline shadow-sm">

        <div class="card-header">

            <h3 class="card-title">

                <i class="fas fa-info-circle mr-2"></i>{{ __('Informações Gerais') }}

            </h3>

        </div>



        <div class="card-body">

            <div class="row mb-3">

                <div class="col-md-2 col-sm-4">

                    <strong><i class="fas fa-hashtag mr-1"></i> {{ __('ID') }}:</strong>

                    <p class="text-muted mb-0">{{ $accountPlan->id }}</p>

                </div>



                <div class="col-md-4 col-sm-4">

                    <strong><i class="fas fa-barcode mr-1"></i> {{ __('Código') }}:</strong>

                    <p class="text-muted mb-0"><code>{{ $accountPlan->code }}</code></p>

                </div>

                <div class="col-md-6 col-sm-4">

                    <strong><i class="fas fa-toggle-on mr-1"></i> {{ __('Status') }}:</strong>

                    <p class="mb-0">

                        @if($accountPlan->active)

                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i> {{ __('Ativo')
                            }}</span>

                        @else

                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle"></i> {{ __('Inativo')
                            }}</span>

                        @endif

                    </p>

                </div>

            </div>

            <hr>

            <div class="row">

                <div class="col-12">

                    <strong><i class="fas fa-align-left mr-1"></i> {{ __('Descrição') }}:</strong>

                    <p class="text-muted mb-0">{{ $accountPlan->description }}</p>

                </div>

            </div>

        </div>

    </div>

    <div class="mt-3 mb-4">

        <a href="{{ route('sec.account_plans.index') }}" class="btn btn-secondary">

            <i class="fas fa-arrow-left mr-1"></i> {{ __('Voltar') }}

        </a>

    </div>

</div>

@stop

@section('css')

<style>
    .card-body p {

        font-size: 1.05rem;

    }

    .card {

        transition: all 0.2s ease-in-out;

    }

    .card:hover {

        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;

    }
</style>

@stop

@section('js')

@parent

@stop