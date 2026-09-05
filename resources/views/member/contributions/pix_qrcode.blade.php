<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento via PIX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">

    <div class="card shadow border-0 text-center w-100" style="max-width: 420px;">
        <div class="card-header bg-success text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-qr-code"></i> Pagamento via PIX</h5>
        </div>
        <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
            <p class="text-muted mb-3">Escaneie o QR Code abaixo com o aplicativo do seu banco:</p>

            @if(!empty($pixCode))
                <div class="d-flex justify-content-center mb-4">
                    @if(str_starts_with($pixCode, 'data:image') || strlen($pixCode) > 500)
                        <img src="{{ str_starts_with($pixCode, 'data:') ? $pixCode : 'data:image/png;base64,' . $pixCode }}" 
                             alt="QR Code PIX" 
                             style="max-width: 220px;" 
                             class="img-fluid border p-2 rounded bg-white shadow-sm">
                    @else
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($pixCode) }}" 
                             alt="QR Code PIX" 
                             style="max-width: 220px; width: 220px; height: 220px;" 
                             class="img-fluid border p-2 rounded bg-white shadow-sm">
                    @endif
                </div>

                <div class="bg-light p-3 rounded text-start w-100 border">
                    <label for="modal-pix-input" class="form-label fw-bold small text-muted">Código PIX (Copia e Cola):</label>
                    <div class="input-group">
                        <input type="text" id="modal-pix-input" class="form-control form-control-sm text-center" value="{{ $pixCode }}" readonly>
                        <button class="btn btn-outline-primary btn-sm" type="button" onclick="copyPixCode()">
                            <i class="bi bi-clipboard"></i> Copiar
                        </button>
                    </div>
                </div>
            @else
                <div class="alert alert-warning mb-0 w-100">
                    Código PIX indisponível para esta contribuição.
                </div>
            @endif
        </div>
        <div class="card-footer bg-light text-center py-2">
            <button type="button" class="btn btn-secondary btn-sm px-4" onclick="window.close()">Fechar Janela</button>
        </div>
    </div>

    <script>
    function copyPixCode() {
        const input = document.getElementById('modal-pix-input');
        input.select();
        navigator.clipboard.writeText(input.value).then(() => {
            alert('Código PIX copiado com sucesso!');
        });
    }
    </script>
</body>
</html>