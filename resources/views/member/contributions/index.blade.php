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
                                    @if ($contribution->asaas_pix_qr_code && $contribution->status->value === 'pending' && $contribution->payment_method->value === 'pix')
                                    <button class="btn btn-sm btn-success" onclick="showPixWindow('{{ $contribution->asaas_pix_qr_code }}', '{{ $contribution->asaas_pix_payload }}')">
                                        <i class="bi bi-qr-code"></i> Visualizar PIX
                                    </button>
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

@section('scripts')
<script>
function showPixWindow(qrCode, payload) {
    // Calcular posição para centralizar a janela
    const width = 400;
    const height = 600;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;
    const pixWindow = window.open('', '_blank', `width=${width},height=${height},left=${left},top=${top}`);
    pixWindow.document.write(`
        <html>
            <head>
                <title>QR Code PIX</title>
                <style>
                    body {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                        margin: 0;
                        background: #f0f0f0;
                        font-family: Arial, sans-serif;
                        text-align: center;
                    }
                    .container {
                        padding: 20px;
                        background: white;
                        border-radius: 8px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                        max-width: 360px;
                        width: 100%;
                    }
                    img {
                        max-width: 300px;
                        max-height: 300px;
                        margin-bottom: 20px;
                    }
                    .copy-paste {
                        margin-top: 10px;
                        padding: 10px;
                        background: #fff;
                        border: 1px solid #ccc;
                        border-radius: 5px;
                    }
                    .copy-paste input {
                        width: 100%;
                        padding: 8px;
                        margin-bottom: 10px;
                        font-size: 14px;
                        border: 1px solid #ccc;
                        border-radius: 4px;
                    }
                    .copy-paste button {
                        padding: 8px 16px;
                        background: #007bff;
                        color: #fff;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                    }
                    .copy-paste button:hover {
                        background: #0056b3;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h3>Contribuição via PIX</h3>
                    <img src="data:image/png;base64,${qrCode}" alt="QR Code PIX">
                    <div class="copy-paste">
                        <p><strong>Código PIX (Copia e Cola):</strong></p>
                        <input type="text" value="${payload}" readonly>
                        <button onclick="navigator.clipboard.writeText(this.previousElementSibling.value).then(() => alert('Código copiado!'))">Copiar</button>
                    </div>
                </div>
            </body>
        </html>
    `);
    pixWindow.document.close();
}
</script>
@endsection
@endsection
