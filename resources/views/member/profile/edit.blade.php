@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dados Cadastrais do Membro') }}</div>

                <div class="card-body">
                    @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                    @endif
                    <div class="text-center mb-4">
                        <div class="mb-2">
                            <i class="bi bi-person-circle text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title">{{ __('Complete seu Perfil') }}</h5>
                        <p class="text-muted">{{ __('Complemente suas informações cadastrais.') }}</p>
                    </div>

                    @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="bi bi-exclamation-circle"></i></strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('member.profile.update') }}" id="profile-form">
                        @csrf
                        @method('PUT')

                        <!-- linha: nome + CPF + RG + gênero -->
                        <div class="row">
                            <h1 class="h5 mb-3">{{ __('Informações Gerais') }}</h1>
                            <hr>
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">{{ __('Primeiro Nome') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input id="first_name" type="text" name="first_name" class="form-control" value="{{ old('first_name', $member->first_name) }}" required>
                                </div>
                                <x-input-error :messages="$errors->get('first_name')" class="text-danger small" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="middle_name" class="form-label">{{ __('Nome do meio') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input id="middle_name" type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $member->middle_name) }}">
                                </div>
                                <x-input-error :messages="$errors->get('first_name')" class="text-danger small" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">{{ __('Sobrenome') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input id="last_name" type="text" name="last_name" class="form-control" value="{{ old('last_name', $member->last_name) }}" required>
                                </div>
                                <x-input-error :messages="$errors->get('last_name')" class="text-danger small" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="cpf" class="form-label">{{ __('CPF') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-file-person"></i></span>
                                    <input id="cpf" type="text" name="cpf" class="form-control" value="{{ old('cpf', $member->cpf) }}" required>
                                </div>
                                <div id="cpf-error" class="text-danger small hidden">O CPF deve ter 11 dígitos.</div>
                                <x-input-error :messages="$errors->get('cpf')" class="text-danger small" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="rg" class="form-label">{{ __('RG') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-card-id"></i></span>
                                    <input id="rg" type="text" name="rg" class="form-control" value="{{ old('rg', $member->rg) }}">
                                </div>
                                <x-input-error :messages="$errors->get('rg')" class="text-danger small" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">{{ __('Gênero') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-gender-ambiguous"></i></span>
                                    <select id="gender" name="gender" class="form-select">
                                        <option value="">{{ __('Selecione o gênero') }}</option>
                                        <option value="masculino" {{ old('gender', $member->gender) == 'masculino' ? 'selected' : '' }}>{{ __('Masculino') }}</option>
                                        <option value="feminino" {{ old('gender', $member->gender) == 'feminino' ? 'selected' : '' }}>{{ __('Feminino') }}</option>
                                        <option value="outro" {{ old('gender', $member->gender) == 'outro' ? 'selected' : '' }}>{{ __('Outro') }}</option>
                                    </select>
                                </div>
                                <x-input-error :messages="$errors->get('gender')" class="text-danger small" />
                            </div>
                        </div>

                        <!-- linha: E-mail + telefone + celular -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="email" class="form-label">{{ __('E-mail') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $member->email) }}" required>
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="text-danger small" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="phone" class="form-label">{{ __('Telefone') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input id="phone" type="text" name="phone" class="form-control" value="{{ old('phone', $member->phone) }}">
                                </div>
                                <x-input-error :messages="$errors->get('phone')" class="text-danger small" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="cellphone" class="form-label">{{ __('Celular') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                    <input id="cellphone" type="text" name="cellphone" class="form-control" value="{{ old('cellphone', $member->cellphone) }}">
                                </div>
                                <x-input-error :messages="$errors->get('cellphone')" class="text-danger small" />
                            </div>
                        </div>

                        <!-- Linha: Comunidade + Data de Nascimento + Estado Civil -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="community_id" class="form-label">{{ __('Comunidade') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
                                    <select id="community_id" name="community_id" class="form-select">
                                        <option value="">{{ __('Selecione uma comunidade (opcional)') }}</option>
                                        @foreach ($communities as $community)
                                        <option value="{{ $community->id }}" {{ old('community_id', $member->community_id) == $community->id ? 'selected' : '' }}>
                                            {{ $community->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-input-error :messages="$errors->get('community_id')" class="text-danger small" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="birth_date" class="form-label">{{ __('Data de Nascimento') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                    <input id="birth_date" type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $member->birth_date ? \Carbon\Carbon::parse($member->birth_date)->format('Y-m-d') : '') }}">
                                </div>
                                <x-input-error :messages="$errors->get('birth_date')" class="text-danger small" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="marital_status" class="form-label">{{ __('Estado Civil') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-heart"></i></span>
                                    <select id="marital_status" name="marital_status" class="form-select">
                                        <option value="">{{ __('Selecione o estado civil') }}</option>
                                        <option value="solteiro" {{ old('marital_status', $member->marital_status) == 'solteiro' ? 'selected' : '' }}>{{ __('Solteiro(a)') }}</option>
                                        <option value="casado" {{ old('marital_status', $member->marital_status) == 'casado' ? 'selected' : '' }}>{{ __('Casado(a)') }}</option>
                                        <option value="divorciado" {{ old('marital_status', $member->marital_status) == 'divorciado' ? 'selected' : '' }}>{{ __('Divorciado(a)') }}</option>
                                        <option value="viuvo" {{ old('marital_status', $member->marital_status) == 'viuvo' ? 'selected' : '' }}>{{ __('Viúvo(a)') }}</option>
                                    </select>
                                </div>
                                <x-input-error :messages="$errors->get('marital_status')" class="text-danger small" />
                            </div>
                        </div>

                        <!-- linha: cep + endereço + numero -->
                        <div class="row mt-4">
                            <h1 class="h5 mb-3">{{ __('Informações de Endereço') }}</h1>
                            <hr>
                            <div class="col-md-3 mb-3">
                                <label for="cep" class="form-label">{{ __('CEP') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-house"></i></span>
                                    <input id="cep" type="text" name="cep" class="form-control" value="{{ old('cep', $member->cep) }}">
                                </div>
                                <div id="cep-error" class="text-danger small hidden">CEP inválido.</div>
                                <x-input-error :messages="$errors->get('cep')" class="text-danger small" />
                            </div>

                            <div class="col-md-7 mb-3">
                                <label for="address" class="form-label">{{ __('Endereço') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-map"></i></span>
                                    <input id="address" type="text" name="address" class="form-control" value="{{ old('address', $member->address) }}">
                                </div>
                                <x-input-error :messages="$errors->get('address')" class="text-danger small" />
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="number" class="form-label">{{ __('Número') }}</label>
                                <input id="number" type="text" name="number" class="form-control" value="{{ old('number', $member->number) }}">
                                <x-input-error :messages="$errors->get('number')" class="text-danger small" />
                            </div>
                        </div>

                        <!-- linha: complemento + bairro + cidade + estado -->
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="complement" class="form-label">{{ __('Complemento') }}</label>
                                <input id="complement" type="text" name="complement" class="form-control" value="{{ old('complement', $member->complement) }}">
                                <x-input-error :messages="$errors->get('complement')" class="text-danger small" />
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="district" class="form-label">{{ __('Bairro') }}</label>
                                <input id="district" type="text" name="district" class="form-control" value="{{ old('district', $member->district) }}">
                                <div x-input-error :group="errors" class="mb-3">
                                    @foreach ($errors->map as $errorx)
                                    <div x-input-error="{{ $errorx->name == 'district' }}" class="text-sm text-red-600">{{ $errorx->message }}</div>
                                    <x-input-error="{{ $errorx->messages }}" class="flex text-center text-red-500" />
                                    <div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">{{ __('Cidade') }}</label>
                                <input id="city" type="text" name="city" class="form-control" value="{{ old('city', $member->city) }}">
                                <x-input-error :messages="$errors->get('city')" class="text-danger small" />
                            </div>

                            <div class="col-md-2 mb-6">
                                <label for="state" class="form-label">{{ __('Estado') }}</label>
                                <input id="state" type="text" name="state" class="form-control" value="{{ old('state', $member->state) }}">
                                <x-input-error :messages="$errors->get('state')" class="text-danger small" />
                            </div>
                        </div>

                        <!-- linha: filiação pai + mãe -->
                        <div class="row mt-4">
                            <h1 class="h5 mb-3">{{ __('Informações de Filiação') }}</h1>
                            <hr>
                            <div class="col-md-6 mb-3">
                                <label for="father_name" class="form-label">{{ __('Nome do Pai') }}</label>
                                <input id="father_name" type="text" name="father_name" class="form-control" value="{{ old('father_name', $member->father_name) }}">
                                <x-input-error :messages="$errors->get('father_name')" class="text-danger small" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="mother_name" class="form-label">{{ __('Nome da Mãe') }}</label>
                                <input id="mother_name" type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $member->mother_name) }}">
                                <x-input-error :messages="$errors->get('mother_name')" class="text-danger small" />
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> {{ __('Salvar') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jQuery === 'undefined' || !$.fn.mask) {
            console.error('jQuery ou jQuery Mask Plugin não carregados.');
            return;
        }

        // Máscaras
        $('#cpf').mask('000.000.000-00');
        $('#phone').mask('(00) 0000-0000');
        $('#cellphone').mask('(00) 00000-0000');
        $('#cep').mask('00000-000');
        $('#rg').mask('00.000.000-0');

        // Validação do CPF
        $('#cpf').on('blur', function() {
            const cpf = $(this).val().replace(/\D/g, '');
            const error = $('#cpf-error');
            if (cpf.length !== 11) {
                $(this).addClass('border-red-500').removeClass('border-green-500');
                error.removeClass('hidden');
            } else {
                $(this).removeClass('border-red-500').addClass('border-green-500');
                error.addClass('hidden');
            }
        });

        // Normalizar campos antes do envio
        $('#profile-form').on('submit', function(e) {
            const cpf = $('#cpf').val().replace(/\D/g, '');
            $('#cpf').val(cpf);
            const phone = $('#phone').val().replace(/\D/g, '');
            $('#phone').val(phone);
            const cellphone = $('#cellphone').val().replace(/\D/g, '');
            $('#cellphone').val(cellphone);
            const cep = $('#cep').val().replace(/\D/g, '');
            $('#cep').val(cep);
            const rg = $('#rg').val().replace(/\D/g, '');
            $('#rg').val(rg);
        });

        // ViaCEP
        $('#cep').on('blur', function() {
            const cep = $(this).val().replace(/\D/g, '');
            const error = $('#cep-error');
            if (cep.length === 8) {
                $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function(data) {
                    if (!data.erro) {
                        $('#address').val(data.logradouro);
                        $('#district').val(data.bairro);
                        $('#city').val(data.localidade);
                        $('#state').val(data.uf);
                        error.addClass('hidden');
                    } else {
                        error.removeClass('hidden');
                    }
                }).fail(function() {
                    error.removeClass('hidden');
                    console.error('Erro ao consultar ViaCEP.');
                });
            } else {
                error.removeClass('hidden');
            }
        });
    });

</script>

<style>
    .animate-fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

</style>
@endsection