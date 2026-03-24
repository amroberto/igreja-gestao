@extends('adminlte::page')

@section('title', 'Relatório por Entidade Financeira')

@section('content_header')
    <h1>Relatório de Contribuições - {{ $financialEntity->name }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">Relatório de Contribuições — {{ $financialEntity->name }}</h3>
            <div class="card-tools">
                <button onclick="window.print()" class="btn btn-light btn-sm">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <a href="{{ route('admin.contributions.report-by-financial-entity') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="text-center mb-4">
                @if ($parochialUnion && $parochialUnion->logo)
                    <img src="{{ asset('storage/' . $parochialUnion->logo) }}" alt="Logo" style="max-height: 90px;">
                @endif
                <h2>Relatório de Contribuições</h2>
                <h4>{{ $financialEntity->name }}</h4>
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
                        <th>Data</th>
                        <th>Membro</th>
                        <th>Comunidade</th>
                        <th>Tipo</th>
                        <th>Forma de Pagamento</th>
                        <th class="text-end">Valor (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contributions as $contribution)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($contribution->contribution_date)->format('d/m/Y') }}</td>
                            <td>{{ $contribution->member->first_name ?? '' }} {{ $contribution->member->last_name ?? '' }}</td>
                            <td>{{ $contribution->member->community->name ?? '—' }}</td>
                            <td>{{ ucfirst($contribution->financial_entity->name) }}</td>
                            <td>{{ $contribution->payment_method }}</td>
                            <td class="text-end">R$ {{ number_format($contribution->amount, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4">Nenhuma contribuição encontrada.</td></tr>
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