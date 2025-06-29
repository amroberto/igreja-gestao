@extends('adminlte::page')

@section('title', __('Cadastro de Membro'))

@section('content_header')
<h1 class="m-0 text-dark">{{ __('Dados Cadastrais do Membro') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Alertas -->
    <div class="row">
        <div class="col-12">
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Informações Pessoais -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user mr-2"></i>{{ __('Informações Pessoais') }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-user mr-1"></i> {{ __('Nome Completo') }}:</strong>
                    <p class="text-muted">{{ $member->name ?: 'N/A' }}</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-calendar-alt mr-1"></i> {{ __('Data de Nascimento') }}:</strong>
                    <p class="text-muted">{{ $member->birth_date ? $member->birth_date->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-ring mr-1"></i> {{ __('Estado Civil') }}:</strong>
                    <p class="text-muted">{{ $member->marital_status ? ucfirst($member->marital_status) : 'N/A' }}</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-venus-mars mr-1"></i> {{ __('Gênero') }}:</strong>
                    <p class="text-muted">{{ $member->gender?->label() ?? __('Não informado') }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-id-card mr-1"></i> {{ __('CPF') }}:</strong>
                    <p class="text-muted">{{ formatCpf($member->cpf) }}</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-id-badge mr-1"></i> {{ __('RG') }}:</strong>
                    <p class="text-muted">{{ formatRg($member->rg) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações de Contato -->
    <div class="card card-info card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-address-book mr-2"></i>{{ __('Informações de Contato') }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-envelope mr-1"></i> {{ __('E-mail') }}:</strong>
                    <p class="text-muted">{{ $member->email ?: 'N/A' }}</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-phone mr-1"></i> {{ __('Telefone') }}:</strong>
                    <p class="text-muted">{{ formatPhone($member->phone) }}</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-mobile-alt mr-1"></i> {{ __('Celular') }}:</strong>
                    <p class="text-muted">{{ formatPhone($member->cellphone) }}</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-users mr-1"></i> {{ __('Comunidade') }}:</strong>
                    <p class="text-muted">{{ $member->community ? $member->community->name : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações de Endereço -->
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-map-marker-alt mr-2"></i>{{ __('Informações de Endereço') }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-map-pin mr-1"></i> {{ __('CEP') }}:</strong>
                    <p class="text-muted">{{ formatCep($member->cep) }}</p>
                </div>
                <div class="col-md-6 col-sm-12 mb-3">
                    <strong><i class="fas fa-road mr-1"></i> {{ __('Endereço') }}:</strong>
                    <p class="text-muted">{{ $member->address ?: 'N/A' }}</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-home mr-1"></i> {{ __('Número') }}:</strong>
                    <p class="text-muted">{{ $member->number ?: 'N/A' }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-building mr-1"></i> {{ __('Complemento') }}:</strong>
                    <p class="text-muted">{{ $member->complement ?: 'N/A' }}</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-city mr-1"></i> {{ __('Bairro') }}:</strong>
                    <p class="text-muted">{{ $member->district ?: 'N/A' }}</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-map mr-1"></i> {{ __('Cidade') }}:</strong>
                    <p class="text-muted">{{ $member->city ?: 'N/A' }}</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <strong><i class="fas fa-globe mr-1"></i> {{ __('Estado') }}:</strong>
                    <p class="text-muted">{{ $member->state ?: 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações de Filiação -->
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users mr-2"></i>{{ __('Informações de Filiação') }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 col-sm-12 mb-3">
                    <strong><i class="fas fa-male mr-1"></i> {{ __('Nome do Pai') }}:</strong>
                    <p class="text-muted">{{ $member->father_name ?: 'N/A' }}</p>
                </div>
                <div class="col-md-6 col-sm-12 mb-3">
                    <strong><i class="fas fa-female mr-1"></i> {{ __('Nome da Mãe') }}:</strong>
                    <p class="text-muted">{{ $member->mother_name ?: 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contribuições -->
    <div class="card card-danger card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-donate mr-2"></i>{{ __('Contribuições') }}</h3>
        </div>
        <div class="card-body">
            @if ($member->contributions->isEmpty())
            <p class="text-muted"><i class="fas fa-info-circle mr-1"></i> {{ __('Nenhuma contribuição registrada.') }}</p>
            @else
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="bg-primary">
                        <tr>
                            <th>{{ __('Data') }}</th>
                            <th>{{ __('Comunidade') }}</th>
                            <th>{{ __('Forma de Pagamento') }}</th>
                            <th>{{ __('Período') }}</th>
                            <th>{{ __('Valor') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($member->contributions as $contribution)
                        <tr>
                            <td>{{ $contribution->contribution_date ? $contribution->contribution_date->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ $contribution->community ? $contribution->community->name : 'N/A' }}</td>
                            <td>{{ $contribution->payment_method ?: 'N/A' }}</td>
                            <td>{{ $contribution->period ?: 'N/A' }}</td>
                            <td>R$ {{ number_format($contribution->amount, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        <div class="card-footer">
            <a href="{{ route('sec.members.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> {{ __('Voltar') }}</a>
            <a href="{{ route('sec.members.edit', ['member' => $member]) }}" class="btn btn-primary float-right"><i class="fas fa-edit mr-1"></i> {{ __('Editar') }}</a>
            <a href="{{ route('sec.members.pdf', ['member' => $member]) }}" class="btn btn-warning float-right mr-2"><i class="fas fa-file-pdf mr-1"></i> {{ __('Gerar PDF') }}</a>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card-body p {
        margin-bottom: 0;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .card {
        transition: all 0.3s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

</style>
@stop

@section('js')
@parent
<!-- Nenhum script adicional necessário -->
@stop
