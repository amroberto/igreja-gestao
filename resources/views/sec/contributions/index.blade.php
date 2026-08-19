@extends('adminlte::page')

@section('title', __('Gerenciar Contribuições'))

@section('content_header')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('sec.dashboard') }}"><i class="fas fa-home"></i> {{ __('Home') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('Contribuições') }}</li>
    </ol>
</nav>
@stop

@section('content')
<div class="container-fluid">
    <x-adminlte-card title="{{ __('Lista de Contribuições') }}" theme="primary" theme-mode="outline" collapsible maximizable>
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

        <!-- Botão de Criar -->
        <div class="mb-3">
            <a href="{{ route('sec.contributions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('Nova Contribuição') }}
            </a>
        </div>

        <!-- Tabela -->
        <table id="contributions" class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">{{ __('Nº Doc.') }}</th>
                    <th scope="col">{{ __('Data') }}</th>
                    <th scope="col">{{ __('Cod. Membro') }}</th>
                    <th scope="col">{{ __('Nome Membro') }}</th>
                    <th scope="col">{{ __('Comunidade') }}</th>
                    <th scope="col">{{ __('Forma de Pagamento') }}</th>
                    <th scope="col">{{ __('Período') }}</th>
                    <th scope="col">{{ __('Valor') }}</th>
                    <th scope="col">{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contributions as $contribution)
                <tr>
                    <td>{{ $contribution->id }}</td>
                    <td>{{ $contribution->contribution_date->format('d/m/Y') }}</td>
                    <td>{{ $contribution->member->id }}</td>
                    <td>{{ $contribution->member->name }}</td>
                    <td>{{ $contribution->community->name ?? __('Nenhuma') }}</td>
                    <td>{{ $contribution->payment_method->label() }}</td>
                    <td>{{ $contribution->period }}</td>
                    <td>R$ {{ number_format($contribution->amount, 2, ',', '.') }}</td>
                    <td>
                        <!-- Botão que abre o modal de exclusão -->
                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal-{{ $contribution->id }}" title="{{ __('Excluir Contribuição') }}">
                            <i class="fas fa-trash"></i>
                        </button>

                        <!-- Modal de Confirmação de Exclusão -->
                        <x-adminlte-modal id="deleteModal-{{ $contribution->id }}" title="{{ __('Confirmar Exclusão') }}" theme="danger" icon="fas fa-trash" size="sm">
                            <p>{{ __('Tem certeza que deseja excluir a contribuição') }} <strong>{{ $contribution->document_number }}</strong>?</p>
                            <x-slot name="footerSlot">
                                <form action="{{ route('sec.contributions.destroy', $contribution) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-adminlte-button type="submit" theme="danger" label="{{ __('Excluir') }}" />
                                </form>
                                <x-adminlte-button theme="secondary" label="{{ __('Cancelar') }}" data-dismiss="modal" />
                            </x-slot>
                        </x-adminlte-modal>

                        <!-- Botão para Gerar Recibo -->
                        <a href="{{ route('sec.contributions.receipt', $contribution->id) }}" class="btn btn-success btn-sm ml-1" title="{{ __('Gerar Recibo') }}">
                            <i class="fas fa-file-pdf"></i>
                        </a>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">{{ __('Nenhuma contribuição encontrada') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-adminlte-card>
</div>
@stop

@section('css')
<style>
    .table {
        width: 100%;
    }

    .badge {
        font-size: 0.9rem;
        padding: 0.5em 0.75em;
    }

    .btn-sm i {
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }
    }

</style>
@stop

@section('plugins.Datatables', true)
@section('plugins.Bootstrap', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#contributions').DataTable({
                    language: {
                        emptyTable: "{{ __('Nenhum registro encontrado') }}"
                        , info: "{{ __('Mostrando de _START_ até _END_ de _TOTAL_ registros') }}"
                        , infoEmpty: "{{ __('Mostrando 0 até 0 de 0 registros') }}"
                        , infoFiltered: "{{ __('(Filtrados de _MAX_ registros)') }}"
                        , lengthMenu: "{{ __('Mostrar _MENU_ registros') }}"
                        , loadingRecords: "{{ __('Carregando...') }}"
                        , processing: "{{ __('Processando...') }}"
                        , search: "{{ __('Pesquisar') }}"
                        , zeroRecords: "{{ __('Nenhum registro encontrado') }}"
                        , paginate: {
                            next: "{{ __('Próximo') }}"
                            , previous: "{{ __('Anterior') }}"
                        }
                    }
                    , responsive: true
                    , autoWidth: false
                    , columnDefs: [{
                            width: '6%'
                            , targets: 0
                        }, // Nº Doc.
                        {
                            width: '9%'
                            , targets: 1
                        }, // Data
                        {
                            width: '8%'
                            , targets: 2
                        }, // Cod. Membro
                        {
                            width: '15%'
                            , targets: 3
                        }, // Nome Membro
                        {
                            width: '14%'
                            , targets: 4
                        }, // Comunidade
                        {
                            width: '13%'
                            , targets: 5
                        }, // Forma de Pagamento
                        {
                            width: '9%'
                            , targets: 6
                        }, // Período
                        {
                            width: '10%'
                            , targets: 7
                        }, // Valor
                        {
                            width: '15%'
                            , targets: 8
                        }, // Ações
                    ]
                , ]
            , ]
            , order: [
                [0, 'desc']
            ] // Ordenar por número da carta (mais recente primeiro)
    });

    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    });

</script>
@stop
