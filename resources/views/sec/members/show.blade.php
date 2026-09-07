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
            <h3 class="card-title"><i class="mr-2 fas fa-user"></i>{{ __('Informações Pessoais') }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-id-badge"></i> {{ __('ID do Membro') }}:</strong>
                    <p class="text-muted">{{ $member->id }}</p>
                </div>
            </div>
            <div class="row">
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-user"></i> {{ __('Nome Completo') }}:</strong>
                    <p class="text-muted">{{ $member->name ?: 'N/A' }}</p>
                </div>
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-calendar-alt"></i> {{ __('Data de Nascimento') }}:</strong>
                    <p class="text-muted">{{ $member->birth_date ? $member->birth_date->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-ring"></i> {{ __('Estado Civil') }}:</strong>
                    <p class="text-muted">{{ $member->marital_status ? ucfirst($member->marital_status) : 'N/A' }}</p>
                </div>
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-venus-mars"></i> {{ __('Gênero') }}:</strong>
                    <p class="text-muted">{{ $member->gender?->label() ?? __('Não informado') }}</p>
                </div>
            </div>
            <div class="row">
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-id-card"></i> {{ __('CPF') }}:</strong>
                    <p class="text-muted">{{ formatCpf($member->cpf) }}</p>
                </div>
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-id-badge"></i> {{ __('RG') }}:</strong>
                    <p class="text-muted">{{ formatRg($member->rg) }}</p>
                </div>
                {{-- is_active --}}
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-toggle-on"></i> {{ __('Status') }}:</strong>
                    <p class="text-muted">{{ $member->is_active ? __('Ativo') : __('Inativo') }}</p>
                </div>
                {{-- is_contributor --}}
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-hand-holding-usd"></i> {{ __('Contribuinte') }}:</strong>
                    <p class="text-muted">{{ $member->is_contributor ? __('Sim') : __('Não') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações de Contato -->
    <div class="card card-info card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="mr-2 fas fa-address-book"></i>{{ __('Informações de Contato') }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-envelope"></i> {{ __('E-mail') }}:</strong>
                    <p class="text-muted">{{ $member->email ?: 'N/A' }}</p>
                </div>
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-phone"></i> {{ __('Telefone') }}:</strong>
                    <p class="text-muted">{{ formatPhone($member->phone) }}</p>
                </div>
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-mobile-alt"></i> {{ __('Celular') }}:</strong>
                    <p class="text-muted">{{ formatPhone($member->cellphone) }}</p>
                </div>
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-users"></i> {{ __('Comunidade') }}:</strong>
                    <p class="text-muted">{{ $member->community ? $member->community->name : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações de Endereço -->
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="mr-2 fas fa-map-marker-alt"></i>{{ __('Informações de Endereço') }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-map-pin"></i> {{ __('CEP') }}:</strong>
                    <p class="text-muted">{{ formatCep($member->cep) }}</p>
                </div>
                <div class="mb-3 col-md-6 col-sm-12">
                    <strong><i class="mr-1 fas fa-road"></i> {{ __('Endereço') }}:</strong>
                    <p class="text-muted">{{ $member->address ?: 'N/A' }}</p>
                </div>
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-home"></i> {{ __('Número') }}:</strong>
                    <p class="text-muted">{{ $member->number ?: 'N/A' }}</p>
                </div>
            </div>
            <div class="row">
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-building"></i> {{ __('Complemento') }}:</strong>
                    <p class="text-muted">{{ $member->complement ?: 'N/A' }}</p>
                </div>
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-city"></i> {{ __('Bairro') }}:</strong>
                    <p class="text-muted">{{ $member->district ?: 'N/A' }}</p>
                </div>
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-map"></i> {{ __('Cidade') }}:</strong>
                    <p class="text-muted">{{ $member->city ?: 'N/A' }}</p>
                </div>
                <div class="mb-3 col-md-3 col-sm-6">
                    <strong><i class="mr-1 fas fa-globe"></i> {{ __('Estado') }}:</strong>
                    <p class="text-muted">{{ $member->state ?: 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações de Filiação -->
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="mr-2 fas fa-users"></i>{{ __('Informações de Filiação') }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="mb-3 col-md-6 col-sm-12">
                    <strong><i class="mr-1 fas fa-male"></i> {{ __('Nome do Pai') }}:</strong>
                    <p class="text-muted">{{ $member->father_name ?: 'N/A' }}</p>
                </div>
                <div class="mb-3 col-md-6 col-sm-12">
                    <strong><i class="mr-1 fas fa-female"></i> {{ __('Nome da Mãe') }}:</strong>
                    <p class="text-muted">{{ $member->mother_name ?: 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Contribuições Quitadas / Lançadas -->
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="mr-2 fas fa-check-circle text-success"></i>{{ __('Últimas Contribuições Quitadas / Lançadas') }}
            </h3>
        </div>
        <div class="card-body">
            @if ($member->confirmedContributions->isEmpty())
                <p class="text-muted mb-0"><i class="mr-1 fas fa-info-circle"></i> {{ __('Nenhuma contribuição quitada registrada.') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="bg-success">
                            <tr>
                                <th style="width: 140px;">{{ __('Nº Documento') }}</th>
                                <th>{{ __('Data Pagto') }}</th>
                                <th>{{ __('Referência / Período') }}</th>
                                <th>{{ __('Tipo') }}</th>
                                <th>{{ __('Comunidade') }}</th>
                                <th>{{ __('Forma') }}</th>
                                <th>{{ __('Valor') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($member->confirmedContributions as $contribution)
                            <tr>
                                <td>
                                    <span class="badge badge-light border">
                                        #{{ str_pad($contribution->id, 6, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td>{{ $contribution->contribution_date ? $contribution->contribution_date->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ $contribution->period ?: ($contribution->description ?: 'N/A') }}</td>
                                <td>{{ ucfirst($contribution->type ?? 'Mensal') }}</td>
                                <td>{{ $contribution->community ? $contribution->community->name : 'N/A' }}</td>
                                <td>
                                    @if ($contribution->payment_method)
                                        @php
                                            $paymentMethod = $contribution->payment_method;
                                            if (is_string($paymentMethod)) {
                                                try {
                                                    $paymentMethod = \App\Enums\PaymentMethodEnum::from($paymentMethod);
                                                } catch (\ValueError $e) {
                                                    $paymentMethod = null;
                                                }
                                            }
                                            echo $paymentMethod?->label() ?? 'N/A';
                                        @endphp
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>R$ {{ number_format($contribution->amount, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- 2. Contribuições em Aberto (Consulta de Nº Doc) -->
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title text-dark">
                <i class="mr-2 fas fa-exclamation-circle text-warning"></i>{{ __('Contribuições em Aberto (Últimas 10)') }}
            </h3>
        </div>
        <div class="card-body">
            @if ($member->pendingContributions->isEmpty())
                <p class="text-muted mb-0"><i class="mr-1 fas fa-check-circle text-success"></i> {{ __('Nenhuma contribuição em aberto para este membro.') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="bg-warning text-dark">
                            <tr>
                                <th style="width: 140px;">{{ __('Nº Documento') }}</th>
                                <th>{{ __('Vencimento') }}</th>
                                <th>{{ __('Referência / Período') }}</th>
                                <th>{{ __('Tipo') }}</th>
                                <th>{{ __('Forma') }}</th>
                                <th>{{ __('Valor') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($member->pendingContributions as $contribution)
                            <tr>
                                <td>
                                    <span class="badge badge-dark p-2" style="font-size: 0.95rem;">
                                        #{{ str_pad($contribution->id, 6, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $dueDate = $contribution->due_date ? \Carbon\Carbon::parse($contribution->due_date) : $contribution->contribution_date;
                                    @endphp
                                    {{ $dueDate ? $dueDate->format('d/m/Y') : 'N/A' }}
                                </td>
                                <td>{{ $contribution->period ?: ($contribution->description ?: 'N/A') }}</td>
                                <td>{{ ucfirst($contribution->type ?? 'Mensal') }}</td>
                                <td>
                                    @php
                                        $methodVal = is_object($contribution->payment_method)
                                            ? strtolower($contribution->payment_method->value ?? '')
                                            : strtolower($contribution->payment_method ?? '');
                                        $isPix = str_contains($methodVal, 'pix');
                                    @endphp
                                    <span class="badge {{ $isPix ? 'badge-success' : 'badge-danger' }}">
                                        {{ $isPix ? 'PIX' : 'Boleto' }}
                                    </span>
                                </td>
                                <td><strong>R$ {{ number_format($contribution->amount, 2, ',', '.') }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div class="card-footer">
            <a href="{{ route('sec.members.index') }}" class="btn btn-secondary"><i class="mr-1 fas fa-arrow-left"></i> {{ __('Voltar') }}</a>
            <a href="{{ route('sec.members.edit', ['member' => $member]) }}" class="float-right btn btn-primary"><i class="mr-1 fas fa-edit"></i> {{ __('Editar') }}</a>
            <a href="{{ route('sec.members.pdf', ['member' => $member]) }}" class="float-right mr-2 btn btn-warning"><i class="mr-1 fas fa-file-pdf"></i> {{ __('Gerar PDF') }}</a>
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
