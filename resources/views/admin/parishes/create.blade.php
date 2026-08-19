@extends('adminlte::page')

@section('title', __('Cadastrar Paróquia'))

@section('content_header')
    <h1 class="m-0 text-dark">{{ __('Cadastrar Paróquia') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.parishes.index') }}">{{ __('Paróquias') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('Cadastrar') }}</li>
        </ol>
    </nav>

    @if (session('success'))
        <x-adminlte-alert theme="success" title="{{ __('Sucesso') }}" dismissable>
            {{ session('success') }}
        </x-adminlte-alert>
    @elseif (session('error'))
        <x-adminlte-alert theme="danger" title="{{ __('Erro') }}" dismissable>
            {{ session('error') }}
        </x-adminlte-alert>
    @endif

    <form action="{{ route('admin.parishes.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <x-adminlte-card title="{{ __('Cadastrar Paróquia') }}" theme="primary" collapsible>
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        {{ __('Informações Gerais') }}
                    </h3>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('CNPJ') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input
                                    type="text"
                                    class="form-control @error('cnpj') is-invalid @enderror"
                                    id="cnpj"
                                    name="cnpj"
                                    value="{{ old('cnpj') }}"
                                    placeholder="{{ __('Digite o CNPJ') }}"
                                >
                            </div>
                            @error('cnpj')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Nome') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-church"></i></span>
                                <input
                                    type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    id="name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="{{ __('Digite o nome da paróquia') }}"
                                    required
                                >
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-address-book mr-2"></i>
                        {{ __('Informações de Contato') }}
                    </h3>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Telefone') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input
                                    type="text"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                >
                            </div>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Celular') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                                <input
                                    type="text"
                                    class="form-control @error('cellphone') is-invalid @enderror"
                                    id="cellphone"
                                    name="cellphone"
                                    value="{{ old('cellphone') }}"
                                >
                            </div>
                            @error('cellphone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('E-mail') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                >
                            </div>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Website') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                <input
                                    type="text"
                                    class="form-control @error('website') is-invalid @enderror"
                                    id="website"
                                    name="website"
                                    value="{{ old('website') }}"
                                >
                            </div>
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        {{ __('Informações de Endereço') }}
                    </h3>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('CEP') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-pin"></i></span>
                                <input
                                    type="text"
                                    class="form-control @error('cep') is-invalid @enderror"
                                    id="cep"
                                    name="cep"
                                    value="{{ old('cep') }}"
                                >
                            </div>
                            @error('cep')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Endereço') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-road"></i></span>
                                <input
                                    type="text"
                                    class="form-control @error('address') is-invalid @enderror"
                                    id="address"
                                    name="address"
                                    value="{{ old('address') }}"
                                >
                            </div>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Número') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-house"></i></span>
                                <input
                                    type="text"
                                    class="form-control @error('number') is-invalid @enderror"
                                    id="number"
                                    name="number"
                                    value="{{ old('number') }}"
                                >
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
                                <input
                                    type="text"
                                    class="form-control @error('complement') is-invalid @enderror"
                                    id="complement"
                                    name="complement"
                                    value="{{ old('complement') }}"
                                >
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Bairro') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map"></i></span>
                                <input
                                    type="text"
                                    class="form-control @error('district') is-invalid @enderror"
                                    id="district"
                                    name="district"
                                    value="{{ old('district') }}"
                                >
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Cidade') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-city"></i></span>
                                <input
                                    type="text"
                                    class="form-control @error('city') is-invalid @enderror"
                                    id="city"
                                    name="city"
                                    value="{{ old('city') }}"
                                >
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12 mb-3">
                            <label class="form-label">{{ __('Estado') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marked-alt"></i></span>
                                <input
                                    type="text"
                                    class="form-control @error('state') is-invalid @enderror"
                                    id="state"
                                    name="state"
                                    value="{{ old('state') }}"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-qrcode mr-2"></i>
                        {{ __('Configuração do Pix Estático') }}
                    </h3>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">{{ __('Ativar Pix Estático') }}</label>
                            <select
                                id="pix_static_enabled"
                                name="pix_static_enabled"
                                class="form-control @error('pix_static_enabled') is-invalid @enderror"
                            >
                                <option value="0" {{ old('pix_static_enabled', 0) == 0 ? 'selected' : '' }}>
                                    {{ __('Não') }}
                                </option>
                                <option value="1" {{ old('pix_static_enabled', 0) == 1 ? 'selected' : '' }}>
                                    {{ __('Sim') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3 pix-fields">
                            <label class="form-label">{{ __('Tipo da Chave Pix') }}</label>
                            <select
                                name="pix_key_type"
                                class="form-control @error('pix_key_type') is-invalid @enderror"
                            >
                                <option value="">{{ __('Selecione') }}</option>
                                <option value="cnpj" {{ old('pix_key_type') === 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                                <option value="cpf" {{ old('pix_key_type') === 'cpf' ? 'selected' : '' }}>CPF</option>
                                <option value="email" {{ old('pix_key_type') === 'email' ? 'selected' : '' }}>E-mail</option>
                                <option value="phone" {{ old('pix_key_type') === 'phone' ? 'selected' : '' }}>Telefone</option>
                                <option value="random" {{ old('pix_key_type') === 'random' ? 'selected' : '' }}>Chave aleatória</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3 pix-fields">
                            <label class="form-label">{{ __('Chave Pix') }}</label>
                            <input
                                type="text"
                                id="pix_key"
                                name="pix_key"
                                class="form-control @error('pix_key') is-invalid @enderror"
                                value="{{ old('pix_key') }}"
                                placeholder="{{ __('Informe a chave Pix da Paróquia') }}"
                            >
                            @error('pix_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row pix-fields">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">{{ __('Nome do Recebedor no Pix') }}</label>
                            <input
                                type="text"
                                name="pix_receiver_name"
                                class="form-control @error('pix_receiver_name') is-invalid @enderror"
                                value="{{ old('pix_receiver_name', old('name')) }}"
                                placeholder="{{ __('Ex.: PARÓQUIA SANTO AMARO') }}"
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('Cidade do Pix') }}</label>
                            <input
                                type="text"
                                name="pix_city"
                                class="form-control @error('pix_city') is-invalid @enderror"
                                value="{{ old('pix_city', old('city')) }}"
                                placeholder="{{ __('Ex.: SÃO PAULO') }}"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-image mr-2"></i>
                        {{ __('Logo') }}
                    </h3>
                </div>

                <div class="card-body">
                    <x-adminlte-input-file
                        name="logo"
                        accept="image/*"
                        class="@error('logo') is-invalid @enderror"
                        id="logo"
                    >
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-upload"></i></div>
                        </x-slot>
                    </x-adminlte-input-file>

                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-save mr-1"></i> {{ __('Salvar') }}
                </button>

                <a href="{{ route('admin.parishes.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> {{ __('Voltar') }}
                </a>
            </div>
        </x-adminlte-card>
    </form>
</div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
    $(document).ready(function () {
        $('#cnpj').mask('00.000.000/0000-00');
        $('#phone').mask('(00) 0000-0000');
        $('#cellphone').mask('(00) 00000-0000');
        $('#cep').mask('00000-000');

        function togglePixFields() {
            $('.pix-fields').toggle($('#pix_static_enabled').val() === '1');
        }

        function applyPixKeyMask() {
            const type = $('select[name="pix_key_type"]').val();
            const input = $('#pix_key');

            input.unmask();
            input.attr('placeholder', 'Informe a chave Pix');

            if (type === 'cnpj') {
                input.mask('00.000.000/0000-00');
                input.attr('placeholder', '00.000.000/0000-00');
            }

            if (type === 'cpf') {
                input.mask('000.000.000-00');
                input.attr('placeholder', '000.000.000-00');
            }

            if (type === 'phone') {
                input.mask('+55 (00) 00000-0000');
                input.attr('placeholder', '+55 (11) 99999-9999');
            }

            if (type === 'email') {
                input.attr('placeholder', 'pix@paroquia.org.br');
            }

            if (type === 'random') {
                input.attr('placeholder', 'Chave aleatória do Pix');
            }
        }

        $('#pix_static_enabled').on('change', togglePixFields);

        $('select[name="pix_key_type"]').on('change', function () {
            $('#pix_key').val('');
            applyPixKeyMask();
        });

        togglePixFields();
        applyPixKeyMask();

        $('#cep').on('blur', function () {
            const cep = $(this).val().replace(/\D/g, '');

            if (cep.length !== 8) {
                return;
            }

            $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function (data) {
                if (data.erro) {
                    alert('{{ __('CEP inválido ou não encontrado.') }}');
                    return;
                }

                $('#address').val(data.logradouro);
                $('#district').val(data.bairro);
                $('#city').val(data.localidade);
                $('#state').val(data.uf);
            });
        });
    });
</script>
@stop