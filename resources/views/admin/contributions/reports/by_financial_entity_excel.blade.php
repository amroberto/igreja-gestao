<table>
    <thead>
        <tr>
            <th>Data</th>
            <th>Código</th>
            <th>Nome</th>
            <th>Comunidade</th>
            <th>Ent. Financeira</th>
            <th>Meses Pagos</th>
            <th>Forma de Pagamento</th>
            <th>Valor</th>
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
            <td>{{ \Carbon\Carbon::parse($contribution->contribution_date)->format('d/m/Y') }}</td>
            <td>{{ $contribution->member->id ?? '' }}</td>
            <td>{{ $contribution->member->first_name ?? '' }} {{ $contribution->member->last_name ?? '' }}</td>
            <td>{{ $contribution->member->community->name ?? '—' }}</td>
            <td>{{ ucfirst($contribution->financial_entity->name ?? '') }}</td>
            <td>{{ $mesesPagos }}</td>
            <td>{{ $contribution->payment_method }}</td>
            <td>{{ number_format($contribution->amount, 2, '.', '') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7"><strong>TOTAL GERAL</strong></td>
            <td><strong>{{ number_format($contributions->sum('amount'), 2, '.', '') }}</strong></td>
        </tr>
    </tfoot>
</table>