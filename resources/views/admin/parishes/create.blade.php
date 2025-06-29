@extends('adminlte::page')

@section('title', __('Cadastrar Paróquia'))

@section('content_header')
    <h1 class="m-0 text-dark">{{ __('Cadastrar Paróquia') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.parishes.index') }}">{{ __('Paróquias') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Cadastrar') }}</li>
        </ol>
    </nav>

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

    <!-- Formulário -->
    <form action="{{ route('admin.parishes.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <x-adminlte-card title="{{ __('Cadastrar Paróquia') }}" theme="primary" collapsible>
            <!-- Informações Gerais -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>{{ __('Informações Gerais') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('CNPJ') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input type="text" class="form-control @error('cnpj') is-invalid @enderror" id="cnpj" name="cnpj" value="{{ old('cnpj') }}" placeholder="{{ __('Digite o CNPJ') }}">
                            </div>
                            @error('cnpj')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Nome') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-church"></i></span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="{{ __('Digite o nome da paróquia') }}" required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações de Contato -->
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-address-book mr-2"></i>{{ __('Informações de Contato') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Telefone') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="{{ __('Digite o telefone') }}">
                            </div>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Celular') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                                <input type="text" class="form-control @error('cellphone') is-invalid @enderror" id="cellphone" name="cellphone" value="{{ old('cellphone') }}" placeholder="{{ __('Digite o celular') }}">
                            </div>
                            @error('cellphone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('E-mail') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Digite o e-mail') }}">
                            </div>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Website') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                <input type="text" class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website') }}" placeholder="{{ __('Digite o website') }}">
                            </div>
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações de Endereço -->
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-marker-alt mr-2"></i>{{ __('Informações de Endereço') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('CEP') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-pin"></i></span>
                                <input type="text" class="form-control @error('cep') is-invalid @enderror" id="cep" name="cep" value="{{ old('cep') }}" placeholder="{{ __('Digite o CEP') }}">
                            </div>
                            @error('cep')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Endereço') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-road"></i></span>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address') }}" placeholder="{{ __('Digite o endereço') }}">
                            </div>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Número') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-house"></i></span>
                                <input type="text" class="form-control @error('number') is-invalid @enderror" id="number" name="number" value="{{ old('number') }}" placeholder="{{ __('Digite o número') }}">
                            </div>
                            @error('number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Complemento') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <input type="text" class="form-control @error('complement') is-invalid @enderror" id="complement" name="complement" value="{{ old('complement') }}" placeholder="{{ __('Digite o complemento') }}">
                            </div>
                            @error('complement')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Bairro') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map"></i></span>
                                <input type="text" class="form-control @error('district') is-invalid @enderror" id="district" name="district" value="{{ old('district') }}" placeholder="{{ __('Digite o bairro') }}">
                            </div>
                            @error('district')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Cidade') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-city"></i></span>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" placeholder="{{ __('Digite a cidade') }}">
                            </div>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Estado') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marked-alt"></i></span>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state') }}" placeholder="{{ __('Digite o estado') }}">
                            </div>
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logo -->
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-image mr-2"></i>{{ __('Logo') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Selecionar Logo') }}</label>
                            <x-adminlte-input-file name="logo" accept="image/*" class="@error('logo') is-invalid @enderror" id="logo">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-upload"></i></div>
                                </x-slot>
                            </x-adminlte-input-file>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="mb-3">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i> {{ __('Salvar') }}</button>
                <a href="{{ route('admin.parishes.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> {{ __('Voltar') }}</a>
            </div>
        </x-adminlte-card>
    </form>
</div>
@stop

@section('css')
    <style>
        .card {
            transition: all 0.3s ease-in-out;
        }
        .card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .row div {
            margin-bottom: 10px;
        }
        .btn-sm {
            margin-right: 5px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#cnpj').mask('00.000.000/0000-00');
            $('#phone').mask('(00) 0000-0000');
            $('#cellphone').mask('(00) 00000-0000');
            $('#cep').mask('00000-000');

            $('#cep').on('blur', function() {
                var cep = $(this).val().replace(/\D/g, '');
                if (cep.length === 8) {
                    $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function(data) {
                        if (!data.erro) {
                            $('#address').val(data.logradouro);
                            $('#district').val(data.bairro);
                            $('#city').val(data.localidade);
                            $('#state').val(data.uf);
                        } else {
                            alert('{{ __('CEP inválido ou não encontrado.') }}');
                        }
                    }).fail(function() {
                        alert('{{ __('Erro ao buscar CEP.') }}');
                    });
                }
            });

            $('#logo').on('change', function() {
                if (this.files.length > 0) {
                    console.log('Arquivo selecionado: ', this.files[0].name);
                } else {
                    console.log('Nenhum arquivo selecionado');
                }
            });
        });
    </script>
@stop