<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Contribuições por Comunidade</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #004080;
            padding-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #999;
            padding: 8px 10px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #f0f0f0;
            color: #004080;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .footer {
            position: fixed;
            bottom: 30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9.5pt;
            color: #555;
        }
    </style>
</head>
<body>

    <!-- Cabeçalho -->
    <div class="header">
        @if($parochialUnion && $parochialUnion->logo)
            <img src="{{ public_path('storage/' . $parochialUnion->logo) }}" 
                 alt="Logo" style="max-height: 60px; margin-bottom: 8px;">
        @endif
        <h2 style="margin: 5px 0;">{{ $parochialUnion->trade_name ?? 'União Paroquial' }}</h2>
        <p style="margin: 0; font-size: 9pt;">{{ $parochialUnion->corporate_name ?? '' }}</p>
    </div>

    <h3 class="text-center">Relatório de Contribuições por Comunidade</h3>
    <p class="text-center">
        <strong>{{ $community->name }}</strong><br>
        Período: {{ Carbon\Carbon::parse($request->start_date)->format('d/m/Y') }} 
        a {{ Carbon\Carbon::parse($request->end_date)->format('d/m/Y') }}
    </p>

    <!-- Tabela -->
    <table>
        <thead>
            <tr>
                <th>Doc.</th>
                <th>Data</th>
                <th>Ent. Financeira</th>
                <th>Código</th>
                <th>Nome</th>
                <th>Período</th>
                <th>Meses Pagos</th>
                <th class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @php $totalMesesPagos = 0; @endphp

            @forelse ($contributions as $contribution)
                @php
                    $mesesPagos = $contribution->getMonthsPaid();
                    $totalMesesPagos += $mesesPagos;
                @endphp
                <tr>
                    <td>{{ $contribution->id }}</td>
                    <td>{{ Carbon\Carbon::parse($contribution->contribution_date)->format('d/m/Y') }}</td>
                    <td>{{ $contribution->financial_entity->name ?? '—' }}</td>
                    <td>{{ $contribution->member_id }}</td>
                    <td>{{ $contribution->member->name }}</td>
                    <td>{{ $contribution->period ?? '—' }}</td>
                    <td class="text-center">{{ $mesesPagos }}</td>
                    <td class="text-right">{{ number_format($contribution->amount, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Nenhuma contribuição encontrada.</td>
                </tr>
            @endforelse
        </tbody>

        @if ($contributions->count() > 0)
        <tfoot>
            <tr class="table-secondary">
                <td colspan="6" class="text-right"><strong>Total:</strong></td>
                <td class="text-center"><strong>{{ $totalMesesPagos }}</strong></td>
                <td class="text-right"><strong>{{ number_format($contributions->sum('amount'), 2, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Rodapé -->
    <div class="footer">
        <p>{{ __('Relatório gerado por ') }} {{ $generatedBy }} {{ __('em') }} {{ $date }}</p>
    </div>

</body>
</html>