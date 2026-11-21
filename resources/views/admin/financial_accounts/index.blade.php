@extends('adminlte::page')

@section('title', __('Contas Financeiras'))

@section('content_header')
<h1 class="m-0 text-dark">{{ __('Contas Financeiras') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Contas Financeiras') }}</li>
        </ol>
    </nav>

    <!-- Card Principal -->
    <x-adminlte-card title="{{ __('Lista de Contas Financeiras') }}" theme="secondary" collapsible>
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

        <!-- Botão Adicionar -->
        <div class="mb-3">
            <a href="{{ route('admin.financial_accounts.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> {{ __('Adicionar Conta Financeira') }}
            </a>
        </div>

        <!-- Tabela -->
        <table id="financialAccounts" class="table table-bordered table-hover table-striped w-100">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Comunidade') }}</th>
                    <th>{{ __('Nome') }}</th>
                    <th>{{ __('Tipo') }}</th>
                    <th>{{ __('Banco') }}</th>
                    <th>{{ __('Número da Conta') }}</th>
                    <th>{{ __('Agência') }}</th>
                    <th>{{ __('Ativa') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($financialAccounts as $financialAccount)
                <tr>
                    <td>{{ $financialAccount->id }}</td>
                    <td>{{ $financialAccount->community->name }}</td>
                    <td>{{ $financialAccount->name }}</td>
                    <td>{{ $financialAccount->type->description() }}</td>
                    <td>{{ $financialAccount->bank_name }}</td>
                    <td>{{ $financialAccount->account_number }}</td>
                    <td>{{ $financialAccount->agency_number }}</td>
                    <td>
                        @switch($financialAccount->is_active)
                        @case(true)
                        <span class="badge bg-success">{{ __('SIM') }}</span>
                        @break
                        @case(false)
                        <span class="badge bg-warning text-dark">{{ __('NÃO') }}</span>
                        @break
                        @endswitch
                    </td>
                    <td>
                            <a href="{{ route('admin.financial_account.edit', ['financial_account' => $financialAccount]) }}" class="btn btn-warning btn-sm" title="{{ __('Editar') }}">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">{{ __('Nenhuma conta financeira encontrada') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-adminlte-card>
</div>
@stop

@section('css')
<style>
    .table th,
    .table td {
        vertical-align: middle;
    }

    .btn-sm {
        margin-right: 5px;
    }

    .table-responsive {
        overflow-x: auto;
    }
</style>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
            $('#financialAccounts').DataTable({
                language: {
                    emptyTable: "{{ __('Nenhum registro encontrado') }}",
                    info: "{{ __('Mostrando de _START_ até _END_ de _TOTAL_ registros') }}",
                    infoEmpty: "{{ __('Mostrando 0 até 0 de 0 registros') }}",
                    infoFiltered: "{{ __('(Filtrados de _MAX_ registros)') }}",
                    lengthMenu: "{{ __('Mostrar _MENU_ registros') }}",
                    loadingRecords: "{{ __('Carregando...') }}",
                    processing: "{{ __('Processando...') }}",
                    search: "{{ __('Pesquisar') }}",
                    zeroRecords: "{{ __('Nenhum registro encontrado') }}",
                    paginate: {
                        next: "{{ __('Próximo') }}",
                        previous: "{{ __('Anterior') }}"
                    }
                },
                responsive: true,
                autoWidth: false,
                columnDefs: [
                    { width: '5%', targets: 0 },  // ID
                    { width: '15%', targets: 1 }, // community
                    { width: '15%', targets: 2 }, // name
                    { width: '10%', targets: 3 }, // type
                    { width: '15%', targets: 4 }, // bank_name
                    { width: '15%', targets: 5 }, // account_number
                    { width: '10%', targets: 6 }, // agency_number
                    { width: '5%', targets: 7 },  // is_active
                    {width: '10%', targets: 8 }
                ]
            });
        });
</script>
@stop