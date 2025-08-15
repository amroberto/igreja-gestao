@extends('adminlte::page')

@section('title', __('Membros'))

@section('content_header')
    <h1 class="m-0 text-dark">{{ __('Membros') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('sec.dashboard') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Membros') }}</li>
        </ol>
    </nav>

    <!-- Alertas -->
    <x-adminlte-card title="Lista de Membros" theme="secondary" collapsible>
        @if (session('success'))
            <x-adminlte-alert theme="success" title="Sucesso" dismissable>
                {{ session('success') }}
            </x-adminlte-alert>
        @elseif (session('error'))
            <x-adminlte-alert theme="danger" title="Erro" dismissable>
                {{ session('error') }}
            </x-adminlte-alert>
        @endif

        <!-- Botão Adicionar -->
        <div class="mb-3">
            <a href="{{ route('sec.members.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle mr-1"></i> {{ __('Adicionar Membro') }}
            </a>
        </div>

        <!-- Tabela -->
        <table id="members" class="table table-bordered table-hover w-100">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Nome') }}</th>
                    <th>{{ __('Comunidade') }}</th>
                    <th>{{ __('Telefone') }}</th>
                    <th>{{ __('Celular') }}</th>
                    <th>{{ __('E-mail') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                    <tr>
                        <td>{{ $member->id }}</td>
                        <td>{{ $member->name }}</td>
                        <td>{{ $member->community ? $member->community->name : 'Nenhuma' }}</td>
                        <td>{{ formatPhone($member->phone) }}</td>
                        <td>{{ formatPhone($member->cellphone) }}</td>
                        <td>{{ $member->email ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('sec.members.show', ['member' => $member]) }}" class="btn btn-info btn-sm" title="{{ __('Visualizar') }}">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ __('Nenhum membro encontrado') }}</td>
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
            $('#members').DataTable({
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
                    { width: '20%', targets: 1 }, // Nome
                    { width: '20%', targets: 2 }, // Comunidade
                    { width: '15%', targets: 3 }, // Telefone
                    { width: '15%', targets: 4 }, // Celular
                    { width: '15%', targets: 5 }, // E-mail
                    { width: '10%', targets: 6 }  // Ações
                ]
            });
        });
    </script>
@stop
