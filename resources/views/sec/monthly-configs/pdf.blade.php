<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Visualização de Configuração Mensal</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            margin: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .header .logo {
            width: 80px;
            height: auto;
        }

        .header .union-info, .header .parish-info {
            flex: 1;
            text-align: center;
            font-size: 10pt;
            margin: 0 10px;
        }

        .header .union-info h1, .header .parish-info h2 {
            font-size: 14pt;
            margin: 5px 0;
        }

        .header p {
            margin: 2px 0;
        }

        .motto {
            font-style: italic;
            text-align: center;
            margin: 20px 0;
            font-size: 12pt;
        }

        .content {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .content .observations {
            flex: 0 0 48%;
            font-size: 10pt;
        }

        .content .observations img {
            max-width: 100%;
            height: auto;
        }

        .content .contributions {
            flex: 0 0 48%;
        }

        .contributions-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        .contributions-table th,
        .contributions-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .contributions-table th {
            background-color: #f2f2f2;
        }

        .activities, .contact, .member-info, .instructions {
            margin: 20px 0;
            font-size: 10pt;
        }

        .activities ul, .instructions ul {
            list-style-type: disc;
            margin-left: 20px;
        }

        .qr-code-placeholder {
            width: 100px;
            height: 100px;
            background-color: #ddd;
            text-align: center;
            line-height: 100px;
            margin: 10px 0;
        }

        .footer {
            margin-top: 20px;
            font-size: 10pt;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho: Logos e dados em uma linha -->
        <div class="header">
            <img src="{{ $parochialUnion->logo ?? asset('images/uniao_paroquial_logo.png') }}" alt="Logo União Paroquial" class="logo">
            <div class="union-info">
                <h1>{{ $parochialUnion->name ?? 'IGREJA EVANGÉLICA LUTERANA DE SÃO PAULO' }}</h1>
                <p>{{ $parochialUnion->address ?? 'Rua Barão de Itapetininga, 255 - CJ 510, 01042-001, B. República - São Paulo/SP' }}</p>
                <p>Tel: {{ $parochialUnion->tel ?? '(11) 3257-8418' }} | E-mail: {{ $parochialUnion->email ?? 'upsp@uol.com.br' }}</p>
                <p>CNPJ: {{ $parochialUnion->cnpj ?? '61.137.543/0001-40' }}</p>
            </div>
            <div class="parish-info">
                <h2>{{ $parish->name ?? 'GUARULHOS' }}</h2>
                <p>{{ $parish->address ?? 'Rua Cidade Lion, 129 - Picanço, 07094-190 - Guarulhos/SP' }}</p>
                <p>Tel: {{ $parish->tel ?? '(11) 2459-1169' }} | E-mail: {{ $parish->email ?? 'paroguaru@luteranos.com.br' }}</p>
                <p>Site: {{ $parish->website ?? 'www.luteranos.com.br/guarulhos' }}</p>
            </div>
            <img src="{{ $parish->logo ?? asset('images/paroquia_logo.png') }}" alt="Logo Paróquia" class="logo">
        </div>

        <!-- Lema bíblico -->
        <div class="motto">
            "{{ $config->motto ?? 'Tu me farás ver os caminhos da vida. Sl 16.11' }}"
        </div>

        <!-- Conteúdo principal: Duas colunas -->
        <div class="content">
            <!-- Coluna esquerda: Observações -->
            <div class="observations">
                <strong>Observações</strong>
                <div>{!! $config->observations ?? '<p>No Salmo 16.11, a afirmação de que "Tu me farás ver os caminhos da vida" não só aponta para a direção que Deus nos dá, mas também para a promessa de que, nele, encontramos o verdadeiro propósito da nossa existência...</p>' !!}</div>
            </div>
            <!-- Coluna direita: Tabela de contribuições -->
            <div class="contributions">
                <strong>Últimas Contribuições</strong>
                <table class="contributions-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Referência</th>
                            <th>Valor R$</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contributions as $contribution)
                            <tr>
                                <td>{{ $contribution->date->format('d/m/Y') }}</td>
                                <td>{{ $contribution->reference }}</td>
                                <td>{{ number_format($contribution->value, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        @if (empty($contributions))
                            <tr>
                                <td>15/01/2025</td>
                                <td>01/2025</td>
                                <td>30,00</td>
                            </tr>
                            <tr>
                                <td>16/12/2024</td>
                                <td>12/2024</td>
                                <td>30,00</td>
                            </tr>
                            <tr>
                                <td>18/11/2024</td>
                                <td>11/2024</td>
                                <td>30,00</td>
                            </tr>
                            <tr>
                                <td>15/10/2024</td>
                                <td>10/2024</td>
                                <td>30,00</td>
                            </tr>
                            <tr>
                                <td>16/09/2024</td>
                                <td>09/2024</td>
                                <td>30,00</td>
                            </tr>
                            <tr>
                                <td>14/08/2024</td>
                                <td>08/2024</td>
                                <td>30,00</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Atividades da comunidade -->
        <div class="activities">
            <p><strong>ATIVIDADES QUE VÃO ACONTECER NA COMUNIDADE:</strong></p>
            <ul>
                <li>Retorno Culto Infantil: 02/02/2025, às 09h30.</li>
                <li>Almoço Jovem: 09/02/2025 - o almoço será servido após o culto.</li>
                <li>Culto dos Aniversariantes: 23/02/2025, às 09h30.</li>
            </ul>
            <p>Todas as pessoas que fazem aniversário no mês de fevereiro estão convidadas a participar do culto dos aniversariantes, para receberem uma bênção.</p>
        </div>

        <!-- Cultos gerais -->
        <div class="contact">
            <p><strong>CULTOS GERAIS:</strong> todos os domingos, às 9h30 - com Culto Infantil e transmissão pelo YOUTUBE Luteranos Guarulhos.</p>
            <p><strong>ESTAMOS SEMPRE À DISPOSIÇÃO! DEUS TE ABENÇOE!</strong></p>
            <p>Pastor Tiago Jaske - 11 99754 4941 | Pastor em treinamento Ivaír Strelow - 11 99558 9000 (ligação ou WhatsApp)</p>
        </div>

        <!-- Informações do membro -->
        <div class="member-info">
            <p><strong>Membro:</strong> {{ $member->name ?? 'Alexandre Martins Roberto' }}</p>
            <p><strong>CPF:</strong> {{ $member->cpf ?? '142.033.968-08' }}</p>
            <p><strong>Endereço:</strong> {{ $member->address ?? 'Rua Segundo-Tenente Gildo Zanim Pistolato, 83, 07115-006 - Guarulhos/SP' }}</p>
        </div>

        <!-- Instruções de contribuição -->
        <div class="instructions">
            <p>O boleto para a sua contribuição está no verso deste informativo. Os boletos da IELSP são registrados pela empresa de intermediação de pagamentos ASAAS Gestão Financeira.</p>
            <p>Sua contribuição é livre e espontânea, mas por exigência do Banco Central somos obrigados a colocar uma data de vencimento e um valor na emissão dos boletos.</p>
            <p>Para alterar a data ou o valor da sua contribuição, entre em contato com a União Paroquial ou a secretaria da Comunidade através dos e-mails e telefones acima, ou via WhatsApp: +55 11 2991-3477.</p>
            <p><strong>Formas de Contribuição:</strong></p>
            <ul>
                <li>Utilizar o boleto no verso desta carta, em qualquer banco, lotérica ou internet banking.</li>
                <li>Entregar a sua contribuição (ou utilizar cartão débito/crédito) nos cultos ou na secretaria da sua Comunidade.</li>
                <li>Fazer PIX para a chave CNPJ 61.137.543/0001-40.</li>
                <li>Fazer transferência ou DOC/TED para Igreja Evangélica Luterana de SP, Banco Itaú (341) - Ag 0057, C/C 66.066-4.</li>
                <li>Utilizar o intermediador de pagamentos ASAAS, para contribuir com cartão de crédito via link: <a href="https://www.asaas.com/c/026023069125">https://www.asaas.com/c/026023069125</a>.</li>
            </ul>
            <p>(Se for efetuado depósito na conta, enviar comprovante para e-mail ou WhatsApp 11 2991-3477)</p>
            <div class="qr-code-placeholder">QR Code PIX (Placeholder)</div>
            <p><em>"A contribuição proporcional é minha oferta de gratidão à igreja por tudo o que o Senhor me tem concedido."</em></p>
            <p><strong>AGRADECEMOS PELA SUA CONTRIBUIÇÃO REGULAR!</strong></p>
        </div>

        <!-- Rodapé -->
        <div class="footer">
            <p>Agradecemos pela sua participação e colaboração.</p>
            <p>Ref.: {{ $config->reference_date->format('m/Y') ?? 'Fevereiro/2025' }}</p>
        </div>
    </div>
</body>
</html>