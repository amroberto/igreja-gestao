@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light fw-bold text-dark d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-hand-holding-heart"></i> Minhas Contribuições</span>
                    <a href="{{ route('member.contributions.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Nova Contribuição
                    </a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if ($contributions->isEmpty())
                    <p class="text-muted text-center py-4">Nenhuma contribuição registrada.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Vencimento</th>
                                    <th>Comunidade</th>
                                    <th>Forma</th>
                                    <th>Período</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($contributions as $contribution)
                                @php
                                $status = is_object($contribution->status)
                                ? strtolower($contribution->status->value)
                                : strtolower($contribution->status);

                                $isPaid = $status === 'confirmed' || $contribution->is_paid;
                                $dueDate = $contribution->due_date ? \Carbon\Carbon::parse($contribution->due_date) : \Carbon\Carbon::parse($contribution->contribution_date);
                                $isOverdue = !$isPaid && $dueDate->isPast() && !$dueDate->isToday();

                                // Identifica se a forma escolhida no cadastro/edição foi PIX ou BOLETO
                                $methodVal = is_object($contribution->payment_method)
                                    ? strtolower($contribution->payment_method->value ?? '')
                                    : strtolower($contribution->payment_method ?? '');
                                
                                $isPix = str_contains($methodVal, 'pix');
                                @endphp
                                <tr>
                                    <td>{{ $dueDate->format('d/m/Y') }}</td>
                                    <td>{{ $contribution->community->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $isPix ? 'bg-success' : 'bg-danger' }}">
                                            <i class="bi {{ $isPix ? 'bi-qr-code' : 'bi-file-earmark-pdf' }}"></i>
                                            {{ is_object($contribution->payment_method) && method_exists($contribution->payment_method, 'label') ? $contribution->payment_method->label() : ($isPix ? 'Pix' : 'Boleto') }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($contribution->type ?? 'mensal') }} ({{ $contribution->period }})</td>
                                    <td><strong>R$ {{ number_format($contribution->amount, 2, ',', '.') }}</strong></td>
                                    <td>
                                        @if ($isPaid)
                                        <span class="badge bg-success">Pago</span>
                                        @elseif ($isOverdue)
                                        <span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> Vencido</span>
                                        @elseif ($status === 'pending')
                                        <span class="badge bg-warning text-dark">Pendente</span>
                                        @else
                                        <span class="badge bg-secondary">{{ $contribution->status ?? 'Desconhecido' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($isPaid)
                                        <span class="text-muted small">Pago em {{ $contribution->paid_at ? $contribution->paid_at->format('d/m/Y') : '-' }}</span>
                                        @elseif ($isOverdue)
                                        {{-- COBRANÇA VENCIDA: Exibe alerta e opção de editar/reemitir --}}
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <span class="text-danger small font-weight-bold">
                                                <i class="bi bi-info-circle"></i> Vencido. Reemita para pagar:
                                            </span>
                                            <a href="{{ route('member.contributions.edit', $contribution->id) }}" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-pencil"></i> Editar e Reemitir
                                            </a>
                                        </div>
                                        @else
                                        {{-- COBRANÇA PENDENTE: Exibe APENAS a forma cadastrada + Editar --}}
                                        <div class="d-inline-flex gap-1">
                                            @if ($isPix)
                                                {{-- Mostra APENAS o botão do PIX --}}
                                                <button type="button" class="btn btn-sm btn-success" onclick="openPixPopup('{{ route('member.contributions.pix', $contribution->id) }}')">
                                                    <i class="bi bi-qr-code"></i> Pagar com Pix
                                                </button>
                                            @else
                                                {{-- Mostra APENAS o botão do BOLETO --}}
                                                <a href="{{ route('member.contributions.boleto', $contribution->id) }}" target="_blank" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-file-earmark-pdf"></i> Pagar com Boleto
                                                </a>
                                            @endif

                                            {{-- Botão Editar --}}
                                            <a href="{{ route('member.contributions.edit', $contribution->id) }}" class="btn btn-sm btn-outline-primary" title="Editar dados ou alterar forma de pagamento">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $contributions->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openPixPopup(url) {
    const width = 450;
    const height = 600;

    const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
    const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;

    const screenWidth = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    const screenHeight = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    const systemZoom = screenWidth / window.screen.availWidth;
    const left = (screenWidth - width) / 2 / systemZoom + dualScreenLeft;
    const top = (screenHeight - height) / 2 / systemZoom + dualScreenTop;

    const popup = window.open(
        url,
        'PagamentoPIX',
        `scrollbars=yes, width=${width / systemZoom}, height=${height / systemZoom}, top=${top}, left=${left}`
    );

    if (window.focus && popup) {
        popup.focus();
    }
}
</script>
@endsection