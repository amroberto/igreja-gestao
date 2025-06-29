@extends('adminlte::page')

@section('title', __('Gerenciar Contribuições'))

@section('content_header')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> {{ __('Home') }}</a></li>
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
                <a href="{{ route('admin.contributions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ __('Nova Contribuição') }}
                </a>
            </div>

            <!-- Tabela -->
            <table id="contributions" class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">{{ __('Nº Doc.') }}</th>
                        <th scope="col">{{ __('Membro') }}</th>
                        <th scope="col">{{ __('Comunidade') }}</th>
                        <th scope="col">{{ __('Período') }}</th>
                        <th scope="col">{{ __('Valor') }}</th>
                        <th scope="col">{{ __('Taxa Asaas') }}</th>
                        <th scope="col">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contributions as $contribution)
                        <tr>
                            <td>{{ $contribution->id }}</td>
                            <td>{{ $contribution->member->name }}</td>
                            <td>{{ $contribution->community->name ?? __('Nenhuma') }}</td>
                            <td>{{ $contribution->period }}</td>
                            <td>R$ {{ number_format($contribution->amount, 2, ',', '.') }}</td>
                            <td>
                                @if ($contribution->asaas_fee_generated || $contribution->asaas_fee_paid)
                                    R$ {{ number_format(($contribution->asaas_fee_generated ?? 0) + ($contribution->asaas_fee_paid ?? 0), 2, ',', '.') }}
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.contributions.edit', $contribution) }}"
                                   class="btn btn-info btn-sm" title="{{ __('Editar Contribuição') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" title="{{ __('Excluir Contribuição') }}"
                                        data-toggle="modal" data-target="#deleteModal-{{ $contribution->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <!-- Modal de Confirmação de Exclusão -->
                                <x-adminlte-modal id="deleteModal-{{ $contribution->id }}" title="{{ __('Confirmar Exclusão') }}"
                                                 theme="danger" icon="fas fa-trash" size="sm">
                                    <p>{{ __('Tem certeza que deseja excluir a contribuição') }} <strong>{{ $contribution->document_number }}</strong>?</p>
                                    <x-slot name="footerSlot">
                                        <form action="{{ route('admin.contributions.destroy', $contribution) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <x-adminlte-button type="submit" theme="danger" label="{{ __('Excluir') }}" />
                                        </form>
                                        <x-adminlte-button theme="secondary" label="{{ __('Cancelar') }}" data-dismiss="modal" />
                                    </x-slot>
                                </x-adminlte-modal>
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
                    { width: '10%', targets: 0 }, // Nº da Carta
                    { width: '20%', targets: 1 }, // Membro
                    { width: '15%', targets: 2 }, // Comunidade
                    { width: '10%', targets: 3 }, // Tipo
                    { width: '10%', targets: 4 }, // Período
                    { width: '10%', targets: 5 }, // Valor
                    { width: '10%', targets: 6 }, // Taxa Asaas
                    { width: '10%', targets: 7 }, // Status
                    { width: '15%', targets: 8 }, // Ações
                ],
                order: [[0, 'desc']] // Ordenar por número da carta (mais recente primeiro)
            });

            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop
