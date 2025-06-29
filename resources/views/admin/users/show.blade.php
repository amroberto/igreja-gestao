@extends('adminlte::page')

@section('title', 'Consignatarias')

@section('content_header')
    <h1>{{$user->name}}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h3>Dados do Usuário</h3>
                        <p><strong>Nome:</strong> {{$user->name}}</p>
                        <p><strong>Email:</strong> {{$user->email}}</p>
                        <p><strong>CPF:</strong> {{$user->cpf}}</p>
                        <p><strong>RG:</strong> {{$user->rg}}</p>
                        <p><strong>Telefone:</strong> {{$user->phone}}</p>
                        <p><strong>Data de Nascimento:</strong> {{ $user->birth_date ? $user->birth_date->format('d/m/Y') : 'Não informado' }}</p>
                        <p><strong>Celular:</strong> {{$user->cell}}</p>
                        <p><strong>CEP:</strong> {{$user->zip_code}}</p>
                        <p><strong>Endereço:</strong> {{$user->address}}</p>
                        <p><strong>Cidade:</strong> {{$user->city}}</p>
                        <p><strong>Estado:</strong> {{$user->state}}</p>
                        <p><strong>País</strong>: {{$user->country}}</p>
                        <p><strong>Tipo de Usuário:</strong> 
                            @if($user->isAdmin())
                                <span class="badge badge-success">Administrador</span>
                            @else
                                <span class="badge badge-primary">Usuário</span>
                            @endif
                        </p>
                        <p><strong>Data de Criação:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                        <p><strong>Última Atualização:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <card-footer>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">Voltar</a>
                    <a href="{{ route('admin.users.edit', ['user' => $user->id]) }}" class="btn btn-warning">Editar</a>
                    <form action="{{ route('admin.users.destroy', ['user' => $user->id]) }}" method="post" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form> 
                </card-footer>
            </div>
        </div>
    </div>
@stop


