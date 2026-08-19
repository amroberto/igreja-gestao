<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Informativo Mensal</title>

    <style>
        @page {
            margin: 20px 25px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            color: #273238;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10px;
            line-height: 1.45;
            margin: 0;
        }

        .header-table,
        .content-table,
        .footer-table {
            border-collapse: collapse;
            width: 100%;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo-cell {
            text-align: center;
            width: 14%;
        }

        .logo {
            max-height: 65px;
            max-width: 65px;
        }

        .union-cell,
        .parish-cell {
            text-align: center;
            width: 36%;
        }

        .union-name {
            color: #174a3a;
            font-size: 13px;
            font-weight: bold;
            margin: 0 0 4px;
            text-transform: uppercase;
        }

        .parish-name {
            color: #174a3a;
            font-size: 11px;
            font-weight: bold;
            margin: 0 0 4px;
            text-transform: uppercase;
        }

        .small-text {
            font-size: 8px;
            line-height: 1.35;
            margin: 1px 0;
        }

        .divider {
            background: #c79a3b;
            height: 3px;
            margin: 12px 0;
        }

        .motto {
            background: #f4f7f3;
            border-left: 4px solid #174a3a;
            color: #174a3a;
            font-size: 11px;
            font-style: italic;
            margin: 12px 0;
            padding: 10px 14px;
            text-align: center;
        }

        .mailing-address {
            background: #ffffff;
            border: 1px solid #555555;
            font-size: 11px;
            margin: 12px 0;
            min-height: 85px;
            padding: 12px 16px;
            text-align: center;
        }

        .mailing-address p {
            line-height: 1.35;
            margin: 0;
        }

        .recipient-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 4px !important;
        }

        .section-title {
            background: #174a3a;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.3px;
            margin: 12px 0 0;
            padding: 7px 10px;
            text-transform: uppercase;
        }

        .content-table {
            margin-bottom: 12px;
        }

        .content-table td {
            vertical-align: top;
        }

        .activities-column {
            width: 62%;
        }

        .contributions-column {
            width: 38%;
        }

        .left-content {
            border: 1px solid #d9ded9;
            border-right: 0;
            min-height: 380px;
            padding: 12px;
        }

        .right-content {
            border: 1px solid #d9ded9;
            min-height: 380px;
            padding: 12px;
        }

        .activities {
            overflow: visible;
            word-wrap: break-word;
        }

        .activities p {
            margin: 0 0 8px;
        }

        .activities ul {
            margin: 5px 0 8px;
            padding-left: 18px;
        }

        .activities li {
            margin-bottom: 4px;
        }

        .activities img {
            height: auto;
            max-width: 100%;
        }

        .contributions-table {
            border-collapse: collapse;
            font-size: 8px;
            margin-top: 7px;
            width: 100%;
        }

        .contributions-table th {
            background: #e8efe9;
            color: #174a3a;
            font-weight: bold;
            padding: 6px 5px;
            text-align: left;
        }

        .contributions-table td {
            border-bottom: 1px solid #d9ded9;
            padding: 5px;
        }

        .contributions-table tr:nth-child(even) td {
            background: #fafcfa;
        }

        .value {
            text-align: right;
            white-space: nowrap;
        }

        .contribution-highlight {
            background: #fffaf0;
            border: 1px solid #d9b86e;
            color: #4b3a13;
            font-size: 10px;
            font-weight: bold;
            margin-top: 12px;
            padding: 9px 12px;
            text-align: center;
        }

        .instructions {
            border: 1px solid #d9ded9;
            padding: 10px 12px;
        }

        .instructions p {
            margin: 0 0 8px;
        }

        .instructions ul {
            margin: 5px 0 8px;
            padding-left: 18px;
        }

        .instructions li {
            margin-bottom: 4px;
        }

        .footer {
            border-top: 1px solid #d9ded9;
            color: #59645d;
            font-size: 8px;
            margin-top: 14px;
            padding-top: 8px;
        }

        .footer-table td:last-child {
            text-align: right;
        }
    </style>
</head>

<body>
    @php
    $logoDataUri = function (?string $logo): ?string {
    if (empty($logo)) {
    return null;
    }

    $logo = preg_replace('#^storage/#', '', ltrim($logo, '/'));
    $filePath = public_path('storage/' . $logo);

    if (!is_file($filePath)) {
    return null;
    }

    return 'data:' . mime_content_type($filePath)
    . ';base64,'
    . base64_encode(file_get_contents($filePath));
    };

    $unionLogoSrc = $logoDataUri($parochialUnion->logo);
    $parishLogoSrc = $logoDataUri($parish->logo);
    @endphp

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if ($unionLogoSrc)
                <img class="logo" src="{{ $unionLogoSrc }}" alt="Logo União Paroquial">
                @endif
            </td>

            <td class="union-cell">
                <p class="union-name">
                    {{ $parochialUnion->trade_name ?? $parochialUnion->name }}
                </p>

                <p class="small-text">{{ $parochialUnion->address }}</p>

                <p class="small-text">
                    Tel.: {{ formatPhone($parochialUnion->phone ?? formatPhone($parochialUnion->tel)) }}
                    | E-mail: {{ $parochialUnion->email }}
                </p>

                <p class="small-text">CNPJ: {{ formatCnpj($parochialUnion->cnpj) }}</p>
            </td>

            <td class="parish-cell">
                <p class="parish-name">{{ $parish->name }}</p>
                <p class="small-text">{{ $parish->address }}</p>

                <p class="small-text">
                    Tel.: {{ formatPhone($parish->phone ?? formatPhone($parish->tel)) }}
                    | E-mail: {{ $parish->email }}
                </p>

                @if ($parish->website)
                <p class="small-text">Site: {{ $parish->website }}</p>
                @endif
            </td>

            <td class="logo-cell">
                @if ($parishLogoSrc)
                <img class="logo" src="{{ $parishLogoSrc }}" alt="Logo Paróquia">
                @endif
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="motto">
        “{{ $config->motto }}”
    </div>

    <div class="mailing-address">
        <p class="recipient-name">
            <b>{{ $member->name }} -- {{ $member->id }}</b>
        </p>

        <p>
            {{ $member->address }}, {{ $member->number }}
            @if ($member->complement)
            - {{ $member->complement }}
            @endif
        </p>

        @if ($member->district)
        <p>{{ $member->district }}</p>
        @endif

        <p>
            <b>{{ formatCep($member->cep) }}</b> - {{ $member->city }} / {{ $member->state }}
        </p>
    </div>

    <table class="content-table">
        <tr>
            <td class="activities-column">
                <div class="section-title">Atividades da comunidade</div>

                <div class="left-content activities">
                    {!! $config->observations !!}
                </div>
            </td>

            <td>
                <div class="section-title">Últimas contribuições</div>

                <div class="right-content">
                    <table class="contributions-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Referência</th>
                                <th class="value">Valor</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($contributions as $contribution)
                            <tr>
                                <td>
                                    {{ $contribution->contribution_date->format('d/m/Y') }}
                                </td>

                                <td>{{ $contribution->period }}</td>

                                <td class="value">
                                    R$ {{ number_format($contribution->amount, 2, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" style="text-align: center;">
                                    Nenhuma contribuição encontrada.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="contribution-highlight">
        Contribuição referente a {{ $config->reference_date->format('m/Y') }}
    </div>

    <div class="section-title">Formas de contribuição</div>

    <div class="instructions">
        <p>
            Sua contribuição é livre e espontânea. Para alterar data ou valor,
            entre em contato com a União Paroquial ou com a secretaria da Comunidade.
        </p>

        <ul>
            <li>Utilizar o boleto no verso desta carta.</li>
            <li>Entregar a contribuição nos cultos ou na secretaria.</li>
            <li>Fazer PIX para a chave CNPJ 61.137.543/0001-40.</li>
            <li>
                Fazer transferência para Igreja Evangélica Luterana de SP,
                Banco Itaú (341), Agência 0057, Conta 66.066-4.
            </li>
        </ul>

        <p>
            <strong>AGRADECEMOS PELA SUA CONTRIBUIÇÃO REGULAR!</strong>
        </p>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>Agradecemos pela sua participação e colaboração.</td>
                <td>Ref.: {{ $config->reference_date->format('m/Y') }}</td>
            </tr>
        </table>
    </div>
</body>

</html>