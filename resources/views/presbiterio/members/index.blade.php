@extends('adminlte::page')

@section('title', 'Membros')

@section('content')
<div class="container-fluid">
    <x-adminlte-card title="Lista de Membros" theme="secondary">
        @if (session('success'))
        <x-adminlte-alert theme="success" title="Sucesso">
            {{ session('success') }}
        </x-adminlte-alert>
        @elseif (session('error'))
        <x-adminlte-alert theme="danger" title="Erro">
            {{ session('error') }}
        </x-adminlte-alert>
        @endif
        <div class="mb-0">
            <a href="{{ route('admin.members.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Membro</a>
        </div>
        <table id="members" class="table table-striped w-100">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Comunidade</th>
                    <th>Telefone</th>
                    <th>Celular</th>
                    <th>E-mail</th>
                    <th>Ações</th>
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
                    <td>
                        <a href="{{ route('presbiterio.members.edit', ['member' => $member ]) }}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Nenhum membro encontrado</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-adminlte-card>
</div>
@stop

@section('css')
<style>
    .content-wrapper .content {
        width: 100% !important;
        max-width: none !important;
    }

    #members {
        width: 100% !important;
    }

</style>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#members').DataTable({
            language: {
                emptyTable: "Nenhum registro encontrado"
                , info: "Mostrando de _START_ até _END_ de _TOTAL_ registros"
                , infoEmpty: "Mostrando 0 até 0 de 0 registros"
                , infoFiltered: "(Filtrados de _MAX_ registros)"
                , lengthMenu: "Mostrar _MENU_ registros"
                , loadingRecords: "Carregando..."
                , processing: "Processando..."
                , search: "Pesquisar"
                , zeroRecords: "Nenhum registro encontrado"
                , paginate: {
                    next: "Próximo"
                    , previous: "Anterior"
                }
            }
            , responsive: true
            , autoWidth: false
            , columnDefs: [{width: '5%', targets: 0}, // ID
                {width: '20%', targets: 1}, // Nome
                {width: '20%', targets: 2}, // Comunidade
                {width: '15%', targets: 3}, // Telefone
                {width: '15%', targets: 4}, // Celular
                {width: '15%', targets: 5}, // E-mail
                {width: '10%', targets: 6} // Ações
            ]
        });
    });

</script>
@stop
