@extends('adminlte::page')

@section('title', __('Comunidades'))

@section('content_header')
    <h1 class="m-0 text-dark">{{ __('Comunidades') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Comunidades') }}</li>
        </ol>
    </nav>

    <!-- Card Principal -->
    <x-adminlte-card title="{{ __('Lista de Comunidades') }}" theme="secondary" collapsible>
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
            <a href="{{ route('admin.communities.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> {{ __('Adicionar Comunidade') }}
            </a>
        </div>

        <!-- Tabela -->
        <table id="communities" class="table table-bordered table-hover table-striped w-100">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Nome') }}</th>
                    <th>{{ __('Paróquia') }}</th>
                    <th>{{ __('Cidade') }}</th>
                    <th>{{ __('Telefone') }}</th>
                    <th>{{ __('Celular') }}</th>
                    <th>{{ __('E-mail') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($communities as $community)
                    <tr>
                        <td>{{ $community->id }}</td>
                        <td>{{ $community->name }}</td>
                        <td>{{ $community->parish ? $community->parish->name : __('Nenhuma') }}</td>
                        <td>{{ $community->city ?? '-' }}</td>
                        <td>{{ formatPhone($community->phone) }}</td>
                        <td>{{ formatPhone($community->cellphone) }}</td>
                        <td>{{ $community->email ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.communities.edit', ['community' => $community]) }}" class="btn btn-warning btn-sm" title="{{ __('Editar') }}">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">{{ __('Nenhuma comunidade encontrada') }}</td>
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
            $('#communities').DataTable({
                language: {
                    url: '{{ asset('vendor/datatables/lang/pt-BR.json') }}'
                },
                responsive: true,
                autoWidth: false,
                columnDefs: [
                    { width: '5%', targets: 0 },  // ID
                    { width: '20%', targets: 1 }, // Nome
                    { width: '15%', targets: 2 }, // Paróquia
                    { width: '13%', targets: 3 }, // Cidade
                    { width: '12%', targets: 4 }, // Telefone
                    { width: '12%', targets: 5 }, // Celular
                    { width: '15%', targets: 6 }, // E-mail
                    { width: '10%', targets: 7 }  // Ações
                ]
            });
        });
    </script>
@stop