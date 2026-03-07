<xaiArtifact artifact_id="e57e9c6c-141a-444d-a11e-d6fbdce8922e" artifact_version_id="3e072d40-73b8-4b9b-9226-0166415e308a" title="_contribution_entry.blade.php" contentType="text/blade">

<div class="mb-3 contribution-row card card-outline card-primary">
    <div class="card-body">
        {{-- Primeira Linha: Campos principais --}}
        <div class="mb-3 row align-items-end">
            {{-- Código do Membro --}}
            <div class="col-md-2 form-group">
                <label for="member_id_{{ $index }}">Código <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                    </div>
                    <input type="number" name="contributions[{{ $index }}][member_id]" id="member_id_{{ $index }}"
                           class="form-control member-id-input" placeholder="Código" required>
                </div>
            </div>

            {{-- Nome do Membro --}}
            <div class="col-md-3 form-group">
                <label for="member_name_{{ $index }}">Nome do Membro</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                    <input type="text" id="member_name_{{ $index }}" class="form-control member-name-display"
                           placeholder="Nome do membro" readonly>
                </div>
            </div>

            {{-- Data do Lançamento --}}
            <div class="col-md-2 form-group">
                <label for="contribution_date_{{ $index }}">Data Lançamento <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                    </div>
                    <input type="date" name="contributions[{{ $index }}][contribution_date]" id="contribution_date_{{ $index }}"
                           class="form-control" required>
                </div>
            </div>

            {{-- Tipo de Contribuição --}}
            <div class="col-md-2 form-group">
                <label for="type_{{ $index }}">Tipo <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-list-alt"></i></span>
                    </div>
                    <select name="contributions[{{ $index }}][type]" id="type_{{ $index }}"
                            class="form-control contribution-type" required>
                        <option value="mensal">Mensal</option>
                        <option value="periodo">Período</option>
                    </select>
                </div>
            </div>

            {{-- Valor --}}
            <div class="col-md-2 form-group">
                <label for="amount_{{ $index }}">Valor <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                    </div>
                    <input type="number" name="contributions[{{ $index }}][amount]" id="amount_{{ $index }}"
                           class="form-control" placeholder="0.00" min="0" step="0.01" required>
                </div>
            </div>

            {{-- Botão Remover --}}
            <div class="col-md-1 form-group">
                <button type="button" class="btn btn-danger btn-block remove-entry-btn" title="Remover linha">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>

        {{-- Segunda Linha: Campos condicionais --}}
        <div class="row align-items-end conditional-fields">
            {{-- Mês Inicial --}}
            <div class="col-md-3 form-group start-date-group">
                <label for="start_date_{{ $index }}">Mês Inicial <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                    </div>
                    <input type="month" name="contributions[{{ $index }}][start_date]" id="start_date_{{ $index }}"
                           class="form-control" required>
                </div>
            </div>

            {{-- Mês Final --}}
            <div class="col-md-3 form-group end-date-group" style="display: none;">
                <label for="end_date_{{ $index }}">Mês Final</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                    </div>
                    <input type="month" name="contributions[{{ $index }}][end_date]" id="end_date_{{ $index }}"
                           class="form-control">
                </div>
            </div>

            {{-- Forma de Pagamento --}}
            <div class="col-md-3 form-group">
                <label for="payment_method_{{ $index }}">Forma de Pagamento <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                    </div>
                    <select name="contributions[{{ $index }}][payment_method]" id="payment_method_{{ $index }}"
                            class="form-control" required>
                        @foreach (\App\Enums\PaymentMethodEnum::cases() as $method)
                            <option value="{{ $method->value }}">{{ $method->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>