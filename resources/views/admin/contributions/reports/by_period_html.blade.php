@extends('adminlte::page')

@section('title', 'Relatório de Contribuições por Período')

@section('content_header')
<h1>Relatório de Contribuições - Todas as Comunidades</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">Relatório de Contribuições — Todas as Comunidades</h3>
            <div class="card-tools">
                <button onclick="window.print()" class="btn btn-light btn-sm">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <a href="{{ route('admin.contributions.report.by.period') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="text-center mb-4">
                @if ($parochialUnion && $parochialUnion->logo)
                <img src="{{ asset('storage/' . $parochialUnion->logo) }}" alt="Logo" style="max-height: 90px;">
                @endif
                <h2 class="mt-3">Relatório de Contribuições</h2>
                <h4>Todas as Comunidades</h4>
                <p>
                    Período: <strong>{{ \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') }}</strong>
                    a
                    <strong>{{ \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') }}</strong>
                </p>
                <p>Gerado por: <strong>{{ $generatedBy }}</strong> em {{ $date }}</p>
            </div>

            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>{{ __('Doc.') }}</th>
                        <th>{{ __('Data') }}</th>
                        <th>{{ __('Ent. Financeira') }}</th>
                        <th>{{ __('Comunidade') }}</th>
                        <th>{{ __('Cod. Membro') }}</th>
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

                @if ($contributions->isNotEmpty())
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td colspan="5" class="text-end">TOTAL GERAL</td>
                        <td class="text-end">R$ {{ number_format($contributions->sum('amount'), 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@stop