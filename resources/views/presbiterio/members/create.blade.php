@extends('adminlte::page')

@section('title', 'Cadastro de Membro')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
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
        </div>
    </div>
    <br>
    <form action="{{ route('admin.members.store') }}" method="post">
        @csrf
        <div class="card">
            <div class="card-header">
                <h3>Cadastro de Membros</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="name">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nome do Membro" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="cpf">cpf</label>
                        <input type="text" class="form-control" id="cpf" name="cpf" placeholder="CPF">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="rg">RG</label>
                        <input type="text" class="form-control" id="rg" name="rg" placeholder="RG">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="community_id">Comunidade</label>
                        <select class="form-control" id="community_id" name="community_id">
                            <option value="">Selecione uma Comunidade</option>
                            @foreach($communitys as $community)
                            <option value="{{ $community->id }}">{{ $community->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="birth_date">Data de Nascimento</label>
                        <input type="date" class="form-control" id="birth_date" name="birth_date" placeholder="Data de Nascimento">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="email">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="E-mail">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-12">
                        <fieldset class="border p-2">
                            <legend class="w-auto">Dados de Contato</legend>
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="phone">Telefone</label>
                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="Telefone">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="cellphone">Celular</label>
                                    <input type="text" class="form-control" id="cellphone" name="cellphone" placeholder="Celular">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-2">
                                    <label for="zip_code">CEP</label>
                                    <input type="text" class="form-control" id="cep" name="cep" placeholder="CEP">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="address">Endereço</label>
                                    <input type="text" class="form-control" id="address" name="address" placeholder="Endereço">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="number">Número</label>
                                    <input type="text" class="form-control" id="number" name="number" placeholder="Número">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="complement">Complemento</label>
                                    <input type="text" class="form-control" id="complement" name="complement" placeholder="Complemento">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="district">Bairro</label>
                                    <input type="text" class="form-control" id="district" name="district" placeholder="Bairro">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="reference">Cidade</label>
                                    <input type="text" class="form-control" id="city" name="city" placeholder="Cidade">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="state">Estado</label>
                                    <input type="text" class="form-control" id="state" name="state" placeholder="Estado">
                                </div>
                            </div>
                        </fieldset>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>Observações</label>
                                <input type="text" class="form-control" id="observations" name="observations" placeholder="Observações">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="is_active">Situação</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_active" id="is_active1" value="1" checked>
                            <label class="form-check-label" for="is_active1">Ativo</label>
                        </div>

                    <div class="form-group col-md-2">
                        <label>Contribuinte</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_contributor" id="is_contributor1" value="1" checked>
                            <label class="form-check-label" for="is_contributor1">Sim</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_contributor" id="is_contributor0" value="0">
                            <label class="form-check-label" for="is_contributor0">Não</label>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="marital_status">Estado Civil</label>
                        <select class="form-control" id="marital_status" name="marital_status">
                            @foreach(\App\Enums\MaritalStatusEnum::cases() as $status)
                            <option value="{{ $status->value() }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-12">
                        <fieldset class="border p-2">
                            <legend class="w-auto">Filiação</legend>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="father_name">Nome do Pai</label>
                                    <input type="text" class="form-control" id="father_name" name="father_name" placeholder="Nome do Pai">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="mother_name">Nome da Mãe</label>
                                    <input type="text" class="form-control" id="mother_name" name="mother_name" placeholder="Nome da Mãe">
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-12">
                        <fieldset class="border p-2">
                            <legend class="w-auto">Contribuição Mensal</legend>
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="delivery_method">Método de Envio</label>
                                    <select class="form-control" id="delivery_method" name="delivery_method">
                                        @foreach (\App\Enums\DeliveryMethodEnum::cases() as $method)
                                        <option value="{{ $method->value() }}">{{ $method->label() }} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="delivery_method">Método de Pagamento</label>
                                    <select class="form-control" id="payment_method" name="payment_method">
                                        @foreach (\App\Enums\PaymentMethodEnum::cases() as $method)
                                        <option value="{{ $method->value() }}">{{ $method->label() }} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="day">Dia do Mês</label>
                                    <input type="number" class="form-control" id="due_day" name="due_day" min="1" max="31" step="1" placeholder="1 a 31" required>
                                </div>
                                <div class="form-group col-md">
                                    <label>Valor</label>
                                    <input type="text" class="form-control" id="amount" name="amount" placeholder="R$ 0,00">
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="card-footer text-left">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="{{ route('admin.members.index') }}" class="btn btn-secondary">Voltar</a>
                </div>
            </div>
        </div>
    </form>
</div>
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
