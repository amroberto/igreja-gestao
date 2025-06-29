@extends('adminlte::page')

@section('title', __('Editar Membro'))

@section('content_header')
<h1 class="m-0 text-dark">{{ __('Editar Membro') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.members.index') }}">{{ __('Membros') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Editar Membro') }}</li>
        </ol>
    </nav>

    <!-- Alertas -->
    <div class="row">
        <div class="col-12">
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
        </div>
    </div>

    <form action="{{ route('admin.members.update', ['member' => $member]) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Informações Pessoais -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user mr-2"></i>{{ __('Informações Pessoais') }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 col-sm-12 mb-3">
                        <label for="name" class="form-label">{{ __('Nome Completo') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $member->name) }}" required>
                        </div>
                        <x-input-error :messages="$errors->get('name')" class="text-danger small" />
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <label for="cpf" class="form-label">{{ __('CPF') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" class="form-control" id="cpf" name="cpf" value="{{ old('cpf', $member->cpf) }}" required>
                        </div>
                        <x-input-error :messages="$errors->get('cpf')" class="text-danger small" />
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <label for="rg" class="form-label">{{ __('RG') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                            <input type="text" class="form-control" id="rg" name="rg" value="{{ old('rg', $member->rg) }}">
                        </div>
                        <x-input-error :messages="$errors->get('rg')" class="text-danger small" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-sm-6 mb-3">
                        <label for="gender" class="form-label">{{ __('Gênero') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                            <select id="gender" name="gender" class="form-control">
                                <option value="">{{ __('Selecione o Gênero') }}</option>
                                <option value="masculino" {{ old('gender', $member->gender) == 'masculino' ? 'selected' : '' }}>{{ __('Masculino') }}</option>
                                <option value="feminino" {{ old('gender', $member->gender) == 'feminino' ? 'selected' : '' }}>{{ __('Feminino') }}</option>
                                <option value="outro" {{ old('gender', $member->gender) == 'outro' ? 'selected' : '' }}>{{ __('Outro') }}</option>
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('gender')" class="text-danger small" />
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <label for="birth_date" class="form-label">{{ __('Data de Nascimento') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="birth_date" name="birth_date" value="{{ old('birth_date', $member->birth_date ? \Carbon\Carbon::parse($member->birth_date)->format('Y-m-d') : '') }}">
                        </div>
                        <x-input-error :messages="$errors->get('birth_date')" class="text-danger small" />
                    </div>
                    <div class="col-md-4 col-sm-12 mb-3">
                        <label for="marital_status" class="form-label">{{ __('Estado Civil') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-ring"></i></span>
                            <select id="marital_status" name="marital_status" class="form-control">
                                <option value="">{{ __('Selecione o Estado Civil') }}</option>
                                @foreach (\App\Enums\MaritalStatusEnum::cases() as $status)
                                <option value="{{ $status->value }}" {{ old('marital_status', $member->marital_status) == $status->value ? 'selected' : '' }}>
                                    {{ $status->getLabels()[$status->value] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('marital_status')" class="text-danger small" />
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
                    <div class="col-md-4 col-sm-6 mb-3">
                        <label for="email" class="form-label">{{ __('E-mail') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $member->email) }}" required>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="text-danger small" />
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <label for="phone" class="form-label">{{ __('Telefone') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $member->phone) }}">
                        </div>
                        <x-input-error :messages="$errors->get('phone')" class="text-danger small" />
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <label for="cellphone" class="form-label">{{ __('Celular') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                            <input type="text" class="form-control" id="cellphone" name="cellphone" value="{{ old('cellphone', $member->cellphone) }}">
                        </div>
                        <x-input-error :messages="$errors->get('cellphone')" class="text-danger small" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-sm-12 mb-3">
                        <label class="form-label">{{ __('Comunidade') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-users"></i></span>
                            <select name="community_id" class="form-control">
                                <option value="">{{ __('Selecione uma comunidade') }}</option>
                                @foreach ($communities as $community)
                                <option value="{{ $community->id }}" {{ $member->community_id == $community->id ? 'selected' : '' }}>
                                    {{ $community->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
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
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="cep" class="form-label">{{ __('CEP') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-pin"></i></span>
                            <input type="text" class="form-control" id="cep" name="cep" value="{{ old('cep', $member->cep) }}">
                        </div>
                        <x-input-error :messages="$errors->get('cep')" class="text-danger small" />
                    </div>
                    <div class="col-md-6 col-sm-12 mb-3">
                        <label for="address" class="form-label">{{ __('Endereço') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-road"></i></span>
                            <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $member->address) }}">
                        </div>
                        <x-input-error :messages="$errors->get('address')" class="text-danger small" />
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="number" class="form-label">{{ __('Número') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-home"></i></span>
                            <input type="text" class="form-control" id="number" name="number" value="{{ old('number', $member->number) }}">
                        </div>
                        <x-input-error :messages="$errors->get('number')" class="text-danger small" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="complement" class="form-label">{{ __('Complemento') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <input type="text" class="form-control" id="complement" name="complement" value="{{ old('complement', $member->complement) }}">
                        </div>
                        <x-input-error :messages="$errors->get('complement')" class="text-danger small" />
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="district" class="form-label">{{ __('Bairro') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-city"></i></span>
                            <input type="text" class="form-control" id="district" name="district" value="{{ old('district', $member->district) }}">
                        </div>
                        <x-input-error :messages="$errors->get('district')" class="text-danger small" />
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="city" class="form-label">{{ __('Cidade') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map"></i></span>
                            <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $member->city) }}">
                        </div>
                        <x-input-error :messages="$errors->get('city')" class="text-danger small" />
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="state" class="form-label">{{ __('Estado') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-globe"></i></span>
                            <input type="text" class="form-control" id="state" name="state" value="{{ old('state', $member->state) }}">
                        </div>
                        <x-input-error :messages="$errors->get('state')" class="text-danger small" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações de Filiação -->
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users mr-2"></i>{{ __('Informações de Filiação') }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-sm-12 mb-3">
                        <label for="father_name" class="form-label">{{ __('Nome do Pai') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-male"></i></span>
                            <input type="text" class="form-control" id="father_name" name="father_name" value="{{ old('father_name', $member->father_name) }}">
                        </div>
                        <x-input-error :messages="$errors->get('father_name')" class="text-danger small" />
                    </div>
                    <div class="col-md-6 col-sm-12 mb-3">
                        <label for="mother_name" class="form-label">{{ __('Nome da Mãe') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-female"></i></span>
                            <input type="text" class="form-control" id="mother_name" name="mother_name" value="{{ old('mother_name', $member->mother_name) }}">
                        </div>
                        <x-input-error :messages="$errors->get('mother_name')" class="text-danger small" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Contribuição Mensal -->
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-donate mr-2"></i>{{ __('Contribuição Mensal') }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="delivery_method" class="form-label">{{ __('Método de Envio') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-truck"></i></span>
                            <select id="delivery_method" name="delivery_method" class="form-control">
                                <option value="">{{ __('Selecione o Método') }}</option>
                                @foreach (\App\Enums\DeliveryMethodEnum::cases() as $method)
                                <option value="{{ $method->value }}" {{ old('delivery_method', $member->delivery_method) == $method->value ? 'selected' : '' }}>
                                    {{ $method->label() }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('delivery_method')" class="text-danger small" />
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="payment_method" class="form-label">{{ __('Método de Pagamento') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                            <select id="payment_method" name="payment_method" class="form-control">
                                <option value="">{{ __('Selecione o Método') }}</option>
                                @foreach (\App\Enums\PaymentMethodEnum::cases() as $method)
                                <option value="{{ $method->value }}" {{ old('payment_method', $member->payment_method) == $method->value ? 'selected' : '' }}>
                                    {{ $method->getLabels() }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('payment_method')" class="text-danger small" />
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="due_day" class="form-label">{{ __('Dia do Mês') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                            <input type="number" class="form-control" id="due_day" name="due_day" min="1" max="31" step="1" value="{{ old('due_day', $member->due_day) }}">
                        </div>
                        <x-input-error :messages="$errors->get('due_day')" class="text-danger small" />
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="amount" class="form-label">{{ __('Valor') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control" id="amount" name="amount" value="{{ old('amount', $member->amount ? number_format($member->amount, 2, ',', '.') : '') }}">
                        </div>
                        <x-input-error :messages="$errors->get('amount')" class="text-danger small" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="is_active" class="form-label">{{ __('Status') }}</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="is_active" id="is_active1" value="1" {{ old('is_active', $member->is_active) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active1">{{ __('Ativo') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="is_active" id="is_active0" value="0" {{ old('is_active', $member->is_active) == 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active0">{{ __('Inativo') }}</label>
                        </div>
                        <x-input-error :messages="$errors->get('is_active')" class="text-danger small" />
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="is_contributor" class="form-label">{{ __('Contribuinte') }}</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="is_contributor" id="is_contributor1" value="1" {{ old('is_contributor', $member->is_contributor) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_contributor1">{{ __('Sim') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="is_contributor" id="is_contributor0" value="0" {{ old('is_contributor', $member->is_contributor) == 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_contributor0">{{ __('Não') }}</label>
                        </div>
                        <x-input-error :messages="$errors->get('is_contributor')" class="text-danger small" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Observações -->
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-comment mr-2"></i>{{ __('Observações') }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="observations" class="form-label">{{ __('Observações') }}</label>
                        <textarea class="form-control" id="observations" name="observations" rows="4">{{ old('observations', $member->observations) }}</textarea>
                        <x-input-error :messages="$errors->get('observations')" class="text-danger small" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="card card-outline">
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> {{ __('Salvar') }}</button>
                <a href="{{ route('admin.members.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> {{ __('Voltar') }}</a>
            </div>
        </div>
    </form>
</div>
@stop

@section('css')
<style>
    .card {
        transition: all 0.3s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .form-check {
        margin-bottom: 0.5rem;
    }

</style>
@stop

@section('js')
@parent
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function() {
        // Máscaras
        $('#cpf').mask('000.000.000-00');
        $('#rg').mask('00.000.000-0');
        $('#phone').mask('(00) 0000-0000');
        $('#cellphone').mask('(00) 00000-0000');
        $('#cep').mask('00000-000');
        $('#amount').mask('000.000,00', {
            reverse: true
        });

        // Validação do CPF
        $('#cpf').on('blur', function() {
            const cpf = $(this).val().replace(/\D/g, '');
            if (cpf.length !== 11) {
                $(this).addClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
                $(this).after('<div class="invalid-feedback">O CPF deve ter 11 dígitos.</div>');
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            }
        });

        // ViaCEP
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
                        $('#cep').after('<div class="invalid-feedback">CEP inválido.</div>');
                    }
                }).fail(function() {
                    $('#cep').addClass('is-invalid');
                    $('#cep').next('.invalid-feedback').remove();
                    $('#cep').after('<div class="invalid-feedback">Erro ao consultar CEP.</div>');
                });
            } else {
                $('#cep').addClass('is-invalid');
                $('#cep').next('.invalid-feedback').remove();
                $('#cep').after('<div class="invalid-feedback">CEP deve ter 8 dígitos.</div>');
            }
        });

        // Normalizar campos antes do envio
        $('form').on('submit', function() {
            $('#cpf').val($('#cpf').val().replace(/\D/g, ''));
            $('#rg').val($('#rg').val().replace(/\D/g, ''));
            $('#phone').val($('#phone').val().replace(/\D/g, ''));
            $('#cellphone').val($('#cellphone').val().replace(/\D/g, ''));
            $('#cep').val($('#cep').val().replace(/\D/g, ''));
            $('#amount').val($('#amount').val().replace(/\D/g, '').replace(',', '.'));
        });
    });

</script>
@stop
