@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Minhas Contribuições') }}</div>
                <div class="card-body">
                    @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <div class="card-header mb-3">
                        <a href="{{ route('member.contributions.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> {{ __('Registrar Nova Contribuição') }}
                        </a>
                    </div>

                    @if ($contributions->isEmpty())
                    <p>{{ __('Nenhuma contribuição registrada.') }}</p>
                    @else
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Comunidade</th>
                                <th>Forma de Pagamento</th>
                                <th>Período</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contributions as $contribution)
                            <tr>
                                <td>{{ $contribution->created_at->format('d/m/Y') }}</td>
                                <td>{{ $contribution->community->name }}</td>
                                <td>{{ $contribution->payment_method->label() }}</td>
                                <td>{{ $contribution->period }}</td>
                                <td>R$ {{ number_format($contribution->amount, 2, ',', '.') }}</td>
                                <td>
                                    @php
                                    $status = strtolower($contribution->status->value);
                                    @endphp
                                    @if ($status === 'confirmed')
                                    <span class="badge bg-success">Pago</span>
                                    @elseif ($status === 'pending')
                                    <span class="badge bg-warning">Pendente</span>
                                    @elseif ($status === 'failed')
                                    <span class="badge bg-info">Falhou</span>
                                    @elseif ($status === 'canceled')
                                    <span class="badge bg-danger">Cancelado</span>
                                    @else
                                    <span class="badge bg-secondary">{{ $contribution->status ?? 'Desconhecido' }}</span>
                                    @endif
                                </td>
                                <td>
                                @if ($contribution->asaas_boleto_url && $contribution->status->value === 'pending' && $contribution->payment_method->value === 'boleto_bancario')
                                    <a href="{{ $contribution->asaas_boleto_url }}" class="btn btn-sm btn-danger" target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i> Visualizar Boleto
                                    </a>
                                @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        {{ $contributions->links() }}
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
