<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Detalhes do Membro') }}</title>
    <style>
        @page {
            margin: 60px 30px 50px 30px; /* Margem para cabeçalho e rodapé */
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
            margin: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .header img {
            max-height: 50px;
            vertical-align: middle;
            margin-bottom: 5px;
        }
        .header .church-info {
            font-size: 8pt;
            color: #555;
            margin-top: 2px;
        }
        .footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
            font-size: 8pt;
            color: #555;
            border-top: 1px solid #ddd;
        }
        h1 {
            font-size: 14pt;
            color: #2c3e50;
            text-align: center;
            margin: 20px 0 15px 0;
        }
        h3 {
            font-size: 12pt;
            color: #34495e;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            padding: 15px;
            background-color: #fff;
            page-break-inside: avoid;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
            font-size: 9pt;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho (apenas na primeira página) -->
    <div class="header">
        @if($parochialUnion->logo)
            <img src="{{ public_path('storage/' . $parochialUnion->logo) }}" alt="Logo {{ $parochialUnion->trade_name }}" style="max-height: 50px; margin-bottom: 8px;">
        @endif
        <h2 style="font-size: 14pt; margin: 5px 0;">{{ $parochialUnion->trade_name ?? 'União Paroquial' }}</h2>
        <p style="font-size: 9pt; margin: 0;">{{ $parochialUnion->corporate_name ?? 'Nome corporativo não informado' }}</p>
    </div>

    <!-- Rodapé -->
    <div class="footer" id="footer">
        Gerado em {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} | Página <span class="page-number"></span> de <span class="total-pages"></span>
    </div>

    <!-- Título -->
    <h1>{{ __('Detalhes do Membro') }}</h1>

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
            <div class="col-md-6"><strong>{{ __('Gênero') }}:</strong> {{ $member->gender->label() ?? '-' }}</div>
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
            <div class="col-md-2"><strong>{{ __('CEP') }}:</strong> {{ formatCep($member->cep) }}</div>
        </div>
        <div class="row">
            <div class="col-md-8"><strong>{{ __('Endereço') }}:</strong> {{ $member->address ?? '-' }}</div>
            <div class="col-md-4"><strong>{{ __('Número') }}:</strong> {{ $member->number ?? '-' }}</div>
        </div>
        <div class="row">
            <div class="col-md-6"><strong>{{ __('Complemento') }}:</strong> {{ $member->complement ?? '-' }}</div>
            <div class="col-md-6"><strong>{{ __('Bairro') }}:</strong> {{ $member->district ?? '-' }}</div>
        </div>
        <div class="row">
            <div class="col-md-6"><strong>{{ __('Cidade') }}:</strong> {{ $member->city ?? '-' }}</div>
            <div class="col-md-6"><strong>{{ __('Estado') }}:</strong> {{ $member->state ?? '-' }}</div>
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
            <div class="col-md-6"><strong>{{ __('Método de Pagamento') }}:</strong> {{ $member->payment_method ? \App\Enums\PaymentMethodEnum::tryFrom($member->payment_method)->label() : '-' }}</div>
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

    <!-- Histórico de Contribuições (Opcional, comentado) -->
    <div class="card">
        <h3>{{ __('Histórico de Contribuições') }}</h3>
        @if($member->contributions->isEmpty())
            <p>{{ __('Nenhuma contribuição registrada.') }}</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Data') }}</th>
                        <th>{{ __('Comunidade') }}</th>
                        <th>{{ __('Período') }}</th>
                        <th>{{ __('Valor') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($member->contributions as $contribution)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($contribution->contribution_date)->format('d/m/Y') }}</td>
                            <td>{{ $contribution->community->name }}
                            <td>{{ $contribution->period ?? '-' }}</td>
                            <td>R$ {{ number_format($contribution->amount, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Script PHP para numeração de páginas -->
    <script type="text/php">
        if (isset($pdf)) {
            $x = 500;
            $y = 780;
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
            $size = 8;
            $color = [0.333, 0.333, 0.333];
            $pdf->page_text($x, $y, $text, $font, $size, $color);
        }
    </script>
</body>
</html>