@extends('adminlte::page')

@section('title', __('Editar União Paroquial'))

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"><i class="fas fa-building mr-2"></i>{{ __('Editar União Paroquial') }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt mr-1"></i>{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><i class="fas fa-cog mr-1"></i>{{ __('Configurações') }}</li>
                <li class="breadcrumb-item active"><i class="fas fa-building mr-1"></i>{{ __('União Paroquial') }}</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('content')
@if (session('success'))
<x-adminlte-alert theme="success" title="{{ __('Sucesso') }}" icon="fas fa-check-circle" dismissable>
    {{ session('success') }}
</x-adminlte-alert>
@endif

@if ($errors->any())
<x-adminlte-alert theme="danger" title="{{ __('Erros de Validação') }}" icon="fas fa-exclamation-triangle" dismissable>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</x-adminlte-alert>
@endif

<x-adminlte-card title="{{ __('Dados da União Paroquial') }}" theme="primary" icon="fas fa-building" collapsible>
    <form action="{{ route('admin.parochial-union.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-md-6">
                <x-adminlte-input name="corporate_name" label="{{ __('Razão Social') }}" value="{{ old('corporate_name', $union->corporate_name) }}" required fgroup-class="has-feedback" error-key="corporate_name" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-building"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-6">
                <x-adminlte-input name="trade_name" label="{{ __('Nome Fantasia') }}" value="{{ old('trade_name', $union->trade_name) }}" required fgroup-class="has-feedback" error-key="trade_name" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-store"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-adminlte-input name="cnpj" id="cnpj" label="{{ __('CNPJ') }}" value="{{ old('cnpj', $union->cnpj) }}" required fgroup-class="has-feedback" error-key="cnpj" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-id-card"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="email" label="{{ __('E-mail') }}" type="email" value="{{ old('email', $union->email) }}" fgroup-class="has-feedback" error-key="email" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-envelope"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="website" label="{{ __('Website') }}" type="url" value="{{ old('website', $union->website) }}" fgroup-class="has-feedback" error-key="website" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-globe"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-adminlte-input name="phone" id="phone" label="{{ __('Telefone') }}" value="{{ old('phone', $union->phone) }}" fgroup-class="has-feedback" error-key="phone" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-phone"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-6">
                <x-adminlte-input name="cellphone" id="cellphone" label="{{ __('Celular/WhatsApp') }}" value="{{ old('cellphone', $union->cellphone) }}" fgroup-class="has-feedback" error-key="cellphone" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fab fa-whatsapp"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <x-adminlte-input name="cep" id="cep" label="{{ __('CEP') }}" value="{{ old('cep', $union->cep) }}" fgroup-class="has-feedback" error-key="cep" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-map-marker-alt"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-8">
                <x-adminlte-input name="address" id="address" label="{{ __('Endereço') }}" value="{{ old('address', $union->address) }}" fgroup-class="has-feedback" error-key="address" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-road"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-2">
                <x-adminlte-input name="number" label="{{ __('Número') }}" value="{{ old('number', $union->number) }}" fgroup-class="has-feedback" error-key="number" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-home"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <x-adminlte-input name="complement" label="{{ __('Complemento') }}" value="{{ old('complement', $union->complement) }}" fgroup-class="has-feedback" error-key="complement" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-info-circle"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-3">
                <x-adminlte-input name="district" id="district" label="{{ __('Bairro') }}" value="{{ old('district', $union->district) }}" fgroup-class="has-feedback" error-key="district" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-map"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="city" id="city" label="{{ __('Cidade') }}" value="{{ old('city', $union->city) }}" fgroup-class="has-feedback" error-key="city" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-city"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-2">
                <x-adminlte-input name="state" id="state" label="{{ __('Estado') }}" value="{{ old('state', $union->state) }}" fgroup-class="has-feedback" error-key="state" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-flag"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        {{-- Bloco do Pix Estático --}}
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
                        <select id="pix_static_enabled" name="pix_static_enabled" class="form-control">
                            <option value="0" {{ old('pix_static_enabled', $union->pix_static_enabled) == 0 ? 'selected' : '' }}>
                                {{ __('Não') }}
                            </option>
                            <option value="1" {{ old('pix_static_enabled', $union->pix_static_enabled) == 1 ? 'selected' : '' }}>
                                {{ __('Sim') }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3 pix-fields">
                        <label class="form-label">{{ __('Tipo da Chave Pix') }}</label>
                        <select name="pix_key_type" id="pix_key_type" class="form-control">
                            <option value="">{{ __('Selecione') }}</option>
                            <option value="cnpj" {{ old('pix_key_type', $union->pix_key_type) === 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                            <option value="cpf" {{ old('pix_key_type', $union->pix_key_type) === 'cpf' ? 'selected' : '' }}>CPF</option>
                            <option value="email" {{ old('pix_key_type', $union->pix_key_type) === 'email' ? 'selected' : '' }}>E-mail</option>
                            <option value="phone" {{ old('pix_key_type', $union->pix_key_type) === 'phone' ? 'selected' : '' }}>Telefone</option>
                            <option value="random" {{ old('pix_key_type', $union->pix_key_type) === 'random' ? 'selected' : '' }}>Chave aleatória</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3 pix-fields">
                        <label class="form-label">{{ __('Chave Pix') }}</label>
                        <input type="text" id="pix_key" name="pix_key"
                            class="form-control @error('pix_key') is-invalid @enderror"
                            value="{{ old('pix_key', $union->pix_key) }}"
                            placeholder="{{ __('Informe a chave Pix da União Paroquial') }}">
                    </div>
                </div>

                <div class="row pix-fields">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">{{ __('Nome do Recebedor no Pix') }}</label>
                        <input type="text" name="pix_receiver_name" class="form-control"
                            value="{{ old('pix_receiver_name', $union->pix_receiver_name ?: $union->trade_name) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Cidade do Pix') }}</label>
                        <input type="text" name="pix_city" class="form-control"
                            value="{{ old('pix_city', $union->pix_city ?: $union->city) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fas fa-image mr-1"></i>{{ __('Logo Atual') }}</label>
                    @if ($union->logo && Storage::disk('public')->exists($union->logo))
                    <div class="card card-light card-outline mb-3">
                        <div class="card-body text-center">
                            <img src="{{ Storage::url($union->logo) }}" alt="Logo da União Paroquial" class="img-fluid rounded shadow-sm" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                        </div>
                    </div>
                    @else
                    <p class="text-muted"><i class="fas fa-info-circle mr-1"></i>{{ __('Nenhuma logo carregada ou arquivo não encontrado') }}</p>
                    @endif
                    <x-adminlte-input name="logo" label="{{ __('Nova Logo') }}" type="file" accept="image/*" fgroup-class="has-feedback" error-key="logo" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-upload"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <x-adminlte-button type="submit" theme="primary" label="{{ __('Salvar') }}" icon="fas fa-save" class="btn-lg" />
            </div>
        </div>
    </form>
</x-adminlte-card>
@endsection

@section('css')
<style>
    .card {
        transition: all 0.3s ease-in-out;
    }
    .card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>
@stop

@section('js')
@parent
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function() {
        // Máscaras de campos gerais
        $('#cnpj').mask('00.000.000/0000-00');
        $('#phone').mask('(00) 0000-0000');
        $('#cellphone').mask('(00) 00000-0000');
        $('#cep').mask('00000-000');

        function togglePixFields() {
            $('.pix-fields').toggle($('#pix_static_enabled').val() === '1');
        }

        function applyPixKeyMask() {
            const type = $('#pix_key_type').val();
            const input = $('#pix_key');

            input.unmask();
            input.attr('placeholder', 'Informe a chave Pix');

            if (type === 'cnpj') {
                input.mask('00.000.000/0000-00');
                input.attr('placeholder', '00.000.000/0000-00');
            } else if (type === 'cpf') {
                input.mask('000.000.000-00');
                input.attr('placeholder', '000.000.000-00');
            } else if (type === 'phone') {
                input.mask('+55 (00) 00000-0000');
                input.attr('placeholder', '+55 (11) 99999-9999');
            } else if (type === 'email') {
                input.attr('placeholder', 'pix@uniaoparoquial.org.br');
            } else if (type === 'random') {
                input.attr('placeholder', 'Chave aleatória do Pix');
            }
        }

        $('#pix_static_enabled').on('change', togglePixFields);

        // Limpa o valor do Pix KEY apenas se o usuário alterar a seleção manualmente
        $('#pix_key_type').on('change', function () {
            $('#pix_key').val('');
            applyPixKeyMask();
        });

        // Inicializações na abertura da tela
        togglePixFields();
        applyPixKeyMask();

        // Autocorreção e busca ViaCEP
        $('#cep').on('blur', function() {
            const cep = $(this).val().replace(/\D/g, '');
            if (cep.length === 8) {
                $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function(data) {
                    if (!data.erro) {
                        $('#address').val(data.logradouro);
                        $('#district').val(data.bairro);
                        $('#city').val(data.localidade);
                        $('#state').val(data.uf);
                        $('#cep').removeClass('is-invalid');
                    } else {
                        $('#cep').addClass('is-invalid');
                        $('#cep').next('.invalid-feedback').remove();
                        $('#cep').after('<div class="invalid-feedback">CEP não encontrado.</div>');
                    }
                }).fail(function() {
                    $('#cep').addClass('is-invalid');
                    $('#cep').next('.invalid-feedback').remove();
                    $('#cep').after('<div class="invalid-feedback">Erro ao consultar CEP.</div>');
                });
            } else if(cep.length > 0) {
                $('#cep').addClass('is-invalid');
                $('#cep').next('.invalid-feedback').remove();
                $('#cep').after('<div class="invalid-feedback">CEP deve ter 8 dígitos.</div>');
            }
        });

        // Normalizar apenas os campos que possuem máscara de fato antes do envio do form
        $('form').on('submit', function() {
            $('#cnpj').val($('#cnpj').val().replace(/\D/g, ''));
            $('#phone').val($('#phone').val().replace(/\D/g, ''));
            $('#cellphone').val($('#cellphone').val().replace(/\D/g, ''));
            $('#cep').val($('#cep').val().replace(/\D/g, ''));
            
            // Tratamento especial para telefone caso seja a chave Pix
            if ($('#pix_key_type').val() === 'phone' || $('#pix_key_type').val() === 'cnpj' || $('#pix_key_type').val() === 'cpf') {
                // Se for número com +55 ou pontuação, limpa se necessário (ou mantém dependendo do backend)
            }
        });
    });
</script>
@stop