@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light py-3">
            <h5 class="card-title mb-0 font-weight-bold">
                Registrar Contribuição para <span>{{ $member->name ?? '' }} ({{ $member->cpf ?? '' }})</span>
            </h5>
        </div>
        <div class="card-body">
            <form id="contribution-form" action="{{ route('member.contributions.store') }}" method="POST">
                @csrf

                {{-- Campo oculto exigido pelo Controller --}}
                <input type="hidden" name="member_id" value="{{ $member->id }}">

                <div id="server-error" class="alert alert-danger contribution-hidden mb-3"></div>

                <h6 class="text-muted font-weight-bold mb-3">Detalhes da Contribuição</h6>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="community_id" class="form-label">Comunidade</label>
                        <select id="community_id" name="community_id" class="form-select" required>
                            @foreach($communities ?? [] as $community)
                                <option value="{{ $community->id }}" 
                                    {{ (isset($member->community_id) && $member->community_id == $community->id) ? 'selected' : '' }}>
                                    {{ $community->name }}
                                </option>
                            @endforeach
                        </select>
                        <span id="community-id-error" class="text-danger small contribution-hidden"></span>
                    </div>

                    <div class="col-md-6">
                        <label for="contribution_date" class="form-label">Data da Contribuição</label>
                        <input type="date" id="contribution_date" name="contribution_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                        <span id="contribution-date-error" class="text-danger small contribution-hidden"></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Período da Contribuição</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="period_type" id="period_type_month" value="current_month" checked>
                        <label class="form-check-label" for="period_type_month">Mês Atual</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="period_type" id="period_type_period" value="period">
                        <label class="form-check-label" for="period_type_period">Período</label>
                    </div>
                </div>

                {{-- Datas na mesma linha --}}
                <div class="row mb-3">
                    <div class="col-md-6" id="single-month-group">
                        <label for="start_date" id="start_date_label" class="form-label">Mês/Ano Inicial</label>
                        <input type="month" id="start_date" name="start_date" value="{{ date('Y-m') }}" class="form-control" required>
                        <span id="start-date-error" class="text-danger small contribution-hidden"></span>
                    </div>

                    <div class="col-md-6 contribution-hidden" id="end-date-group">
                        <label for="end_date" class="form-label">Mês/Ano Final</label>
                        <input type="month" id="end_date" name="end_date" class="form-control">
                        <span id="end-date-error" class="text-danger small contribution-hidden"></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Forma de Pagamento</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_method" id="pay_boleto" value="boleto_bancario" checked>
                        <label class="form-check-label" for="pay_boleto">Boleto</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_method" id="pay_pix" value="pix">
                        <label class="form-check-label" for="pay_pix">Pix</label>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="amount" class="form-label">Valor (R$)</label>
                        <input type="text" id="amount" name="amount" placeholder="0,00" class="form-control" required>
                        <span id="amount-error" class="text-danger small contribution-hidden"></span>
                    </div>

                    <div class="col-md-8">
                        <label for="description" class="form-label">Descrição</label>
                        <input type="text" id="description" name="description" value="Contribuição mensal - {{ $member->name ?? '' }}" class="form-control">
                        <span id="description-error" class="text-danger small contribution-hidden"></span>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" id="submit-button" class="btn btn-warning text-white px-5 font-weight-bold" style="background-color: #ff6600; border: none;">
                        Criar Contribuição
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal PIX -->
<div class="modal fade" id="pixModal" tabindex="-1" aria-labelledby="pixModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold" id="pixModalLabel">Pagamento via PIX</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted">Escaneie o QR Code abaixo com o aplicativo do seu banco:</p>
        
        {{-- QR Code Centralizado --}}
        <div class="mb-3 d-flex justify-content-center align-items-center">
            <img id="pix-qrcode-img" src="" alt="QR Code PIX" class="img-fluid" style="max-height: 250px;">
        </div>
        
        <div class="mb-3">
            <label for="pix-payload-input" class="form-label font-weight-bold">Ou use o Copia e Cola:</label>
            <div class="input-group">
                <input type="text" id="pix-payload-input" class="form-control" readonly>
                <button class="btn btn-outline-primary" type="button" id="copy-pix-btn">Copiar</button>
            </div>
        </div>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Concluir e Ir para a Listagem</button>
      </div>
    </div>
  </div>
</div>

<style>
    .contribution-hidden {
        display: none !important;
    }
</style>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Máscara do campo Valor
    if (typeof jQuery !== 'undefined' && $.fn.mask) {
        $('#amount').mask('000.000.000,00', { reverse: true });
    }

    // 2. Alternância do tipo de período (Mês Atual x Período)
    const periodRadios = document.querySelectorAll('input[name="period_type"]');
    const endDateGroup = document.getElementById('end-date-group');

    function togglePeriod() {
        const selected = document.querySelector('input[name="period_type"]:checked').value;
        if (selected === 'current_month') {
            endDateGroup.classList.add('contribution-hidden');
        } else {
            endDateGroup.classList.remove('contribution-hidden');
        }
    }

    periodRadios.forEach(radio => radio.addEventListener('change', togglePeriod));

    // 3. Botão Copiar código PIX
    const copyBtn = document.getElementById('copy-pix-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            const payloadInput = document.getElementById('pix-payload-input');
            payloadInput.select();
            document.execCommand('copy');
            alert('Código PIX copiado com sucesso!');
        });
    }

    // 4. Redirecionar para a index ao fechar o Modal do PIX
    const pixModalEl = document.getElementById('pixModal');
    if (pixModalEl) {
        pixModalEl.addEventListener('hidden.bs.modal', function () {
            window.location.href = "{{ route('member.contributions.index') }}";
        });
    }

    // 5. Envio do Formulário via AJAX
    const form = document.getElementById('contribution-form');
    const submitButton = document.getElementById('submit-button');
    const serverError = document.getElementById('server-error');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Reseta mensagens de erro anteriores
        serverError.classList.add('contribution-hidden');
        serverError.innerHTML = '';
        document.querySelectorAll('.text-danger.small').forEach(el => {
            el.textContent = '';
            el.classList.add('contribution-hidden');
        });

        submitButton.disabled = true;
        submitButton.textContent = 'Processando...';

        const formData = new FormData(form);

        // Converte o valor "1.500,00" -> "1500.00"
        const rawAmount = formData.get('amount');
        if (rawAmount) {
            const normalizedAmount = rawAmount.replace(/\./g, '').replace(',', '.');
            formData.set('amount', normalizedAmount);
        }

        // Converte datas "YYYY-MM" -> "YYYY-MM-01"
        const startDate = formData.get('start_date');
        if (startDate && startDate.length === 7) {
            formData.set('start_date', startDate + '-01');
        }

        const endDate = formData.get('end_date');
        if (endDate && endDate.length === 7) {
            formData.set('end_date', endDate + '-01');
        } else if (!endDate) {
            formData.delete('end_date');
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 422 && data.errors) {
                    let errorMessages = [];
                    for (const [field, messages] of Object.entries(data.errors)) {
                        const messageText = messages.join(' ');
                        errorMessages.push(`<b>${field}:</b> ${messageText}`);

                        const errorSpan = document.getElementById(`${field.replace(/_/g, '-')}-error`);
                        if (errorSpan) {
                            errorSpan.textContent = messageText;
                            errorSpan.classList.remove('contribution-hidden');
                        }
                    }
                    serverError.innerHTML = errorMessages.join('<br>');
                } else {
                    serverError.textContent = data.message || 'Erro ao processar a requisição.';
                }
                serverError.classList.remove('contribution-hidden');
                submitButton.disabled = false;
                submitButton.textContent = 'Criar Contribuição';
                return;
            }

            // BOLETO: Abre o PDF e redireciona para a index
            if (data.bankSlipUrl) {
                window.open(data.bankSlipUrl, '_blank');
                window.location.href = "{{ route('member.contributions.index') }}";
                return;
            }

            // PIX: Exibe o modal com QR Code e Payload
            if (data.pixQrCode || data.pixPayload) {
                const qrImg = document.getElementById('pix-qrcode-img');
                const payloadInput = document.getElementById('pix-payload-input');

                if (data.pixQrCode) {
                    qrImg.src = data.pixQrCode.startsWith('http') || data.pixQrCode.startsWith('data:') 
                        ? data.pixQrCode 
                        : `data:image/png;base64,${data.pixQrCode}`;
                }

                if (data.pixPayload) {
                    payloadInput.value = data.pixPayload;
                }

                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const pixModal = new bootstrap.Modal(pixModalEl);
                    pixModal.show();
                } else {
                    $('#pixModal').modal('show');
                }
                return;
            }

            // Fallback genérico
            window.location.href = data.redirect || "{{ route('member.contributions.index') }}";

        } catch (err) {
            serverError.textContent = err.message || 'Erro de conexão com o servidor.';
            serverError.classList.remove('contribution-hidden');
            submitButton.disabled = false;
            submitButton.textContent = 'Criar Contribuição';
        }
    });
});
</script>
@endsection