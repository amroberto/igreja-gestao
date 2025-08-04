@extends('adminlte::page')

@section('title', __('Categoria - Outras Receitas Ordinárias'))

@section('content_header')
    <h1 class="m-0 text-dark">{{ __('Categorias - Outras Receitas Ordinárias') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Categoria - Outras Receitas Ordinárias') }}</li>
        </ol>
    </nav>

    <!-- Card Principal -->
    <x-adminlte-card title="{{ __('Lista de Categoria de Outras Receitas Ordinárias') }}" theme="secondary" collapsible>
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
            <a href="{{ route('admin.revenue-ordinary-categories.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> {{ __('Adicionar Categoria') }}
            </a>
        </div>

        <!-- Tabela -->
        <table id="revenue-ordinary-other" class="table table-bordered table-hover table-striped w-100">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Nome') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->name }}</td>
                        <td>
                            <a href="{{ route('admin.revenue-ordinary-categories.edit', ['revenue_ordinary_category' => $category]) }}" class="btn btn-warning btn-sm" title="{{ __('Editar') }}">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.revenue-ordinary-categories.destroy', ['revenue_ordinary_category' => $category]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Tem certeza que deseja excluir esta categoria?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="{{ __('Excluir') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ __('Nenhuma categoria encontrada') }}</td>
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
            $('#revenue-ordinary-other').DataTable({
                language: {
                    url: '{{ asset('vendor/datatables/lang/pt-BR.json') }}'
                },
                responsive: true,
                autoWidth: false,
                columnDefs: [
                    { width: '5%', targets: 0 },  // ID
                    { width: '85%', targets: 1 }, // Nome
                    { width: '10%', targets: 5 }  // Ações
                ]
            });
        });
    </script>
@stop
