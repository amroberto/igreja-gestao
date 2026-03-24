<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ __('Relatório de Contribuições') }}</title>
    <style>
        body {
            font-family: Helvetica, sans-serif;
            font-size: 10px;
            margin: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

    </style>
</head>
<>
    <!-- Cabeçalho -->
    <div class="header">
        @if($parochialUnion->logo)
            <img src="{{ public_path('storage/' . $parochialUnion->logo) }}" alt="Logo {{ $parochialUnion->trade_name }}" style="max-height: 50px; margin-bottom: 8px;">
        @endif
        <h2 style="font-size: 14pt; margin: 5px 0;">{{ $parochialUnion->trade_name ?? 'União Paroquial' }}</h2>
        <p style="font-size: 9pt; margin: 0;">{{ $parochialUnion->corporate_name ?? 'Nome corporativo não informado' }}</p>
    </div>

    <h3 class="text-center">{{ __('Relatório de Contribuições por Período') }}</h3>
    <p class="text-center">{{ __('Período: ') }} {{ Carbon\Carbon::parse($request->start_date)->format('d/m/Y') }} {{ __('a') }} {{ Carbon\Carbon::parse($request->end_date)->format('d/m/Y') }}</p>

    <!-- Tabela de Contribuições -->
    <table>
        <thead>
            <tr>
                <th>{{ __('Doc.') }}</th>
                <th>{{ __('Data') }}</th>
                <th>{{ __('Ent. Financeira') }}</th>
                <th>{{ __('Comunidade') }}</th>
                <th>{{ __('Código') }}</th>
                <th>{{ __('Nome') }}</th>
                <th>{{ __('Período') }}</th>
                <th>{{ __('Meses Pagos') }}</th>
                <th class="text-right">{{ __('Valor') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalMesesPagos = 0;
            @endphp

            @forelse ($contributions as $contribution)
                @php
                    $mesesPagos = $contribution->getMonthsPaid();
                    $totalMesesPagos += $mesesPagos;
                @endphp
                <tr>
                    <td>{{ $contribution->id }}</td>
                    <td>{{ Carbon\Carbon::parse($contribution->contribution_date)->format('d/m/Y') }}</td>
                    <td>{{ $contribution->financial_entity->name }}</td>
                    <td>{{ $contribution->community->name }}</td>
                    <td>{{ $contribution->member_id }}</td>
                    <td>{{ $contribution->member->name }}</td>
                    <td>{{ $contribution->period }}</td>
                    <td class="text-center">{{ $mesesPagos }}</td>
                    <td class="text-right">{{ number_format($contribution->amount, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">{{ __('Nenhuma contribuição encontrada.') }}</td>
                </tr>
            @endforelse
        </tbody>

        @if ($contributions->count() > 0)
        <tfoot>
            <tr>
                <td colspan="7" class="text-right"><strong>{{ __('Total:') }}</strong></td>
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
