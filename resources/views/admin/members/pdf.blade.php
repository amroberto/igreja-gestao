<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Detalhes do Membro') }} - {{ $member->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 20px;
            color: #333;
        }
        h1 {
            font-size: 18pt;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }
        h3 {
            font-size: 14pt;
            color: #34495e;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #fff;
        }
        .row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            clear: both;
        }
        .col-md-4 {
            display: table-cell;
            width: 33.33%;
            padding: 5px;
            vertical-align: top;
        }
        .col-md-6 {
            display: table-cell;
            width: 50%;
            padding: 5px;
            vertical-align: top;
        }
        .col-md-3 {
            display: table-cell;
            width: 25%;
            padding: 5px;
            vertical-align: top;
        }
        .col-md-12 {
            display: block;
            width: 100%;
            padding: 5px;
        }
        strong {
            display: inline-block;
            width: 120px;
            font-weight: bold;
        }
        p {
            margin: 0 0 10px 0;
        }
    </style>
</head>
<body>
    <h1>{{ __('Detalhes do Membro') }}: {{ $member->name }}</h1>

    <!-- Informações Pessoais -->
    <div class="card">
        <h3>{{ __('Informações Pessoais') }}</h3>
        <div class="row">
            <div class="col-md-12"><strong>{{ __('Nome') }}:</strong> {{ $member->name }}</div>
        </div>
        <div class="row">
            <div class="col-md-6"><strong>{{ __('CPF') }}:</strong> {{ formatCpf($member->cpf) }}</div>
            <div class="col-md-6"><strong>{{ __('RG') }}:</strong> {{ formatRg($member->rg) ?? '-' }}</div>
        </div>
        <div class="row">
            <div class="col-md-6"><strong>{{ __('Gênero') }}:</strong> {{ ucfirst($member->gender ?? '-') }}</div>
            <div class="col-md-6"><strong>{{ __('Data de Nascimento') }}:</strong> {{ $member->birth_date ? \Carbon\Carbon::parse($member->birth_date)->format('d/m/Y') : '-' }}</div>
        </div>
        <div class="row">
            <div class="col-md-12"><strong>{{ __('Estado Civil') }}:</strong> {{ $member->marital_status ? \App\Enums\MaritalStatusEnum::tryFrom($member->marital_status)->getLabels()[$member->marital_status] : '-' }}</div>
        </div>
    </div>

    <!-- Informações de Contato -->
    <div class="card">
        <h3>{{ __('Informações de Contato') }}</h3>
        <div class="row">
            <div class="col-md-6"><strong>{{ __('E-mail') }}:</strong> {{ $member->email ?? '-' }}</div>
            <div class="col-md-6"><strong>{{ __('Telefone') }}:</strong> {{ formatPhone($member->phone) }}</div>
        </div>
        <div class="row">
            <div class="col-md-6"><strong>{{ __('Celular') }}:</strong> {{ formatPhone($member->cellphone) }}</div>
            <div class="col-md-6"><strong>{{ __('Comunidade') }}:</strong> {{ $member->community ? $member->community->name : 'Nenhuma' }}</div>
        </div>
    </div>

    <!-- Informações de Endereço -->
    <div class="card">
        <h3>{{ __('Informações de Endereço') }}</h3>
        <div class="row">
            <div class="col-md-6"><strong>{{ __('CEP') }}:</strong> {{ formatCep($member->cep) }}</div>
        </div>
        <div class="row">
            <div class="col-md-8"><strong>{{ __('Endereço') }}:</strong> {{ $member->address ?? '-' }}</div>
            <div class="col-md-4"><strong>{{ __('Número') }}:</strong> {{ $member->number ?? '-' }}</div>
        </div>
        <div class="row">
            <div class="col-md-8"><strong>{{ __('Complemento') }}:</strong> {{ $member->complement ?? '-' }}</div>
            <div class="col-md-4"><strong>{{ __('Bairro') }}:</strong> {{ $member->district ?? '-' }}</div>
        </div>
        <div class="row">
            <div class="col-md-8"><strong>{{ __('Cidade') }}:</strong> {{ $member->city ?? '-' }}</div>
            <div class="col-md-4"><strong>{{ __('Estado') }}:</strong> {{ $member->state ?? '-' }}</div>
        </div>
    </div>

    <!-- Informações de Filiação -->
    <div class="card">
        <h3>{{ __('Informações de Filiação') }}</h3>
        <div class="row">
            <div class="col-md-6"><strong>{{ __('Nome do Pai') }}:</strong> {{ $member->father_name ?? '-' }}</div>
            <div class="col-md-6"><strong>{{ __('Nome da Mãe') }}:</strong> {{ $member->mother_name ?? '-' }}</div>
        </div>
    </div>

    <!-- Contribuição Mensal -->
    <div class="card">
        <h3>{{ __('Contribuição Mensal') }}</h3>
        <div class="row">
            <div class="col-md-6"><strong>{{ __('Método de Envio') }}:</strong> {{ $member->delivery_method ? \App\Enums\DeliveryMethodEnum::tryFrom($member->delivery_method)->label() : '-' }}</div>
            <div class="col-md-6"><strong>{{ __('Método de Pagamento') }}:</strong> {{ $member->payment_method ? \App\Enums\PaymentMethodEnum::tryFrom($member->payment_method)->getLabels() : '-' }}</div>
        </div>
        <div class="row">
            <div class="col-md-6"><strong>{{ __('Dia do Mês') }}:</strong> {{ $member->due_day ?? '-' }}</div>
            <div class="col-md-6"><strong>{{ __('Valor') }}:</strong> {{ $member->amount ? 'R$ ' . number_format($member->amount, 2, ',', '.') : '-' }}</div>
        </div>
        <div class="row">           
            <div class="col-md-6"><strong>{{ __('Status') }}:</strong> {{ $member->is_active ? 'Ativo' : 'Inativo' }}</div>
            <div class="col-md-6"><strong>{{ __('Contribuinte') }}:</strong> {{ $member->is_contributor ? 'Sim' : 'Não' }}</div>
        </div>
    </div>

    <!-- Observações -->
    <div class="card">
        <h3>{{ __('Observações') }}</h3>
        <p>{{ $member->observations ?? '-' }}</p>
    </div>
</body>
</html>