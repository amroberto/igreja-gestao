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
                            @php
                            $method = is_object($contribution->payment_method)
                            ? strtolower($contribution->payment_method->value ?? $contribution->payment_method->name ??
                            '')
                            : strtolower($contribution->payment_method);

                            $pixPayload = $contribution->asaas_pix_payload ?? $contribution->asaas_pix_qr_code;
                            @endphp
                            <tr>
                                <td>{{ $contribution->created_at->format('d/m/Y') }}</td>
                                <td>{{ $contribution->community->name ?? '-' }}</td>
                                <td>{{ is_object($contribution->payment_method) &&
                                    method_exists($contribution->payment_method, 'label') ?
                                    $contribution->payment_method->label() : $contribution->payment_method }}</td>
                                <td>{{ $contribution->period }}</td>
                                <td>R$ {{ number_format($contribution->amount, 2, ',', '.') }}</td>
                                <td>
                                    @php
                                    $status = is_object($contribution->status) ?
                                    strtolower($contribution->status->value) : strtolower($contribution->status);
                                    @endphp
                                    @if ($status === 'confirmed' || $contribution->is_paid)
                                    <span class="badge bg-success">Pago</span>
                                    @elseif ($status === 'pending')
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                    @elseif ($status === 'failed')
                                    <span class="badge bg-info">Falhou</span>
                                    @elseif ($status === 'canceled')
                                    <span class="badge bg-danger">Cancelado</span>
                                    @else
                                    <span class="badge bg-secondary">{{ $contribution->status ?? 'Desconhecido'
                                        }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- Se o pagamento NÃO estiver confirmado/pago, exibe os botões correspondentes
                                    --}}
                                    @if ($status !== 'confirmed' && !$contribution->is_paid)

                                    {{-- Botão Boleto --}}
                                    @if (str_contains($method, 'boleto') || $contribution->asaas_boleto_url)
                                    @if($contribution->asaas_boleto_url)
                                    <a href="{{ $contribution->asaas_boleto_url }}" class="btn btn-sm btn-danger me-1"
                                        target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i> Visualizar Boleto
                                    </a>
                                    @endif
                                    @endif

                                    {{-- Botão PIX --}}
                                    @if (str_contains($method, 'pix'))
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                        data-bs-target="#pixModal{{ $contribution->id }}">
                                        <i class="bi bi-qr-code"></i> Visualizar PIX
                                    </button>

                                    <!-- Modal PIX individual -->
                                    <div class="modal fade" id="pixModal{{ $contribution->id }}" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Pagamento via PIX</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center py-4">
                                                    <p class="mb-3">Escaneie o QR Code abaixo com o aplicativo do seu
                                                        banco:</p>

                                                    <div class="d-flex justify-content-center mb-4">
                                                        @if(!empty($pixPayload))
                                                        @if(str_starts_with($pixPayload, 'data:image') ||
                                                        strlen($pixPayload) > 500)
                                                        <img src="{{ str_starts_with($pixPayload, 'data:') ? $pixPayload : 'data:image/png;base64,' . $pixPayload }}"
                                                            alt="QR Code PIX" style="max-width: 220px;"
                                                            class="img-fluid border p-2 rounded">
                                                        @else
                                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($pixPayload) }}"
                                                            alt="QR Code PIX"
                                                            style="max-width: 220px; width: 220px; height: 220px;"
                                                            class="img-fluid border p-2 rounded">
                                                        @endif
                                                        @else
                                                        <div class="alert alert-warning mb-0">Código PIX indisponível.
                                                        </div>
                                                        @endif
                                                    </div>

                                                    <div class="bg-light p-3 rounded">
                                                        <label for="pix-input-{{ $contribution->id }}"
                                                            class="form-label fw-bold">Código PIX (Copia e
                                                            Cola):</label>
                                                        <div class="input-group">
                                                            <input type="text" id="pix-input-{{ $contribution->id }}"
                                                                class="form-control text-center"
                                                                value="{{ $pixPayload }}" readonly>
                                                            <button class="btn btn-outline-primary" type="button"
                                                                onclick="copyPix('pix-input-{{ $contribution->id }}')">
                                                                <i class="bi bi-clipboard"></i> Copiar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer justify-content-center">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Fechar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center">
                        {{ $contributions->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyPix(inputId) {
    const pixInput = document.getElementById(inputId);
    pixInput.select();
    pixInput.setSelectionRange(0, 99999);
    
    navigator.clipboard.writeText(pixInput.value).then(() => {
        alert('Código PIX copiado com sucesso!');
    }).catch(() => {
        document.execCommand('copy');
        alert('Código PIX copiado!');
    });
}
</script>
@endpush