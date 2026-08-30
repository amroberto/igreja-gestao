<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Informativo Mensal</title>
    <style>
        @page {
            margin: 5px 35px;
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

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            font-style: italic;
            margin: 10px 0;
            padding: 10px 14px;
            text-align: center;
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
            vertical-align: middle;
        }

        .logo {
            max-height: 60px !important;
            max-width: 60px !important;
            width: auto !important;
            height: auto !important;
            display: block;
            margin: 0 auto;
        }

        .union-cell,
        .parish-cell {
            text-align: left;
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
            font-size: 10px;
            height: 16px;
            line-height: 16px;
            font-style: italic;
            margin: 10px 0;
            padding: 8px 10px;
            text-align: center;
        }

        .mailing-address {
            background: #ffffff;
            font-size: 10px;
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
            text-align: center;
            text-transform: uppercase;
        }

        .content-table {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .content-table td {
            vertical-align: top;
        }

        .activities-column {
            width: 62%;
            padding-right: 10px;
        }

        .contributions-column {
            width: 38%;
        }

        /* Container da Imagem sem esticar */
        .left-content {
            border-right: 0;
            height: 325px;
            min-height: 325px;
            padding: 0;
            text-align: center;
            vertical-align: middle;
        }

        .right-content {
            border: 1px solid #d9ded9;
            height: 325px;
            min-height: 325px;
            padding: 8px;
        }

        .activities {
            overflow: hidden;
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

        /* Imagem centralizada sem distorção lateral */
        .left-content.activities .card-image-pdf {
            width: auto !important;
            height: auto !important;
            max-width: 100% !important;
            max-height: 330px !important;
            display: inline-block;
            margin: 0 auto;
            border: 1px solid #e1e8e2;
            border-radius: 4px;
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

        .doc-highlight {
            background: #f4f7f3;
            border: 1px solid #174a3a;
            color: #174a3a;
            font-size: 9.5px;
            margin: 10px 0;
            padding: 6px 10px;
            text-align: center;
        }

        .instructions-container {
            background: #ffffff;
            border: 1px solid #d9ded9;
            margin-top: 10px;
            padding: 8px;
            page-break-inside: avoid;
        }

        .instructions-container td {
            vertical-align: middle;
        }

        .instructions p {
            margin: 0 0 6px;
        }

        .instructions ul {
            margin: 4px 0 8px;
            padding-left: 16px;
        }

        .instructions li {
            line-height: 1.4;
            margin-bottom: 6px;
        }

        .bank-details {
            background: #f8faf8;
            border-left: 3px solid #174a3a;
            font-size: 8.5px;
            margin-top: 4px;
            padding: 6px 10px;
        }

        /* Card do Pix / QR Code */
        .qrcode-card {
            background: #f8faf8;
            border: 1px solid #c0d1c3;
            border-radius: 8px;
            margin-left: 10px;
            padding: 10px 8px 10px;
            text-align: center;
        }

        .qrcode-title {
            color: #174a3a;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .qrcode-subtitle {
            color: #555555;
            font-size: 8px;
            margin-bottom: 8px;
        }

        .qrcode-card img.qr-img {
            border: 2px solid #ffffff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: block;
            height: 105px;
            margin: 0 auto 6px;
            width: 105px;
        }

        .qrcode-badge {
            background: #ffffff;
            /* Fundo branco para não gastar toner e dar contraste */
            border: 1px solid #174a3a;
            border-radius: 4px;
            color: #174a3a;
            /* Texto escuro */
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.3px;
            padding: 4px 6px;
            text-transform: uppercase;
            text-align: center;
            white-space: nowrap;
        }

        /* Ícone escuro/preto para contraste em P&B */
        .pix-logo-icon {
            display: inline-block;
            font-size: 9px;
            color: #000000;
            /* Preto puro imprime perfeitamente em impressora laser */
            font-weight: bold;
            vertical-align: middle;
            margin-right: 3px;
        }

        .qrcode-badge span {
            display: inline-block;
            vertical-align: middle;
        }

        .qrcode-badge span {
            display: inline-block;
            vertical-align: middle;
        }

        .pix-badge-icon {
            width: 11px !important;
            height: 11px !important;
            display: inline-block !important;
            vertical-align: middle !important;
            margin-right: 4px !important;
        }

        .qrcode-badge span {
            display: inline-block !important;
            vertical-align: middle !important;
        }

        .thanks-text {
            color: #174a3a;
            font-size: 9px;
            font-weight: bold;
            margin-top: 8px !important;
        }

        .footer {
            border-top: 1px solid #d9ded9;
            color: #59645d;
            font-size: 8px;
            margin-top: 14px;
            padding-top: 8px;
            page-break-inside: avoid;
        }

        .footer-table td:last-child {
            text-align: right;
        }
    </style>
</head>

<body>

    <div class="title">
        IGREJA EVANGÉLICA LUTERANA DE SÃO JOÃO
    </div>
    @php
    // Helper robusto para carregar imagens locais em Base64 no Dompdf
    $logoDataUri = function (?string $path): ?string {
    if (empty($path)) {
    return null;
    }

    if (str_starts_with($path, 'data:image')) {
    return $path;
    }

    $cleanPath = ltrim($path, '/');
    $cleanPath = preg_replace('#^storage/#', '', $cleanPath);

    $fullPath = storage_path('app/public/' . $cleanPath);

    if (!is_file($fullPath)) {
    $fullPath = public_path('storage/' . $cleanPath);
    }

    if (!is_file($fullPath)) {
    $fullPath = public_path($cleanPath);
    }

    if (!is_file($fullPath)) {
    return null;
    }

    $mime = mime_content_type($fullPath) ?: 'image/png';
    return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
    };

    $unionLogoSrc = $logoDataUri($parochialUnion->logo ?? null);
    $parishLogoSrc = $logoDataUri($parish->logo ?? null);

    $cardImageSrc = null;
    if (
    isset($config->content_type) &&
    $config->content_type === 'image' &&
    !empty($config->image_path)
    ) {
    $cardImageSrc = $logoDataUri($config->image_path);
    }
    @endphp

    <!-- CABEÇALHO -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if ($unionLogoSrc)
                <img class="logo" src="{{ $unionLogoSrc }}" alt="Logo União Paroquial">
                @endif
            </td>
            <td class="union-cell">
                <p class="union-name">
                    {{ $parochialUnion->trade_name ?? ($parochialUnion->name ?? 'UNIÃO PAROQUIAL') }}
                </p>
                <p class="small-text">{{ $parochialUnion->address ?? '' }}, {{ $parochialUnion->number ?? '' }} - {{
                    $parochialUnion->complement ?? '' }}</p>
                <p class="small-text">
                    {{ function_exists('formatCep') ? formatCep($parochialUnion->cep ?? '') : ($parochialUnion->cep ??
                    '') }} /
                    {{ $parochialUnion->district ?? '' }} -
                    {{ $parochialUnion->city ?? '' }}/{{ $parochialUnion->state ?? '' }}
                </p>
                <p class="small-text">
                    Tel.: {{ function_exists('formatPhone') ? formatPhone($parochialUnion->phone ??
                    ($parochialUnion->tel ?? '')) : ($parochialUnion->phone ?? '') }}
                    | E-mail: {{ $parochialUnion->email ?? '' }}
                </p>
                <p class="small-text">CNPJ: {{ function_exists('formatCnpj') ? formatCnpj($parochialUnion->cnpj ?? '') :
                    ($parochialUnion->cnpj ?? '') }}</p>
            </td>
            <td class="parish-cell">
                <p class="parish-name">{{ $parish->name ?? 'PARÓQUIA' }}</p>
                <p class="small-text">{{ $parish->address ?? '' }}, {{ $parish->number ?? '' }} - {{ $parish->complement
                    ?? '' }}</p>
                <p class="small-text">
                    {{ function_exists('formatCep') ? formatCep($parish->cep ?? '') : ($parish->cep ?? '') }} -
                    {{ $parish->district ?? '' }} -
                    {{ $parish->city ?? '' }}/{{ $parish->state ?? '' }}
                </p>
                <p class="small-text">
                    Tel.: {{ function_exists('formatPhone') ? formatPhone($parish->phone ?? '') : ($parish->phone ?? '')
                    }}
                    | E-mail: {{ $parish->email ?? '' }}
                </p>
                @if (!empty($parish->website))
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

    <!-- LEMA / VERSÍCULO -->
    <div class="motto">
        “{{ $config->motto ?? 'Tu me farás ver os caminhos da vida. Sl 16.11' }}”
    </div>

    <!-- DESTINATÁRIO -->
    <div class="mailing-address">
        <p class="recipient-name">
            {{ $member->name ?? 'Nome do Membro / Contribuinte' }}
        </p>
        <p>
            {{ $member->address ?? '' }}, {{ $member->number ?? '' }}
        </p>
        <p>
            <b>{{ function_exists('formatCep') ? formatCep($member->cep ?? '') : ($member->cep ?? '') }}</b> - {{
            $member->city ?? '' }} / {{ $member->state ?? '' }}
        </p>
    </div>

    <!-- CONTEÚDO PRINCIPAL E CONTRIBUIÇÕES -->
    <table class="content-table">
        <tr>
            <td class="activities-column">
                <div class="left-content activities">
                    @if ($cardImageSrc)
                    <img src="{{ $cardImageSrc }}" class="card-image-pdf" alt="Informativo / Evento">
                    @else
                    <div style="padding: 8px; text-align: left;">
                        {!! !empty($config->observations) ? $config->observations : 'A contribuição proporcional é minha
                        oferta de gratidão à igreja.' !!}
                    </div>
                    @endif
                </div>
            </td>
            <td class="contributions-column">
                <div class="section-title" style="margin-top: 0;">Últimas contribuições</div>
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
                            @forelse ($contributions ?? [] as $contribution)
                            <tr>
                                <td>
                                    {{ isset($contribution->contribution_date) ?
                                    $contribution->contribution_date->format('d/m/Y') : '' }}
                                </td>
                                <td>{{ $contribution->period ?? '' }}</td>
                                <td class="value">
                                    R$ {{ number_format($contribution->amount ?? 0, 2, ',', '.') }}
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

    <!-- IDENTIFICAÇÃO DO DOCUMENTO -->
    <div class="doc-highlight">
        Nº Documento: <strong>{{ $documentNumber ?? '000000' }}</strong> |
        Referência: <strong>{{ isset($config->reference_date) ? $config->reference_date->format('m/Y') : date('m/Y')
            }}</strong> |
        Cód. Membro: <strong>{{ str_pad($member->id ?? 0, 6, '0', STR_PAD_LEFT) }}</strong>
    </div>

    <div class="section-title">Formas de contribuição</div>

    <!-- INSTRUÇÕES E QR CODE -->
    <div class="instructions-container">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 68%;" class="instructions">
                    <p>Sua contribuição é livre e espontânea. Para alterar datas ou valores, entre em contato com a
                        secretaria.</p>

                    <ul>
                        <li>Entregar a contribuição presencialmente nos cultos ou na secretaria.</li>
                        <li>
                            <strong>Transferência Bancária / PIX:</strong>
                            <div class="bank-details">
                                <strong>PIX (Chave CNPJ):</strong> 12.345.678/0001-66<br>
                                <strong>Banco Itaú</strong> | Agência: 1111 | C/C: 11111-1<br>
                                <strong>Favorecido:</strong> Igreja Evangélica Luterana de São João
                            </div>
                        </li>
                    </ul>

                    <p class="thanks-text">
                        AGRADECEMOS PELA SUA CONTRIBUIÇÃO REGULAR!
                    </p>
                </td>
                <td style="width: 32%;">
                    @if (isset($qrCodeBase64))
                    <div class="qrcode-card">
                        <div class="qrcode-title">Contribua com Pix</div>
                        <div class="qrcode-subtitle">Aponte a câmera do seu banco</div>

                        <img src="{{ $qrCodeBase64 }}" class="qr-img" alt="QR Code Pix">

                        <div class="qrcode-badge">
                            <span class="pix-logo-icon">❖</span>
                            <span>{{ !empty($parochialUnion->pix_static_enabled) ? 'ESCANEAR PARA CONTRIBUIR' : 'PIX -
                                QR CODE'
                                }}</span>
                        </div>
                    </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- RODAPÉ -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>Agradecemos pela sua participação e colaboração. | Cod:{{ str_pad($member->id ?? 0, 6, '0',
                    STR_PAD_LEFT) }} -- Doc:{{ $documentNumber ?? '000000' }}</td>
                <td>Ref.: {{ isset($config->reference_date) ? $config->reference_date->format('m/Y') : date('m/Y') }}
                </td>
            </tr>
        </table>
    </div>
</body>

</html>