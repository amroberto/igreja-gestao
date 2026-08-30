<div class="text-center py-2">
    <p class="mb-3">Escaneie o QR Code abaixo com o aplicativo do seu banco:</p>

    @if(!empty($pixCode))
        <div class="d-flex justify-content-center mb-4">
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(220)->generate($pixCode) !!}
        </div>

        <div class="bg-light p-3 rounded">
            <label for="modal-pix-input" class="form-label fw-bold">Código PIX (Copia e Cola):</label>
            <div class="input-group">
                <input type="text" id="modal-pix-input" class="form-control text-center" value="{{ $pixCode }}" readonly>
                <button class="btn btn-outline-primary" type="button" onclick="
                    const input = document.getElementById('modal-pix-input');
                    input.select();
                    navigator.clipboard.writeText(input.value).then(() => alert('Código PIX copiado!'));
                ">
                    <i class="bi bi-clipboard"></i> Copiar
                </button>
            </div>
        </div>
    @else
        <div class="alert alert-warning mb-0">
            Código PIX indisponível para esta contribuição.
        </div>
    @endif
</div>