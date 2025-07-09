@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Editar Meus Dados') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('member.update', $member->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Nome -->
                        <div class="form-group row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Primeiro Nome') }}</label>
                            <div class="col-md-6">
                                <input id="first_name" type="text" class="form-control @error('name') is-invalid @enderror" name="first_name" value="{{ old('first_name', $member->first_name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="middle_name" class="col-md-4 col-form-label text-md-right">{{ __('Nome do meio') }}</label>
                            <div class="col-md-6">
                                <input id="middle_name" type="text" class="form-control @error('middle_name') is-invalid @enderror" name="middle_name" value="{{ old('middle_name', $member->middle_name) }}">
                                @error('middle_name')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="last_name" class="col-md-4 col-form-label text-md-right">{{ __('Sobrenome') }}</label>
                            <div class="col-md-6">
                                <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name', $member->last_name) }}" required>
                                @error('last_name')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-mail') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $member->email) }}" required>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- RG -->
                        <div class="form-group row mb-3">
                            <label for="rg" class="col-md-4 col-form-label text-md-right">{{ __('RG') }}</label>
                            <div class="col-md-6">
                                <input id="rg" type="text" class="form-control @error('rg') is-invalid @enderror" name="rg" value="{{ old('rg', $member->rg) }}">
                                @error('rg')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Data de Nascimento -->
                        <div class="form-group row mb-3">
                            <label for="birth_date" class="col-md-4 col-form-label text-md-right">{{ __('Data de Nascimento') }}</label>
                            <div class="col-md-6">
                                <input id="birth_date" type="date" class="form-control @error('birth_date') is-invalid @enderror" name="birth_date" value="{{ old('birth_date', $member->birth_date) }}">
                                @error('birth_date')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Telefone -->
                        <div class="form-group row mb-3">
                            <label for="phone" class="col-md-4 col-form-label text-md-right">{{ __('Telefone') }}</label>
                            <div class="col-md-6">
                                <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $member->phone) }}">
                                @error('phone')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Celular -->
                        <div class="form-group row mb-3">
                            <label for="cellphone" class="col-md-4 col-form-label text-md-right">{{ __('Celular') }}</label>
                            <div class="col-md-6">
                                <input id="cellphone" type="text" class="form-control @error('cellphone') is-invalid @enderror" name="cellphone" value="{{ old('cellphone', $member->cellphone) }}">
                                @error('cellphone')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- CEP -->
                        <div class="form-group row mb-3">
                            <label for="cep" class="col-md-4 col-form-label text-md-right">{{ __('CEP') }}</label>
                            <div class="col-md-6">
                                <input id="cep" type="text" class="form-control @error('cep') is-invalid @enderror" name="cep" value="{{ old('cep', $member->cep) }}" maxlength="8">
                                @error('cep')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="form-group row mb-3">
                            <label for="address" class="col-md-4 col-form-label text-md-right">{{ __('Endereço') }}</label>
                            <div class="col-md-6">
                                <input id="address" type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address', $member->address) }}">
                                @error('address')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Número -->
                        <div class="form-group row mb-3">
                            <label for="number" class="col-md-4 col-form-label text-md-right">{{ __('Número') }}</label>
                            <div class="col-md-6">
                                <input id="number" type="text" class="form-control @error('number') is-invalid @enderror" name="number" value="{{ old('number', $member->number) }}">
                                @error('number')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Complemento -->
                        <div class="form-group row mb-3">
                            <label for="complement" class="col-md-4 col-form-label text-md-right">{{ __('Complemento') }}</label>
                            <div class="col-md-6">
                                <input id="complement" type="text" class="form-control @error('complement') is-invalid @enderror" name="complement" value="{{ old('complement', $member->complement) }}">
                                @error('complement')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Bairro -->
                        <div class="form-group row mb-3">
                            <label for="district" class="col-md-4 col-form-label text-md-right">{{ __('Bairro') }}</label>
                            <div class="col-md-6">
                                <input id="district" type="text" class="form-control @error('district') is-invalid @enderror" name="district" value="{{ old('district', $member->district) }}">
                                @error('district')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Cidade -->
                        <div class="form-group row mb-3">
                            <label for="city" class="col-md-4 col-form-label text-md-right">{{ __('Cidade') }}</label>
                            <div class="col-md-6">
                                <input id="city" type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city', $member->city) }}">
                                @error('city')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="form-group row mb-3">
                            <label for="state" class="col-md-4 col-form-label text-md-right">{{ __('Estado') }}</label>
                            <div class="col-md-6">
                                <input id="state" type="text" class="form-control @error('state') is-invalid @enderror" name="state" value="{{ old('state', $member->state) }}" maxlength="2">
                                @error('state')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Comunidade -->
                        <div class="form-group row mb-3">
                            <label for="community_id" class="col-md-4 col-form-label text-md-right">{{ __('Comunidade') }}</label>
                            <div class="col-md-6">
                                <select id="community_id" class="form-control @error('community_id') is-invalid @enderror" name="community_id">
                                    <option value="">{{ __('Selecione uma comunidade') }}</option>
                                    @foreach ($communities as $community)
                                        <option value="{{ $community->id }}" {{ old('community_id', $member->community_id) == $community->id ? 'selected' : '' }}>
                                            {{ $community->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('community_id')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Estado Civil -->
                        <div class="form-group row mb-3">
                            <label for="marital_status" class="col-md-4 col-form-label text-md-right">{{ __('Estado Civil') }}</label>
                            <div class="col-md-6">
                                <input id="marital_status" type="text" class="form-control @error('marital_status') is-invalid @enderror" name="marital_status" value="{{ old('marital_status', $member->marital_status) }}">
                                @error('marital_status')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Nome do Pai -->
                        <div class="form-group row mb-3">
                            <label for="father_name" class="col-md-4 col-form-label text-md-right">{{ __('Nome do Pai') }}</label>
                            <div class="col-md-6">
                                <input id="father_name" type="text" class="form-control @error('father_name') is-invalid @enderror" name="father_name" value="{{ old('father_name', $member->father_name) }}">
                                @error('father_name')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Nome da Mãe -->
                        <div class="form-group row mb-3">
                            <label for="mother_name" class="col-md-4 col-form-label text-md-right">{{ __('Nome da Mãe') }}</label>
                            <div class="col-md-6">
                                <input id="mother_name" type="text" class="form-control @error('mother_name') is-invalid @enderror" name="mother_name" value="{{ old('mother_name', $member->mother_name) }}">
                                @error('mother_name')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Salvar') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    console.log('Formulário de edição de membro carregado');

</script>
@stop

@section('js')
@parent

{{-- jQuery Mask Plugin --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

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

</script>
<script src="https://unpkg.com/imask"></script>
<script>
    IMask(document.getElementById('amount'), {
        mask: 'R$ num'
        , blocks: {
            num: {
                mask: Number
                , thousandsSeparator: '.'
                , radix: ','
                , scale: 2
                , padFractionalZeros: true
            , }
        }
    });

</script>
@endsection
