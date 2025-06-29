@extends('adminlte::page')

@section('title', __('Paróquias'))

@section('content_header')
    <h1 class="m-0 text-dark">{{ __('Paróquias') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Paróquias') }}</li>
        </ol>
    </nav>

    <!-- Card Principal -->
    <x-adminlte-card title="{{ __('Lista de Paróquias') }}" theme="secondary" collapsible>
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
            <a href="{{ route('admin.parishes.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> {{ __('Adicionar Paróquia') }}
            </a>
        </div>

        <!-- Tabela -->
        <table id="parishes" class="table table-bordered table-hover table-striped w-100">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Nome') }}</th>
                    <th>{{ __('Cidade') }}</th>
                    <th>{{ __('Telefone') }}</th>
                    <th>{{ __('E-mail') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($parishes as $parish)
                    <tr>
                        <td>{{ $parish->id }}</td>
                        <td>{{ $parish->name }}</td>
                        <td>{{ $parish->city ?? '-' }}</td>
                        <td>{{ formatPhone($parish->phone) }}</td>
                        <td>{{ $parish->email ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.parishes.edit', ['parish' => $parish]) }}" class="btn btn-warning btn-sm" title="{{ __('Editar') }}">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ __('Nenhuma paróquia encontrada') }}</td>
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
            $('#parishes').DataTable({
                language: {
                    url: '{{ asset('vendor/datatables/lang/pt-BR.json') }}'
                },
                responsive: true,
                autoWidth: false,
                columnDefs: [
                    { width: '5%', targets: 0 },  // ID
                    { width: '25%', targets: 1 }, // Nome
                    { width: '20%', targets: 2 }, // Cidade
                    { width: '20%', targets: 3 }, // Telefone
                    { width: '20%', targets: 4 }, // E-mail
                    { width: '10%', targets: 5 }  // Ações
                ]
            });
        });
    </script>
@stop