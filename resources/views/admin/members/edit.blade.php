@extends('adminlte::page')

@section('title', __('Editar Membro'))

@section('content_header')
<div class="container-fluid">
    <div class="mb-2 row">
        <div class="col-sm-6">
            <h1 class="m-0"><i class="mr-2 fas fa-user-edit"></i>{{ __('Editar Membro') }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="mr-1 fas fa-tachometer-alt"></i>{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.members.index') }}"><i class="mr-1 fas fa-users"></i>{{ __('Membros') }}</a></li>
                <li class="breadcrumb-item active"><i class="mr-1 fas fa-user-edit"></i>{{ __('Editar') }}</li>
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
    <form action="{{ route('admin.members.update', $member->id) }}" method="POST" id="memberForm">
        @csrf
        @method('PUT')

        <h5 class="mb-3">{{ __('Informações Pessoais') }}</h5>
        <div class="mb-4 row">
            <div class="col-md-4">
                <x-adminlte-input name="first_name" label="{{ __('Primeiro Nome') }}" value="{{ $member->first_name ?? old('first_name') }}" required fgroup-class="has-feedback" error-key="first_name" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="middle_name" label="{{ __('Nome do Meio') }}" value="{{ $member->middle_name ?? old('middle_name') }}" fgroup-class="has-feedback" error-key="middle_name" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="last_name" label="{{ __('Sobrenome') }}" value="{{ $member->last_name ?? old('last_name') }}" required fgroup-class="has-feedback" error-key="last_name" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <div class="mb-4 row">
            <div class="col-md-6">
                <x-adminlte-input name="birth_date" label="{{ __('Data de Nascimento') }}" type="date" value="{{ $member->birth_date ? $member->birth_date->format('Y-m-d') : old('birth_date') }}" fgroup-class="has-feedback" error-key="birth_date" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-6">
                <x-adminlte-select name="gender" label="{{ __('Gênero') }}" fgroup-class="has-feedback" error-key="gender" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-venus-mars"></i>
                        </div>
                    </x-slot>
                    <option value="">{{ __('Selecione') }}</option>
                    @foreach (\App\Enums\GenderEnum::cases() as $gender)
                    <option value="{{ $gender->value }}" {{ ($member->gender?->value ?? old('gender')) === $gender->value ? 'selected' : '' }}>
                        {{ $gender->label() }}
                    </option>
                    @endforeach
                </x-adminlte-select>
            </div>
        </div>

        <div class="mb-4 row">
            <div class="col-md-3">
                <x-adminlte-input name="cpf" label="{{ __('CPF') }}" id="cpf" value="{{ $member->cpf ?? old('cpf') }}" required fgroup-class="has-feedback" error-key="cpf" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-id-card"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-3">
                <x-adminlte-input name="rg" label="{{ __('RG') }}" id="rg" value="{{ $member->rg ?? old('rg') }}" fgroup-class="has-feedback" error-key="rg" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-id-card"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-6">
                <x-adminlte-select 
                    name="community_id" 
                    label="{{ __('Comunidade') }}" 
                    fgroup-class="has-feedback" 
                    error-key="community_id" 
                    igroup-size="sm" 
                    required
                >
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-church"></i>
                        </div>
                    </x-slot>

                    <option value="">{{ __('Selecione uma comunidade') }}</option>

                    @foreach ($communities as $community)
                        <option value="{{ $community->id }}" 
                                {{ old('community_id', $member->community_id) == $community->id ? 'selected' : '' }}>
                            {{ $community->name }}
                        </option>
                    @endforeach
                </x-adminlte-select>
            </div>
        </div>

        <h5 class="mb-3">{{ __('Dados de Contato') }}</h5>
        <div class="mb-4 row">
            <div class="col-md-4">
                <x-adminlte-input name="phone" label="{{ __('Telefone') }}" id="phone" value="{{ $member->phone ?? old('phone') }}" fgroup-class="has-feedback" error-key="phone" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-phone"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="cellphone" label="{{ __('Celular') }}" id="cellphone" value="{{ $member->cellphone ?? old('cellphone') }}" fgroup-class="has-feedback" error-key="cellphone" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="email" label="{{ __('E-mail') }}" type="email" value="{{ $member->email ?? old('email') }}" fgroup-class="has-feedback" error-key="email" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <h5 class="mb-3">{{ __('Endereço') }}</h5>
        <div class="mb-4 row">
            <div class="col-md-2">
                <x-adminlte-input name="cep" label="{{ __('CEP') }}" id="cep" value="{{ $member->cep ?? old('cep') }}" fgroup-class="has-feedback" error-key="cep" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-8">
                <x-adminlte-input name="address" label="{{ __('Endereço') }}" value="{{ $member->address ?? old('address') }}" fgroup-class="has-feedback" error-key="address" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-road"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-2">
                <x-adminlte-input name="number" label="{{ __('Número') }}" value="{{ $member->number ?? old('number') }}" fgroup-class="has-feedback" error-key="number" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-home"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <div class="mb-4 row">
            <div class="col-md-3">
                <x-adminlte-input name="complement" label="{{ __('Complemento') }}" value="{{ $member->complement ?? old('complement') }}" fgroup-class="has-feedback" error-key="complement" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-info-circle"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-3">
                <x-adminlte-input name="district" label="{{ __('Bairro') }}" value="{{ $member->district ?? old('district') }}" fgroup-class="has-feedback" error-key="district" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-map"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="city" label="{{ __('Cidade') }}" value="{{ $member->city ?? old('city') }}" fgroup-class="has-feedback" error-key="city" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-city"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-2">
                <x-adminlte-input name="state" label="{{ __('Estado') }}" value="{{ $member->state ?? old('state') }}" fgroup-class="has-feedback" error-key="state" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-flag"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <h5 class="mb-3">{{ __('Outras Informações') }}</h5>
        <div class="mb-4 row">
            <div class="col-md-4">
                <x-adminlte-select name="marital_status" label="{{ __('Estado Civil') }}" fgroup-class="has-feedback" error-key="marital_status" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-ring"></i>
                        </div>
                    </x-slot>
                    <option value="">{{ __('Selecione') }}</option>
                    @foreach (\App\Enums\MaritalStatusEnum::cases() as $status)
                    <option value="{{ $status->value }}" {{ ($member->marital_status?->value ?? old('marital_status')) === $status->value ? 'selected' : '' }}>
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
                    <option value="1" {{ ($member->is_active ?? old('is_active', 1)) == 1 ? 'selected' : '' }}>{{ __('Ativo') }}</option>
                    <option value="0" {{ ($member->is_active ?? old('is_active', 1)) == 0 ? 'selected' : '' }}>{{ __('Inativo') }}</option>
                </x-adminlte-select>
            </div>
            <div class="col-md-4">
                <x-adminlte-select name="is_contributor" label="{{ __('Contribuinte') }}" fgroup-class="has-feedback" error-key="is_contributor" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                    </x-slot>
                    <option value="1" {{ ($member->is_contributor ?? old('is_contributor', 1)) == 1 ? 'selected' : '' }}>{{ __('Sim') }}</option>
                    <option value="0" {{ ($member->is_contributor ?? old('is_contributor', 1)) == 0 ? 'selected' : '' }}>{{ __('Não') }}</option>
                </x-adminlte-select>
            </div>
        </div>

        <h5 class="mb-3">{{ __('Filiação') }}</h5>
        <div class="mb-4 row">
            <div class="col-md-6">
                <x-adminlte-input name="father_name" label="{{ __('Nome do Pai') }}" value="{{ $member->father_name ?? old('father_name') }}" fgroup-class="has-feedback" error-key="father_name" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-male"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-6">
                <x-adminlte-input name="mother_name" label="{{ __('Nome da Mãe') }}" value="{{ $member->mother_name ?? old('mother_name') }}" fgroup-class="has-feedback" error-key="mother_name" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-female"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <h5 class="mb-3">{{ __('Contribuição Mensal') }}</h5>
        <div class="mb-4 row">
            <div class="col-md-3">
                <x-adminlte-select name="delivery_method" label="{{ __('Método de Envio') }}" fgroup-class="has-feedback" error-key="delivery_method" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </x-slot>
                    <option value="">{{ __('Selecione') }}</option>
                    @foreach (\App\Enums\DeliveryMethodEnum::cases() as $method)
                    <option value="{{ $method->value }}" {{ ($member->delivery_method instanceof \App\Enums\DeliveryMethodEnum ? $member->delivery_method->value : $member->delivery_method ?? old('delivery_method', 'online')) === $method->value ? 'selected' : '' }}>
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
                    <option value="{{ $method->value }}" {{ ($member->payment_method instanceof \App\Enums\PaymentMethodEnum ? $member->payment_method->value : $member->payment_method ?? old('payment_method')) === $method->value ? 'selected' : '' }}>
                        {{ $method->label() }}
                    </option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <div class="col-md-2">
                <x-adminlte-input name="due_day" label="{{ __('Dia do Mês') }}" type="number" value="{{ $member->due_day ?? old('due_day', 5) }}" min="1" max="31" step="1" fgroup-class="has-feedback" error-key="due_day" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="amount" label="{{ __('Valor') }}" id="amount" value="{{ $member->amount ? number_format($member->amount, 2, ',', '.') : old('amount', '0,00') }}" fgroup-class="has-feedback" error-key="amount" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            R$
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <div class="mb-4 row">
            <div class="col-md-12">
                <x-adminlte-textarea name="observations" label="{{ __('Observações') }}" value="{{ $member->observations ?? old('observations') }}" fgroup-class="has-feedback" error-key="observations" igroup-size="sm">
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
                <x-adminlte-button type="submit" theme="primary" label="{{ __('Atualizar') }}" icon="fas fa-save" class="mr-2 btn-lg" />
                <x-adminlte-button theme="secondary" label="{{ __('Voltar') }}" icon="fas fa-arrow-left" class="btn-lg" onclick="window.location='{{ route('admin.members.index') }}'" />
            </div>
        </div>
    </form>
</x-adminlte-card>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://unpkg.com/imask@7/dist/imask.min.js"></script>
<script>
    $(document).ready(function() {
        // Máscaras para os campos
        $('#cpf').mask('000.000.000-00');
        $('#cep').mask('00000-000');
        $('#phone').mask('(00) 0000-0000');
        $('#cellphone').mask('(00) 0 0000-0000');
        $('#rg').mask('00.000.000-00');

        // Busca de CEP
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
            } else {
                alert('Formato de CEP inválido.');
            }
        });

        // Máscara para o campo amount
        const amountInput = document.getElementById('amount');
        const amountMask = IMask(amountInput, {
            mask: 'R$ num',
            blocks: {
                num: {
                    mask: Number,
                    thousandsSeparator: '.',
                    radix: ',',
                    scale: 2,
                    padFractionalZeros: true,
                    normalizeZeros: true,
                    min: 0
                }
            }
        });

        // Sanitizar os dados antes do envio
        $('#memberForm').on('submit', function(e) {
            $('#cpf').val($('#cpf').val().replace(/\D/g, ''));
            $('#cep').val($('#cep').val().replace(/\D/g, ''));
            $('#phone').val($('#phone').val().replace(/\D/g, ''));
            $('#cellphone').val($('#cellphone').val().replace(/\D/g, ''));
            $('#rg').val($('#rg').val().replace(/\D/g, ''));
            $('#amount').val(amountMask.unmaskedValue.replace(',', '.'));
        });
    });
</script>
@endsection