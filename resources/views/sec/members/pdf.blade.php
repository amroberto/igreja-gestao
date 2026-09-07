<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Ficha Cadastral do Membro') }}</title>
    <style>
        @page {
            margin: 25px 20px 30px 20px;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8pt;
            color: #2b2b2b;
            line-height: 1.2;
            margin: 0;
        }

        /* Estrutura do Cabeçalho Estilo Recibo */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .logo-col {
            width: 22%;
            text-align: left;
        }

        .logo-img {
            max-height: 70px;
            max-width: 120px;
        }

        .info-col {
            width: 54%;
            text-align: center;
            font-size: 7pt;
            color: #333;
            line-height: 1.25;
        }

        .info-col h2 {
            font-size: 11pt;
            font-weight: bold;
            color: #1a252f;
            margin: 0 0 2px 0;
            text-transform: uppercase;
        }

        .info-col h3 {
            font-size: 8.5pt;
            font-weight: normal;
            color: #2c3e50;
            margin: 0 0 3px 0;
            border: none;
            padding: 0;
        }

        .info-line {
            margin: 0;
        }

        .badge-col {
            width: 24%;
            text-align: right;
            vertical-align: top !important;
        }

        .doc-box {
            border: 1px solid #2c3e50;
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 5px;
            text-align: center;
        }

        .doc-title {
            font-size: 7pt;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            border-bottom: 1px solid #dcdcdc;
            padding-bottom: 2px;
            margin-bottom: 3px;
        }

        .doc-number {
            font-size: 10pt;
            font-weight: bold;
            color: #c0392b;
        }

        .doc-date {
            font-size: 6.5pt;
            color: #555;
            margin-top: 2px;
        }

        /* Seções e Blocos com Largura Total */
        .section-box {
            border: 1px solid #dcdcdc;
            border-radius: 4px;
            margin-bottom: 6px;
            background-color: #fff;
            overflow: hidden;
            width: 100%;
        }

        .section-header {
            background-color: #2c3e50;
            color: #fff;
            font-size: 7.5pt;
            font-weight: bold;
            padding: 3px 6px;
            text-transform: uppercase;
        }

        .section-body {
            padding: 4px 6px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table td {
            border: none;
            padding: 2px 4px;
            font-size: 7.5pt;
            vertical-align: top;
        }

        .data-label {
            font-weight: bold;
            color: #444;
        }

        /* Tabela de Contribuições Quitadas */
        .contributions-table {
            width: 100%;
            border-collapse: collapse;
        }

        .contributions-table th {
            background-color: #f0f3f5;
            color: #333;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #dcdcdc;
            padding: 3px 5px;
            text-align: left;
        }

        .contributions-table td {
            border: 1px solid #e2e2e2;
            padding: 3px 5px;
            font-size: 7.5pt;
        }

        .badge-doc {
            font-weight: bold;
            color: #1a252f;
        }

        /* Rodapé Fixo */
        .footer {
            position: fixed;
            bottom: -15px;
            left: 0;
            right: 0;
            height: 15px;
            text-align: center;
            font-size: 6.5pt;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 2px;
        }
    </style>
</head>
<body>

    <!-- Cabeçalho Estilo Recibo -->
    <table class="header-table">
        <tr>
            <!-- Logo -->
            <td class="logo-col">
                @if(isset($parochialUnion) && $parochialUnion->logo)
                    <img src="{{ public_path('storage/' . $parochialUnion->logo) }}" class="logo-img" alt="Logo">
                @endif
            </td>

            <!-- Informações Institucionais Centralizadas -->
            <td class="info-col">
                <h2>{{ $parochialUnion->trade_name ?? 'União Paroquial' }}</h2>
                @if(!empty($parochialUnion->corporate_name))
                    <h3>{{ $parochialUnion->corporate_name }}</h3>
                @endif

                <p class="info-line">
                    @if(!empty($parochialUnion->cnpj)) <strong>CNPJ:</strong> {{ $parochialUnion->cnpj }} @endif
                    @if(!empty($parochialUnion->public_utility_decree)) | {{ $parochialUnion->public_utility_decree }} @endif
                </p>

                <p class="info-line">
                    @if(!empty($parochialUnion->address)) {{ $parochialUnion->address }}@endif
                    @if(!empty($parochialUnion->city)) - {{ $parochialUnion->city }}/{{ $parochialUnion->state ?? '' }}@endif
                </p>

                <p class="info-line">
                    @if(!empty($parochialUnion->phone)) <strong>Tel:</strong> {{ $parochialUnion->phone }} @endif
                    @if(!empty($parochialUnion->email)) | <strong>E-mail:</strong> {{ $parochialUnion->email }} @endif
                </p>
            </td>

            <!-- Caixa de Identificação do Documento -->
            <td class="badge-col">
                <div class="doc-box">
                    <div class="doc-title">{{ __('Ficha do Membro') }}</div>
                    <div class="doc-number">Nº {{ str_pad($member->id, 6, '0', STR_PAD_LEFT) }}</div>
                    <div class="doc-date">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- 1. Informações Pessoais -->
    <div class="section-box">
        <div class="section-header">{{ __('Informações Pessoais') }}</div>
        <div class="section-body">
            <table class="data-table">
                <tr>
                    <td style="width: 50%;"><span class="data-label">{{ __('Nome') }}:</span> {{ $member->name }}</td>
                    <td style="width: 25%;"><span class="data-label">{{ __('CPF') }}:</span> {{ formatCpf($member->cpf) }}</td>
                    <td style="width: 25%;"><span class="data-label">{{ __('RG') }}:</span> {{ formatRg($member->rg) ?? '-' }}</td>
                </tr>
                <tr>
                    <td><span class="data-label">{{ __('Data de Nascimento') }}:</span> {{ $member->birth_date ? \Carbon\Carbon::parse($member->birth_date)->format('d/m/Y') : '-' }}</td>
                    <td><span class="data-label">{{ __('Gênero') }}:</span> {{ is_object($member->gender) ? $member->gender->label() : ($member->gender ?? '-') }}</td>
                    <td><span class="data-label">{{ __('Estado Civil') }}:</span> {{ $member->marital_status ? ucfirst($member->marital_status) : '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 2. Contato e Filiação -->
    <div class="section-box">
        <div class="section-header">{{ __('Contato & Filiação') }}</div>
        <div class="section-body">
            <table class="data-table">
                <tr>
                    <td style="width: 35%;"><span class="data-label">{{ __('E-mail') }}:</span> {{ $member->email ?? '-' }}</td>
                    <td style="width: 20%;"><span class="data-label">{{ __('Telefone') }}:</span> {{ formatPhone($member->phone) }}</td>
                    <td style="width: 20%;"><span class="data-label">{{ __('Celular') }}:</span> {{ formatPhone($member->cellphone) }}</td>
                    <td style="width: 25%;"><span class="data-label">{{ __('Comunidade') }}:</span> {{ $member->community ? $member->community->name : 'Nenhuma' }}</td>
                </tr>
                <tr>
                    <td colspan="2"><span class="data-label">{{ __('Nome do Pai') }}:</span> {{ $member->father_name ?? '-' }}</td>
                    <td colspan="2"><span class="data-label">{{ __('Nome da Mãe') }}:</span> {{ $member->mother_name ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 3. Endereço -->
    <div class="section-box">
        <div class="section-header">{{ __('Endereço') }}</div>
        <div class="section-body">
            <table class="data-table">
                <tr>
                    <td style="width: 50%;"><span class="data-label">{{ __('Logradouro') }}:</span> {{ $member->address ?? '-' }}, {{ $member->number ?? 'S/N' }}</td>
                    <td style="width: 25%;"><span class="data-label">{{ __('Complemento') }}:</span> {{ $member->complement ?? '-' }}</td>
                    <td style="width: 25%;"><span class="data-label">{{ __('Bairro') }}:</span> {{ $member->district ?? '-' }}</td>
                </tr>
                <tr>
                    <td colspan="2"><span class="data-label">{{ __('Cidade/UF') }}:</span> {{ $member->city ?? '-' }}/{{ $member->state ?? '-' }}</td>
                    <td><span class="data-label">{{ __('CEP') }}:</span> {{ formatCep($member->cep) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 4. Configurações de Contribuição -->
    <div class="section-box">
        <div class="section-header">{{ __('Configuração de Contribuição') }}</div>
        <div class="section-body">
            <table class="data-table">
                <tr>
                    <td style="width: 30%;">
                        <span class="data-label">{{ __('Método Envio') }}:</span> 
                        {{ is_object($member->delivery_method) ? $member->delivery_method->label() : (\App\Enums\DeliveryMethodEnum::tryFrom($member->delivery_method)?->label() ?? '-') }}
                    </td>
                    <td style="width: 30%;">
                        <span class="data-label">{{ __('Método Pagamento') }}:</span> 
                        {{ is_object($member->payment_method) ? $member->payment_method->label() : (\App\Enums\PaymentMethodEnum::tryFrom($member->payment_method)?->label() ?? '-') }}
                    </td>
                    <td style="width: 20%;"><span class="data-label">{{ __('Dia Venc.') }}:</span> Dia {{ $member->due_day ?? '-' }}</td>
                    <td style="width: 20%;"><span class="data-label">{{ __('Valor Base') }}:</span> {{ $member->amount ? 'R$ ' . number_format($member->amount, 2, ',', '.') : '-' }}</td>
                </tr>
                <tr>
                    <td colspan="2"><span class="data-label">{{ __('Status') }}:</span> {{ $member->is_active ? 'Ativo' : 'Inativo' }}</td>
                    <td colspan="2"><span class="data-label">{{ __('Contribuinte') }}:</span> {{ $member->is_contributor ? 'Sim' : 'Não' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 5. Observações (exibido apenas se preenchido) -->
    @if(!empty($member->observations))
    <div class="section-box">
        <div class="section-header">{{ __('Observações') }}</div>
        <div class="section-body" style="font-size: 7.5pt; padding: 4px 6px;">
            {{ $member->observations }}
        </div>
    </div>
    @endif

    <!-- 6. Contribuições do Membro (Quitadas/Lançadas) -->
    @php
        $confirmedList = $member->contributions->filter(fn($c) => $c->is_paid || $c->status === 'confirmed')->sortByDesc('created_at')->take(10);
    @endphp

    <div class="section-box">
        <div class="section-header" style="background-color: #27ae60;">{{ __('Últimas Contribuições Quitadas / Lançadas') }}</div>
        <div class="section-body" style="padding: 3px;">
            @if($confirmedList->isEmpty())
                <p style="font-size: 7.5pt; color: #777; margin: 0; text-align: center; padding: 5px;">{{ __('Nenhuma contribuição quitada registrada.') }}</p>
            @else
                <table class="contributions-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">{{ __('Nº Doc') }}</th>
                            <th style="width: 20%;">{{ __('Data Pagto') }}</th>
                            <th style="width: 45%;">{{ __('Referência / Período') }}</th>
                            <th style="width: 20%; text-align: right;">{{ __('Valor') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($confirmedList as $contribution)
                        <tr>
                            <td class="badge-doc">#{{ str_pad($contribution->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ \Carbon\Carbon::parse($contribution->contribution_date)->format('d/m/Y') }}</td>
                            <td>{{ $contribution->period ?? $contribution->description ?? '-' }}</td>
                            <td style="text-align: right; font-weight: bold;">R$ {{ number_format($contribution->amount, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        {{ $parochialUnion->trade_name ?? 'União Paroquial' }} | {{ __('Ficha de Membro emitida via sistema') }}
    </div>

</body>
</html>