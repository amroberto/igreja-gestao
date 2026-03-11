<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Contribuição</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 30px;
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #003087;
        }
        .header .logo img {
            max-width: 100px;
            height: auto;
            margin-bottom: 10px;
        }
        .header h2 {
            font-size: 18px;
            font-weight: bold;
            color: #003087;
            margin: 0 0 5px;
        }
        .header p {
            font-size: 10px;
            margin: 2px 0;
            color: #555;
        }
        .address {
            font-size: 10px;
            margin: 5px 0;
            color: #666;
        }
        .receipt-meta {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 5px;
        }
        .receipt-meta span {
            font-weight: bold;
            color: #003087;
        }
        .receipt-details {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #003087;
        }
        .receipt-details p {
            margin: 8px 0;
            font-size: 12px;
        }
        .receipt-details span {
            font-weight: bold;
            color: #003087;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .generated-by {
            text-align: right;
            font-size: 9px;
            color: #555;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($parochialUnion->logo)
                <img src="{{ public_path('storage/' . $parochialUnion->logo) }}" alt="Logo {{ $parochialUnion->trade_name }}" style="max-height: 50px; margin-bottom: 8px;">
            @endif
            <h2>{{ $parochialUnion->trade_name }}</h2>
            <p>{{ $parochialUnion->corporate_name }}</p>
            <p>CNPJ: {{ formatCnpj($parochialUnion->cnpj) }}</p>
            <p>Utilidade Pública Estadual - Decreto 50.416 de 25/09/1968</p>
            <div class="address">
                <p>
                    Sede: 
                    {{ $parochialUnion->address }}
                    {{ $parochialUnion->number ? ', nº ' . $parochialUnion->number : '' }}
                    {{ $parochialUnion->complement ? ' - ' . $parochialUnion->complement : '' }},
                    {{ $parochialUnion->district }}
                </p>
                <p>
                    {{ formatCep($parochialUnion->cep) }} - {{ $parochialUnion->city }} / {{ $parochialUnion->state }}
                </p>
            </div>
            <p>
                Tel.: {{ formatPhone($parochialUnion->phone) }}
                @if($parochialUnion->cellphone)
                    - WhatsApp: {{ formatPhone($parochialUnion->cellphone) }}
                @endif
            </p>
            <p>E-mail: {{ $parochialUnion->email }}</p>
            <p>Site: {{ $parochialUnion->website }}</p>
        </div>

        <div class="receipt-meta">
            <p><span>Recibo Nº:</span> {{ $receiptNumber }}</p>
            <p><span>São Paulo,</span> {{ $date }}</p>
        </div>

        <div class="receipt-details">
            <p>Recebemos em {{ $contribution->contribution_date->format('d/m/Y') }}, de <span>{{ $contribution->member->name }}</span></p>
            <p>a importância de R$ <span>{{ number_format($contribution->amount, 2, ',', '.') }} ({{ $valorPorExtenso }})</span></p>
            <p>proveniente de <span>Contribuição mensal no período de {{ $contribution->period }}</span></p>
        </div>

        <div class="footer">
            <p>Para mais informações, entre em contato com a secretaria.</p>
        </div>

        <div class="generated-by">
            <p>Gerado por: {{ $generatedBy }}</p>
        </div>
    </div>
</body>
</html>
