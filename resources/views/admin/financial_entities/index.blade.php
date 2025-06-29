@extends('adminlte::page')

@section('title', __('Entidades Financeiras'))

@section('content_header')
    <h1 class="m-0 text-dark">{{ __('Entidades Financeiras') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Entidades Financeiras') }}</li>
        </ol>
    </nav>

    <!-- Card Principal -->
    <x-adminlte-card title="{{ __('Lista de Entidades Financeiras') }}" theme="secondary" collapsible>
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
            <a href="{{ route('admin.financial_entities.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> {{ __('Adicionar Entidade Financeira') }}
            </a>
        </div>

        <!-- Tabela -->
        <table id="financial_entities" class="table table-bordered table-hover table-striped w-100">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Nome') }}</th>
                    <th>{{ __('Comunidade') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($financial_entities as $entity)
                    <tr>
                        <td>{{ $entity->id }}</td>
                        <td>{{ $entity->name }}</td>
                        <td>{{ $entity->community->name }}</td>
                        <td>
                            <a href="{{ route('admin.financial_entities.edit', ['financial_entity' => $entity]) }}" class="btn btn-warning btn-sm" title="{{ __('Editar') }}">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.financial_entities.destroy', ['financial_entity' => $entity]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Você tem certeza que deseja excluir esta entidade?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="{{ __('Excluir') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">{{ __('Nenhuma entidade Financeira encontrada') }}</td>
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
        .table-responsive {
            overflow-x: auto;
        }
    </style>
@stop

@section('plugins.Datatables', true)

@section('js')
    <script>
        $(document).ready(function() {
            $('#financial_entities').DataTable({
                language: {
                    url: '{{ asset('vendor/datatables/lang/pt-BR.json') }}'
                },
                responsive: true,
                autoWidth: false,
                columnDefs: [
                    { width: '5%', targets: 0 },  // ID
                    { width: '40%', targets: 1 }, // Nome
                    { width: '40%', targets: 2 }, // Comunidade
                    { width: '15%', targets: 7 }  // Ações
                ]
            });
        });
    </script>
@stop