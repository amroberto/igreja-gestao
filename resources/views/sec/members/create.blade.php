@extends('adminlte::page')

@section('title', __('Cadastro de Membro'))

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"><i class="fas fa-user-plus mr-2"></i>{{ __('Cadastro de Membro') }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt mr-1"></i>{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.members.index') }}"><i class="fas fa-users mr-1"></i>{{ __('Membros') }}</a></li>
                <li class="breadcrumb-item active"><i class="fas fa-user-plus mr-1"></i>{{ __('Criar') }}</li>
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

<x-adminlte-card title="{{ __('Dados do Membro') }}" theme="primary" icon="fas fa-user" collapsible>
    <form action="{{ route('sec.members.store') }}" method="POST">
        @csrf
        <h5 class="mb-3">{{ __('Informações Pessoais') }}</h5>
        <div class="row mb-4">
            <div class="col-md-6">
                <x-adminlte-input name="name" label="{{ __('Nome') }}" value="{{ old('name') }}" required fgroup-class="has-feedback" error-key="name" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-3">
                <x-adminlte-input name="birth_date" label="{{ __('Data de Nascimento') }}" type="date" value="{{ old('birth_date') }}" fgroup-class="has-feedback" error-key="birth_date" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-3">
                <x-adminlte-select name="gender" label="{{ __('Gênero') }}" fgroup-class="has-feedback" error-key="gender" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-venus-mars"></i>
                        </div>
                    </x-slot>
                    <option value="">{{ __('Selecione') }}</option>
                    @foreach (\App\Enums\GenderEnum::cases() as $gender)
                    <option value="{{ $gender->value }}" {{ old('gender') == $gender->value ? 'selected' : '' }}>
                        {{ $gender->label() }}
                    </option>
                    @endforeach
                </x-adminlte-select>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-3">
                <x-adminlte-input name="cpf" label="{{ __('CPF') }}" value="{{ old('cpf') }}" required fgroup-class="has-feedback" error-key="cpf" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-id-card"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-3">
                <x-adminlte-input name="rg" label="{{ __('RG') }}" value="{{ old('rg') }}" fgroup-class="has-feedback" error-key="rg" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-id-card"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-md-6">
                <x-adminlte-select name="community_id_display" label="{{ __('Comunidade') }}" fgroup-class="has-feedback" error-key="community_id" igroup-size="sm" disabled>
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-church"></i>
                        </div>
                    </x-slot>
                    <option value="{{ Auth::user()->community_id }}">{{ Auth::user()->community->name ?? __('Comunidade do Usuário') }}</option>
                </x-adminlte-select>
                <input type="hidden" name="community_id" value="{{ Auth::user()->community_id }}">
            </div>
        </div>

        <h5 class="mb-3">{{ __('Dados de Contato') }}</h5>
        <div class="row mb-4">
            <div class="col-md-4">
                <x-adminlte-input name="phone" label="{{ __('Telefone') }}" value="{{ old('phone') }}" fgroup-class="has-feedback" error-key="phone" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-phone"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="cellphone" label="{{ __('Celular') }}" value="{{ old('cellphone') }}" fgroup-class="has-feedback" error-key="cellphone" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="email" label="{{ __('E-mail') }}" type="email" value="{{ old('email') }}" fgroup-class="has-feedback" error-key="email" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <h5 class="mb-3">{{ __('Endereço') }}</h5>
        <div class="row mb-4">
            <div class="col-md-2">
                <x-adminlte-input name="cep" label="{{ __('CEP') }}" value="{{ old('cep') }}" fgroup-class="has-feedback" error-key="cep" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-8">
                <x-adminlte-input name="address" label="{{ __('Endereço') }}" value="{{ old('address') }}" fgroup-class="has-feedback" error-key="address" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-road"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-2">
                <x-adminlte-input name="number" label="{{ __('Número') }}" value="{{ old('number') }}" fgroup-class="has-feedback" error-key="number" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-home"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-3">
                <x-adminlte-input name="complement" label="{{ __('Complemento') }}" value="{{ old('complement') }}" fgroup-class="has-feedback" error-key="complement" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-info-circle"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-3">
                <x-adminlte-input name="district" label="{{ __('Bairro') }}" value="{{ old('district') }}" fgroup-class="has-feedback" error-key="district" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-map"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="city" label="{{ __('Cidade') }}" value="{{ old('city') }}" fgroup-class="has-feedback" error-key="city" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-city"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-2">
                <x-adminlte-input name="state" label="{{ __('Estado') }}" value="{{ old('state') }}" fgroup-class="has-feedback" error-key="state" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-flag"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <h5 class="mb-3">{{ __('Outras Informações') }}</h5>
        <div class="row mb-4">
            <div class="col-md-4">
                <x-adminlte-select name="marital_status" label="{{ __('Estado Civil') }}" fgroup-class="has-feedback" error-key="marital_status" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-ring"></i>
                        </div>
                    </x-slot>
                    <option value="">{{ __('Selecione') }}</option>
                    @foreach (\App\Enums\MaritalStatusEnum::cases() as $status)
                    <option value="{{ $status->value }}" {{ old('marital_status') == $status->value ? 'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <div class="col-md-4">
                <x-adminlte-select name="is_active" label="{{ __('Situação') }}" fgroup-class="has-feedback" error-key="is_active" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-toggle-on"></i>
                        </div>
                    </x-slot>
                    <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>{{ __('Ativo') }}</option>
                    <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>{{ __('Inativo') }}</option>
                </x-adminlte-select>
            </div>
            <div class="col-md-4">
                <x-adminlte-select name="is_contributor" label="{{ __('Contribuinte') }}" fgroup-class="has-feedback" error-key="is_contributor" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                    </x-slot>
                    <option value="1" {{ old('is_contributor', 1) == 1 ? 'selected' : '' }}>{{ __('Sim') }}</option>
                    <option value="0" {{ old('is_contributor', 1) == 0 ? 'selected' : '' }}>{{ __('Não') }}</option>
                </x-adminlte-select>
            </div>
        </div>

        <h5 class="mb-3">{{ __('Filiação') }}</h5>
        <div class="row mb-4">
            <div class="col-md-6">
                <x-adminlte-input name="father_name" label="{{ __('Nome do Pai') }}" value="{{ old('father_name') }}" fgroup-class="has-feedback" error-key="father_name" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-male"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-6">
                <x-adminlte-input name="mother_name" label="{{ __('Nome da Mãe') }}" value="{{ old('mother_name') }}" fgroup-class="has-feedback" error-key="mother_name" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-female"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <h5 class="mb-3">{{ __('Contribuição Mensal') }}</h5>
        <div class="row mb-4">
            <div class="col-md-3">
                <x-adminlte-select name="delivery_method" label="{{ __('Método de Envio') }}" fgroup-class="has-feedback" error-key="delivery_method" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </x-slot>
                    <option value="">{{ __('Selecione') }}</option>
                    @foreach (\App\Enums\DeliveryMethodEnum::cases() as $method)
                    <option value="{{ $method->value }}" {{ old('delivery_method') == $method->value ? 'selected' : '' }}>
                        {{ $method->label() }}
                    </option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <div class="col-md-3">
                <x-adminlte-select name="payment_method" label="{{ __('Método de Pagamento') }}" fgroup-class="has-feedback" error-key="payment_method" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </x-slot>
                    <option value="">{{ __('Selecione') }}</option>
                    @foreach (\App\Enums\PaymentMethodEnum::cases() as $method)
                    <option value="{{ $method->value }}" {{ old('payment_method') == $method->value ? 'selected' : '' }}>
                        {{ $method->label() }}
                    </option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <div class="col-md-2">
                <x-adminlte-input name="due_day" label="{{ __('Dia do Mês') }}" type="number" value="{{ old('due_day') }}" min="1" max="31" step="1" required fgroup-class="has-feedback" error-key="due_day" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="amount" label="{{ __('Valor') }}" value="{{ old('amount') }}" fgroup-class="has-feedback" error-key="amount" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-money-bill"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <x-adminlte-textarea name="observations" label="{{ __('Observações') }}" value="{{ old('observations') }}" fgroup-class="has-feedback" error-key="observations" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-comment"></i>
                        </div>
                    </x-slot>
                </x-adminlte-textarea>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <x-adminlte-button type="submit" theme="primary" label="{{ __('Salvar') }}" icon="fas fa-save" class="btn-lg mr-2" />
                <x-adminlte-button theme="secondary" label="{{ __('Voltar') }}" icon="fas fa-arrow-left" class="btn-lg" onclick="window.location='{{ route('sec.members.index') }}'" />
            </div>
        </div>
    </form>
</x-adminlte-card>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://unpkg.com/imask"></script>
<script>
    $(document).ready(function() {
        $('#cellphone').mask('(00) 0 0000-0000');
        $('#phone').mask('(00) 0000-0000');
        $('#cep').mask('00000-000');
        $('#rg').mask('00.000.000-00');
        $('#cpf').mask('000.000.000-00');

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
                        alert('CEP inválido ou não encontrado.');
                    }
                }).fail(function() {
                    alert('Erro ao buscar CEP.');
                });
            }
        });
    });

    IMask(document.getElementById('amount'), {
        mask: 'R$ num'
        , blocks: {
            num: {
                mask: Number
                , thousandsSeparator: '.'
                , radix: ','
                , scale: 2
                , padFractionalZeros: true
            }
        }
    });

</script>
@endsection
