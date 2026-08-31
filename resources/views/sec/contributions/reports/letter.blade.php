<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #333; margin: 30px; }
        .header { text-align: center; border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
        .content { line-height: 1.6; margin-bottom: 30px; }
        .box { background: #f9f9f9; padding: 15px; border-radius: 5px; border: 1px solid #eee; margin: 20px 0; }
        .footer { margin-top: 40px; text-align: center; font-size: 11px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $parochialUnion->name ?? 'União Paroquial' }}</h2>
        <p>Aviso de Contribuição Mensal</p>
    </div>

    <div class="content">
        <p>Prezado(a) membro <strong>{{ $member->name }}</strong>,</p>
        
        <p>Seguem os detalhes sobre a sua contribuição referente ao período de <strong>{{ $contribution->contribution_date->format('m/Y') }}</strong>:</p>

        <div class="box">
            <p><strong>Vencimento:</strong> {{ $contribution->contribution_date->format('d/m/Y') }}</p>
            <p><strong>Valor:</strong> R$ {{ number_format($contribution->amount, 2, ',', '.') }}</p>
            <p><strong>Forma de Pagamento:</strong> {{ $member->payment_method->label() ?? $member->payment_method }}</p>
        </div>

        <p>Se você optou por recebimento via Boleto Bancário, o documento de pagamento encontra-se anexado na página seguinte.</p>
    </div>

    <div class="footer">
        <p>Agradecemos a sua valiosa cooperação com a nossa comunidade!</p>
    </div>
</body>
</html>