@extends('adminlte::page')

@section('title', 'Configurações Mensais')

@section('content_header')
    <h1>Cartas de Contribuição Mensal</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listagem de Cartas de Contribuição Mensal</h3>
            <div class="card-tools">
                <a href="{{ route('sec.monthly-configs.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Criar Nova Carta
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if ($configs->isEmpty())
                <p>Nenhuma configuração mensal encontrada. <a href="{{ route('sec.monthly-configs.create') }}">Crie uma nova configuração</a>.</p>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Comunidade</th>
                            <th>Mês de Referência</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($configs as $config)
                            <tr>
                                <td>{{ $config->community->name ?? 'Comunidade não encontrada' }}</td>
                                <td>{{ \Carbon\Carbon::parse($config->reference)->format('m/Y') }}</td>
                                <td>
                                    <a href="{{ route('sec.monthly-configs.show', $config) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Visualizar
                                    </a>
                                    <a href="{{ route('sec.monthly-configs.edit', $config) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <form action="{{ route('sec.monthly-configs.destroy', $config) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta configuração?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Excluir
                                        </button>
                                    </form>
                                    <a href="{{ route('sec.monthly-configs.preview', $config) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-file-pdf"></i> Visualizar PDF
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
