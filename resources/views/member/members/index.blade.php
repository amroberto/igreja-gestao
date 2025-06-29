@extends('adminlte::page')

@section('title', 'Membros')

@section('content')
<div class="container">
    <div class="card w-100 p-3">
        <div class="card-header">
            <h3>Membros</h3>
            <div class="mb-0">
                <a href="{{ route('admin.members.create') }}" class="btn btn-primary float-right"><i class="fas fa-plus"></i> Adicionar Membro</a>
            </div>
        </div>

        <div class="card-body">
            @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @elseif (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif
            <table id="members" class="table table-striped w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
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
                        <td>{{ formatPhone($member->phone) }}</td>
                        <td>{{ formatPhone($member->cellphone) }}</td>
                        <td>{{ formatPhone($member->email) }}</td>
                        <td>
                            <a href="{{ route('admin.members.edit', ['member' => $member ]) }}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        var table = $('#members').DataTable({
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
            , autoWidth: false,
            responsive: true,
        });

        // Ajusta as colunas após a inicialização
        table.columns.adjust().draw();
    });

</script>
@stop
