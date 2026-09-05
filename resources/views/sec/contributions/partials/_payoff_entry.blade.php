<div class="payoff-row card card-outline card-success mb-3">
    <div class="card-body">
        <input type="hidden" name="payoffs[{{ $index }}][contribution_id]" class="contribution-id-input">
        <input type="hidden" name="payoffs[{{ $index }}][start_date]" class="start-date-hidden-input">

        {{-- Linha 1: Documento, Membro e Dados Originais --}}
        <div class="row align-items-end mb-3">
            {{-- Número do Documento --}}
            <div class="col-md-2 form-group">
                <label>Nº Documento <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                    </div>
                    <input type="number" class="form-control document-number-input" placeholder="Ex: 353" required>
                </div>
            </div>

            {{-- Nome do Membro --}}
            <div class="col-md-4 form-group">
                <label>Membro</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                    <input type="text" class="form-control member-display" placeholder="Informe o número do documento..." readonly>
                </div>
            </div>

            {{-- Valor (Editável na Baixa) --}}
            <div class="col-md-2 form-group">
                <label>Valor Pago (R$) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                    </div>
                    <input type="number" name="payoffs[{{ $index }}][amount]" class="form-control amount-input" step="0.01" min="0.01" placeholder="0.00" required>
                </div>
            </div>

            {{-- Mês Inicial (Apenas visualização) --}}
            <div class="col-md-3 form-group">
                <label>Mês Inicial</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                    </div>
                    <input type="month" class="form-control start-date-display" disabled readonly>
                </div>
            </div>

            {{-- Botão Remover --}}
            <div class="col-md-1 form-group">
                <button type="button" class="btn btn-danger btn-block remove-entry-btn" title="Remover linha">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>

        {{-- Linha 2: Informações de Baixa --}}
        <div class="row align-items-end">
            {{-- Tipo de Contribuição --}}
            <div class="col-md-3 form-group">
                <label>Tipo <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-list-alt"></i></span>
                    </div>
                    <select name="payoffs[{{ $index }}][type]" class="form-control payoff-type" required>
                        <option value="mensal" selected>Mensal</option>
                        <option value="periodo">Período</option>
                    </select>
                </div>
            </div>

            {{-- Mês Final (Ativado se for Período) --}}
            <div class="col-md-3 form-group end-date-container" style="display: none;">
                <label>Mês Final <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                    </div>
                    <input type="month" name="payoffs[{{ $index }}][end_date]" class="form-control end-date-input">
                </div>
            </div>

            {{-- Data do Pagamento --}}
            <div class="col-md-3 form-group">
                <label>Data de Pagamento <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                    </div>
                    <input type="date" name="payoffs[{{ $index }}][paid_at]" class="form-control paid-at-input" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            {{-- Forma de Pagamento --}}
            <div class="col-md-3 form-group">
                <label>Forma de Pagamento <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                    </div>
                    <select name="payoffs[{{ $index }}][payment_method]" class="form-control payment-method-input" required>
                        @foreach (\App\Enums\PaymentMethodEnum::cases() as $method)
                            <option value="{{ $method->value }}">{{ $method->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>