@extends('adminlte::page')

@section('title', __('Planos de Contas'))

@section('content_header')
    <h1 class="m-0 text-dark">{{ __('Planos de Contas') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Planos de Contas') }}</li>
        </ol>
    </nav>

    <!-- Alertas -->
    <x-adminlte-card title="Lista de Planos de Contas" theme="secondary" collapsible>
        @if (session('success'))
            <x-adminlte-alert theme="success" title="Sucesso" dismissable>
                {{ session('success') }}
            </x-adminlte-alert>
        @elseif (session('error'))
            <x-adminlte-alert theme="danger" title="Erro" dismissable>
                {{ session('error') }}
            </x-adminlte-alert>
        @endif

        <!-- Tabela -->
        <table id="accountPlans" class="table table-bordered table-hover w-100">
            <thead>
                <>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Código') }}</th>
                    <th>{{ __('Descrição') }}</th>
                    <th>{{ __('Ativo') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accountPlans as $accountPlan)
                    <tr>
                        <td>{{ $accountPlan->id }}</td>
                        <td>{{ $accountPlan->code }}</td>
                        <td>{{ $accountPlan->description }}</td>
                        <td>{{ $accountPlan->active ? __('Sim') : __('Não') }}</td>
                        <td class="text-center">
                            <a href="{{ route('sec.account_plans.show', ['accountPlan' => $accountPlan]) }}" class="btn btn-info btn-sm" title="{{ __('Visualizar') }}">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">{{ __('Nenhum plano de conta encontrado') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-adminlte-card>
</div>
@stop

@section('css')
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-sm {
            margin-right: 5px;
        }
        .card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
@stop

@section('plugins.Datatables', true)

@section('js')
    <script>
        $(document).ready(function() {
            $('#accountPlans').DataTable({
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
                    { width: '10%', targets: 1 }, // Código
                    { width: '50%', targets: 2 }, // Descrição
                    { width: '10%', targets: 3 }, // Ativo
                    { width: '15%', targets: 4 }  // Ações
                ]
            });
        });
    </script>
@stop