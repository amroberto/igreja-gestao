@extends('layouts.app')

@section('title', 'Registrar Contribuição')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Registrar Contribuição para <b><i>{{ $member->name }} ({{ formatCpf($member->cpf) }})</i></b></h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif
                    <div id="server-error" class="alert alert-danger contribution-hidden" role="alert"></div>

                    <form id="contribution-form" method="POST" action="{{ route('member.contributions.store') }}">
                        @csrf
                        <input type="hidden" name="member_id" value="{{ $member->id }}">

                        <div class="row mb-4">
                            <h5 class="mb-3">Detalhes da Contribuição</h5>
                            <div class="col-md-6 mb-3">
                                <label for="community_id" class="form-label">Comunidade</label>
                                <select id="community_id" name="community_id" class="form-select" required>
                                    <option value="">Selecione uma Comunidade</option>
                                    @foreach($communities as $community)
                                    <option value="{{ $community->id }}" {{ old('community_id', $member->community_id ?? '') == $community->id ? 'selected' : '' }}>
                                        {{ $community->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <div id="community-error" class="text-danger small contribution-hidden"></div>
                                <x-input-error :messages="$errors->get('community_id')" class="text-danger small" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="contribution_date" class="form-label">Data da Contribuição</label>
                                <input type="date" id="contribution_date" name="contribution_date" class="form-control" value="{{ old('contribution_date', date('Y-m-d')) }}" required>
                                <div id="contribution-date-error" class="text-danger small contribution-hidden"></div>
                                <x-input-error :messages="$errors->get('contribution_date')" class="text-danger small" />
                            </div>
                        </div>

                        <div class="row mb-4">
                            <h5 class="mb-3">Período da Contribuição</h5>
                            <div class="col-md-12">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="period_type" id="current_month" value="current_month" {{ old('period_type', 'current_month') == 'current_month' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="current_month">Mês Atual</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="period_type" id="period" value="period" {{ old('period_type') == 'period' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="period">Período</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Mês/Ano Inicial</label>
                                <input type="month" id="start_date" name="start_date" class="form-control" value="{{ old('start_date', date('Y-m')) }}" required>
                                <div id="start-date-error" class="text-danger small contribution-hidden"></div>
                                <x-input-error :messages="$errors->get('start_date')" class="text-danger small" />
                            </div>
                            <div class="col-md-6 mb-3 contribution-hidden" id="end-date-field">
                                <label for="end_date" class="form-label">Mês/Ano Final</label>
                                <input type="month" id="end_date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                                <div id="end-date-error" class="text-danger small contribution-hidden"></div>
                                <x-input-error :messages="$errors->get('end_date')" class="text-danger small" />
                            </div>
                        </div>

                        <div class="row mb-4">
                            <h5 class="mb-3">Forma de Pagamento</h5>
                            <div class="col-md-12">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_method" id="boleto" value="boleto_bancario" {{ old('payment_method', 'boleto_bancario') == 'boleto_bancario' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="boleto">Boleto</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pix" value="pix" {{ old('payment_method') == 'pix' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pix">Pix</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="creditcard" {{ old('payment_method') == 'creditcard' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="credit_card">Cartão de Crédito</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="amount" class="form-label">Valor (R$)</label>
                                <input type="text" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" required>
                                <div id="amount-error" class="text-danger small contribution-hidden"></div>
                                <x-input-error :messages="$errors->get('amount')" class="text-danger small" />
                            </div>
                            <div class="col-md-8">
                                <label for="description" class="form-label">Descrição</label>
                                <input type="text" name="description" id="description" class="form-control" value="{{ old('description', 'Contribuição mensal - ' . $member->name) }}">
                                <div id="description-error" class="text-danger small contribution-hidden"></div>
                                <x-input-error :messages="$errors->get('description')" class="text-danger small" />
                            </div>
                        </div>

                        <div id="credit-card-fields" class="row mb-4 contribution-hidden">
                            <h5 class="mb-3">Detalhes do Cartão</h5>
                            <div class="col-md-6 mb-3">
                                <label for="creditCard_number" class="form-label">Número do Cartão</label>
                                <input type="text" id="creditCard_number" name="creditCard[number]" class="form-control" placeholder="1234 5678 9012 3456" disabled>
                                <div id="card-number-error" class="text-danger small contribution-hidden"></div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="creditCard_expiryMonth" class="form-label">Mês de Validade</label>
                                <input type="text" id="creditCard_expiryMonth" name="creditCard[expiryMonth]" class="form-control" placeholder="MM" disabled>
                                <div id="card-expiry-month-error" class="text-danger small contribution-hidden"></div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="creditCard_expiryYear" class="form-label">Ano de Validade</label>
                                <input type="text" id="creditCard_expiryYear" name="creditCard[expiryYear]" class="form-control" placeholder="AAAA" disabled>
                                <div id="card-expiry-year-error" class="text-danger small contribution-hidden"></div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="creditCard_ccv" class="form-label">CVC</label>
                                <input type="text" id="creditCard_ccv" name="creditCard[ccv]" class="form-control" placeholder="123" disabled>
                                <div id="card-cvc-error" class="text-danger small contribution-hidden"></div>
                            </div>
                            <div class="col-md-9 mb-3">
                                <label for="creditCard_holderName" class="form-label">Nome no Cartão</label>
                                <input type="text" id="creditCard_holderName" name="creditCard[holderName]" class="form-control" value="{{ $member->name }}" disabled>
                                <div id="card-holder-name-error" class="text-danger small contribution-hidden"></div>
                            </div>
                            <!-- Campos ocultos para holderInfo -->
                            <input type="hidden" name="creditCard[holderInfo][name]" value="{{ $member->name }}">
                            <input type="hidden" name="creditCard[holderInfo][email]" value="{{ $member->email }}">
                            <input type="hidden" name="creditCard[holderInfo][cpfCnpj]" value="{{ $member->cpf }}">
                            <input type="hidden" name="creditCard[holderInfo][postalCode]" value="{{ $member->cep }}">
                            <input type="hidden" name="creditCard[holderInfo][addressNumber]" value="{{ $member->number }}">
                            <input type="hidden" name="creditCard[holderInfo][phone]" value="{{ $member->phone }}">
                            <input type="hidden" name="creditCard[holderInfo][mobilePhone]" value="{{ $member->cellphone }}">
                        </div>

                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <button type="submit" class="btn btn-primary" id="submit-button">
                                Criar Contribuição
                            </button>
                        </div>
                    </form>

                    <!-- Modal para perguntar sobre salvar o cartão -->
                    <div class="modal fade" id="saveCardModal" tabindex="-1" aria-labelledby="saveCardModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="saveCardModalLabel">Salvar Cartão</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Deseja salvar este cartão para facilitar futuras contribuições?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Não</button>
                                    <button type="button" class="btn btn-primary" id="saveCardButton">Sim</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        // Função para validar o número do cartão com o algoritmo Luhn
        function validateLuhn(cardNumber) {
            cardNumber = cardNumber.replace(/\s/g, '');
            let sum = 0;
            let isEven = false;
            for (let i = cardNumber.length - 1; i >= 0; i--) {
                let digit = parseInt(cardNumber[i], 10);
                if (isEven) {
                    digit *= 2;
                    if (digit > 9) digit -= 9;
                }
                sum += digit;
                isEven = !isEven;
            }
            return sum % 10 === 0;
        }

        // Aplicar máscaras
        if (typeof jQuery !== 'undefined' && $.fn.mask) {
            $('#amount').mask('000.000.000,00', { reverse: true });
            $('#creditCard_number').mask('0000 0000 0000 0000');
            $('#creditCard_expiryMonth').mask('00');
            $('#creditCard_expiryYear').mask('0000');
            $('#creditCard_ccv').mask('000');
        } else {
            console.error('jQuery ou jQuery Mask Plugin não carregados.');
        }

        // Alternar campos de cartão de crédito
        const paymentMethodInputs = document.querySelectorAll('input[name="payment_method"]');
        const creditCardFields = document.getElementById('credit-card-fields');
        const creditCardInputs = [
            document.getElementById('creditCard_number'),
            document.getElementById('creditCard_expiryMonth'),
            document.getElementById('creditCard_expiryYear'),
            document.getElementById('creditCard_ccv'),
            document.getElementById('creditCard_holderName'),
        ];
        paymentMethodInputs.forEach(input => {
            input.addEventListener('change', function () {
                const isCreditCard = this.value === 'creditcard';
                creditCardFields.classList.toggle('contribution-hidden', !isCreditCard);
                creditCardInputs.forEach(input => {
                    input.disabled = !isCreditCard;
                    if (!isCreditCard) {
                        input.value = input.id === 'creditCard_holderName' ? '{{ $member->name }}' : '';
                    }
                });
                // Limpar mensagens de erro
                document.getElementById('card-number-error').classList.add('contribution-hidden');
                document.getElementById('card-expiry-month-error').classList.add('contribution-hidden');
                document.getElementById('card-expiry-year-error').classList.add('contribution-hidden');
                document.getElementById('card-cvc-error').classList.add('contribution-hidden');
                document.getElementById('card-holder-name-error').classList.add('contribution-hidden');
            });
        });

        // Alternar campo end_date com base no period_type
        const periodTypeInputs = document.querySelectorAll('input[name="period_type"]');
        const endDateField = document.getElementById('end-date-field');
        periodTypeInputs.forEach(input => {
            input.addEventListener('change', function () {
                endDateField.classList.toggle('contribution-hidden', this.value === 'current_month');
                if (this.value === 'current_month') {
                    document.getElementById('end_date').value = '';
                    document.getElementById('end-date-error').classList.add('contribution-hidden');
                }
            });
        });

        // Validações client-side
        const amountInput = document.getElementById('amount');
        const amountError = document.getElementById('amount-error');
        const contributionDateInput = document.getElementById('contribution_date');
        const contributionDateError = document.getElementById('contribution-date-error');
        const startDateInput = document.getElementById('start_date');
        const startDateError = document.getElementById('start-date-error');
        const endDateInput = document.getElementById('end_date');
        const endDateError = document.getElementById('end-date-error');
        const descriptionInput = document.getElementById('description');
        const descriptionError = document.getElementById('description-error');
        const communityInput = document.getElementById('community_id');
        const communityError = document.getElementById('community-error');
        const cardNumberInput = document.getElementById('creditCard_number');
        const cardNumberError = document.getElementById('card-number-error');
        const cardExpiryMonthInput = document.getElementById('creditCard_expiryMonth');
        const cardExpiryMonthError = document.getElementById('card-expiry-month-error');
        const cardExpiryYearInput = document.getElementById('creditCard_expiryYear');
        const cardExpiryYearError = document.getElementById('card-expiry-year-error');
        const cardCvcInput = document.getElementById('creditCard_ccv');
        const cardCvcError = document.getElementById('card-cvc-error');
        const cardHolderNameInput = document.getElementById('creditCard_holderName');
        const cardHolderNameError = document.getElementById('card-holder-name-error');
        const serverError = document.getElementById('server-error');
        const submitButton = document.getElementById('submit-button');

        function validateForm() {
            let isValid = true;
            serverError.classList.add('contribution-hidden');

            // Validar valor
            const amountValue = amountInput.value.replace(/\./g, '').replace(',', '.');
            if (isNaN(amountValue) || parseFloat(amountValue) <= 0) {
                amountInput.classList.add('border-red-500');
                amountInput.classList.remove('border-green-500');
                amountError.classList.remove('contribution-hidden');
                amountError.textContent = 'O valor deve ser maior que R$ 0,00.';
                isValid = false;
            } else {
                amountInput.classList.remove('border-red-500');
                amountInput.classList.add('border-green-500');
                amountError.classList.add('contribution-hidden');
            }

            // Validar data da contribuição
            const today = new Date().toISOString().split('T')[0];
            if (!contributionDateInput.value || contributionDateInput.value < today) {
                contributionDateInput.classList.add('border-red-500');
                contributionDateInput.classList.remove('border-green-500');
                contributionDateError.classList.remove('contribution-hidden');
                contributionDateError.textContent = 'A data da contribuição deve ser hoje ou futura.';
                isValid = false;
            } else {
                contributionDateInput.classList.remove('border-red-500');
                contributionDateInput.classList.add('border-green-500');
                contributionDateError.classList.add('contribution-hidden');
            }

            // Validar data inicial (mês/ano)
            if (!startDateInput.value) {
                startDateInput.classList.add('border-red-500');
                startDateInput.classList.remove('border-green-500');
                startDateError.classList.remove('contribution-hidden');
                startDateError.textContent = 'O mês/ano inicial é obrigatório.';
                isValid = false;
            } else if (!/^\d{4}-\d{2}$/.test(startDateInput.value)) {
                startDateInput.classList.add('border-red-500');
                startDateInput.classList.remove('border-green-500');
                startDateError.classList.remove('contribution-hidden');
                startDateError.textContent = 'Formato de mês/ano inválido (ex.: YYYY-MM).';
                isValid = false;
            } else {
                startDateInput.classList.remove('border-red-500');
                startDateInput.classList.add('border-green-500');
                startDateError.classList.add('contribution-hidden');
            }

            // Validar data final (obrigatória apenas para período)
            const periodType = document.querySelector('input[name="period_type"]:checked').value;
            if (periodType === 'period' && !endDateInput.value) {
                endDateInput.classList.add('border-red-500');
                endDateInput.classList.remove('border-green-500');
                endDateError.classList.remove('contribution-hidden');
                endDateError.textContent = 'O mês/ano final é obrigatório para período.';
                isValid = false;
            } else if (endDateInput.value && !/^\d{4}-\d{2}$/.test(endDateInput.value)) {
                endDateInput.classList.add('border-red-500');
                endDateInput.classList.remove('border-green-500');
                endDateError.classList.remove('contribution-hidden');
                endDateError.textContent = 'Formato de mês/ano inválido (ex.: YYYY-MM).';
                isValid = false;
            } else if (endDateInput.value && endDateInput.value < startDateInput.value) {
                endDateInput.classList.add('border-red-500');
                endDateInput.classList.remove('border-green-500');
                endDateError.classList.remove('contribution-hidden');
                endDateError.textContent = 'O mês/ano final deve ser posterior ou igual ao inicial.';
                isValid = false;
            } else {
                endDateInput.classList.remove('border-red-500');
                endDateInput.classList.add('border-green-500');
                endDateError.classList.add('contribution-hidden');
            }

            // Validar descrição (opcional, mas não vazia se preenchida)
            if (descriptionInput.value && descriptionInput.value.trim() === '') {
                descriptionInput.classList.add('border-red-500');
                descriptionInput.classList.remove('border-green-500');
                descriptionError.classList.remove('contribution-hidden');
                descriptionError.textContent = 'A descrição não pode ser vazia.';
                isValid = false;
            } else {
                descriptionInput.classList.remove('border-red-500');
                descriptionInput.classList.add('border-green-500');
                descriptionError.classList.add('contribution-hidden');
            }

            // Validar comunidade
            if (!communityInput.value) {
                communityInput.classList.add('border-red-500');
                communityInput.classList.remove('border-green-500');
                communityError.classList.remove('contribution-hidden');
                communityError.textContent = 'Selecione uma comunidade.';
                isValid = false;
            } else {
                communityInput.classList.remove('border-red-500');
                communityInput.classList.add('border-green-500');
                communityError.classList.add('contribution-hidden');
            }

            // Validar campos de cartão de crédito (apenas se payment_method for creditcard)
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            if (paymentMethod === 'creditcard') {
                // Validar número do cartão com Luhn
                if (!/^\d{4}\s?\d{4}\s?\d{4}\s?\d{1,7}$/.test(cardNumberInput.value)) {
                    cardNumberInput.classList.add('border-red-500');
                    cardNumberInput.classList.remove('border-green-500');
                    cardNumberError.classList.remove('contribution-hidden');
                    cardNumberError.textContent = 'O número do cartão é inválido.';
                    isValid = false;
                } else if (!validateLuhn(cardNumberInput.value)) {
                    cardNumberInput.classList.add('border-red-500');
                    cardNumberInput.classList.remove('border-green-500');
                    cardNumberError.classList.remove('contribution-hidden');
                    cardNumberError.textContent = 'O número do cartão não é válido (falha na validação Luhn).';
                    isValid = false;
                } else {
                    cardNumberInput.classList.remove('border-red-500');
                    cardNumberInput.classList.add('border-green-500');
                    cardNumberError.classList.add('contribution-hidden');
                }

                // Validar mês de expiração
                const expiryMonth = parseInt(cardExpiryMonthInput.value, 10);
                if (isNaN(expiryMonth) || expiryMonth < 1 || expiryMonth > 12) {
                    cardExpiryMonthInput.classList.add('border-red-500');
                    cardExpiryMonthInput.classList.remove('border-green-500');
                    cardExpiryMonthError.classList.remove('contribution-hidden');
                    cardExpiryMonthError.textContent = 'Mês de validade inválido (01-12).';
                    isValid = false;
                } else {
                    cardExpiryMonthInput.classList.remove('border-red-500');
                    cardExpiryMonthInput.classList.add('border-green-500');
                    cardExpiryMonthError.classList.add('contribution-hidden');
                }

                // Validar ano de expiração e verificar se o cartão está vencido
                const expiryYear = parseInt(cardExpiryYearInput.value, 10);
                const currentYear = new Date().getFullYear();
                const currentMonth = new Date().getMonth() + 1; // Mês atual (1-12)
                if (isNaN(expiryYear) || expiryYear < currentYear || expiryYear > 2099) {
                    cardExpiryYearInput.classList.add('border-red-500');
                    cardExpiryYearInput.classList.remove('border-green-500');
                    cardExpiryYearError.classList.remove('contribution-hidden');
                    cardExpiryYearError.textContent = 'Ano de validade inválido ({{ date('Y') }}-2099).';
                    isValid = false;
                } else if (expiryYear === currentYear && expiryMonth < currentMonth) {
                    cardExpiryMonthInput.classList.add('border-red-500');
                    cardExpiryMonthInput.classList.remove('border-green-500');
                    cardExpiryMonthError.classList.remove('contribution-hidden');
                    cardExpiryMonthError.textContent = 'O cartão está vencido.';
                    isValid = false;
                } else {
                    cardExpiryYearInput.classList.remove('border-red-500');
                    cardExpiryYearInput.classList.add('border-green-500');
                    cardExpiryYearError.classList.add('contribution-hidden');
                }

                // Validar CVC
                if (!/^\d{3,4}$/.test(cardCvcInput.value)) {
                    cardCvcInput.classList.add('border-red-500');
                    cardCvcInput.classList.remove('border-green-500');
                    cardCvcError.classList.remove('contribution-hidden');
                    cardCvcError.textContent = 'CVC inválido.';
                    isValid = false;
                } else {
                    cardCvcInput.classList.remove('border-red-500');
                    cardCvcInput.classList.add('border-green-500');
                    cardCvcError.classList.add('contribution-hidden');
                }

                // Validar nome do titular
                if (!cardHolderNameInput.value) {
                    cardHolderNameInput.classList.add('border-red-500');
                    cardHolderNameInput.classList.remove('border-green-500');
                    cardHolderNameError.classList.remove('contribution-hidden');
                    cardHolderNameError.textContent = 'O nome do titular é obrigatório.';
                    isValid = false;
                } else {
                    cardHolderNameInput.classList.remove('border-red-500');
                    cardHolderNameInput.classList.add('border-green-500');
                    cardHolderNameError.classList.add('contribution-hidden');
                }
            } else {
                // Limpar validações do cartão se não for creditcard
                cardNumberInput.classList.remove('border-red-500', 'border-green-500');
                cardExpiryMonthInput.classList.remove('border-red-500', 'border-green-500');
                cardExpiryYearInput.classList.remove('border-red-500', 'border-green-500');
                cardCvcInput.classList.remove('border-red-500', 'border-green-500');
                cardHolderNameInput.classList.remove('border-red-500', 'border-green-500');
                cardNumberError.classList.add('contribution-hidden');
                cardExpiryMonthError.classList.add('contribution-hidden');
                cardExpiryYearError.classList.add('contribution-hidden');
                cardCvcError.classList.add('contribution-hidden');
                cardHolderNameError.classList.add('contribution-hidden');
            }

            return isValid;
        }

        // Submissão do formulário
        document.getElementById('contribution-form').addEventListener('submit', async function (event) {
            event.preventDefault();
            if (!validateForm()) {
                submitButton.disabled = false;
                submitButton.textContent = 'Criar Contribuição';
                return;
            }

            const serverError = document.getElementById('server-error');
            submitButton.disabled = true;
            submitButton.textContent = 'Processando...';
            serverError.classList.add('contribution-hidden');

            const formData = new FormData(document.getElementById('contribution-form'));
            // Converter vírgula para ponto no amount
            const amountValue = formData.get('amount').replace(/\./g, '').replace(',', '.');
            formData.set('amount', amountValue);
            // Converter start_date e end_date de YYYY-MM para YYYY-MM-DD
            const startDateValue = formData.get('start_date');
            if (startDateValue) {
                formData.set('start_date', `${startDateValue}-01`);
            }
            const endDateValue = formData.get('end_date');
            if (endDateValue) {
                formData.set('end_date', `${endDateValue}-01`);
            }

            // Limpar espaços do número do cartão de crédito
            const paymentMethod = formData.get('payment_method');
            let creditCardData = null;
            if (paymentMethod === 'creditcard') {
                const cardNumber = formData.get('creditCard[number]').replace(/\s/g, '');
                formData.set('creditCard[number]', cardNumber);
                // Armazenar dados do cartão para uso posterior (salvar cartão)
                creditCardData = {
                    number: cardNumber,
                    expiryMonth: formData.get('creditCard[expiryMonth]'),
                    expiryYear: formData.get('creditCard[expiryYear]'),
                    ccv: formData.get('creditCard[ccv]'),
                    holderName: formData.get('creditCard[holderName]'),
                    holderInfo: {
                        name: formData.get('creditCard[holderInfo][name]'),
                        email: formData.get('creditCard[holderInfo][email]'),
                        cpfCnpj: formData.get('creditCard[holderInfo][cpfCnpj]'),
                        postalCode: formData.get('creditCard[holderInfo][postalCode]'),
                        addressNumber: formData.get('creditCard[holderInfo][addressNumber]'),
                        phone: formData.get('creditCard[holderInfo][phone]'),
                        mobilePhone: formData.get('creditCard[holderInfo][mobilePhone]'),
                    }
                };
            }

            // Log dos dados enviados para depuração
            console.log('Enviando dados para o servidor:', Object.fromEntries(formData));

            try {
                const response = await fetch('{{ route('member.contributions.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData,
                });

                const data = await response.json();

                console.log('Resposta do servidor:', data);

                if (!response.ok) {
                    serverError.classList.remove('contribution-hidden');
                    if (response.status === 422 && data.errors) {
                        let errorMessage = 'Erro de validação:';
                        Object.keys(data.errors).forEach(key => {
                            errorMessage += `\n${key}: ${data.errors[key].join(', ')}`;
                        });
                        serverError.textContent = errorMessage;
                    } else {
                        serverError.textContent = data.message || 'Erro ao criar cobrança';
                    }
                    submitButton.disabled = false;
                    submitButton.textContent = 'Criar Contribuição';
                    return;
                }

                // Manipular sucesso com base no método de pagamento
                if (paymentMethod === 'boleto_bancario' && data.bankSlipUrl) {
                    window.open(data.bankSlipUrl, '_blank');
                    alert('Cobrança criada com sucesso! O boleto foi aberto em uma nova aba.');
                    setTimeout(() => {
                        window.location.href = '{{ route('member.contributions.index') }}';
                    }, 500);
                } else if (paymentMethod === 'pix' && (data.pixQrCode || data.pixPayload)) {
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
                                    ${data.pixQrCode ? `<img src="data:image/png;base64,${data.pixQrCode}" alt="QR Code PIX">` : ''}
                                    <div class="copy-paste">
                                        <p><strong>Código PIX (Copia e Cola):</strong></p>
                                        <input type="text" value="${data.pixPayload || ''}" readonly>
                                        <button onclick="navigator.clipboard.writeText(this.previousElementSibling.value).then(() => alert('Código copiado!'))">Copiar</button>
                                    </div>
                                </div>
                            </body>
                        </html>
                    `);
                    pixWindow.document.close();
                    alert('Cobrança criada com sucesso! O QR Code PIX foi aberto em uma nova janela.');
                    setTimeout(() => {
                        window.location.href = '{{ route('member.contributions.index') }}';
                    }, 500);
                } else if (paymentMethod === 'creditcard' && data.cardData) {
                    const saveCardButton = document.getElementById('saveCardButton');
                    saveCardButton.onclick = async function () {
                        try {
                            const saveResponse = await fetch('{{ route('member.credit-cards.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                },
                                body: JSON.stringify({
                                    member_id: '{{ $member->id }}',
                                    cardData: creditCardData,
                                    asaas_payment_id: data.asaas_payment_id,
                                }),
                            });

                            const saveData = await saveResponse.json();
                            if (!saveResponse.ok) {
                                serverError.classList.remove('contribution-hidden');
                                serverError.textContent = saveData.message || 'Erro ao salvar o cartão';
                                return;
                            }

                            alert('Cartão salvo com sucesso!');
                        } catch (error) {
                            console.error('Erro ao salvar cartão:', error);
                            serverError.classList.remove('contribution-hidden');
                            serverError.textContent = 'Erro ao salvar cartão: ' + error.message;
                        } finally {
                            bootstrap.Modal.getInstance(document.getElementById('saveCardModal')).hide();
                            setTimeout(() => {
                                window.location.href = '{{ route('member.contributions.index') }}';
                            }, 500);
                        }
                    };

                    // Exibir o modal
                    const modal = new bootstrap.Modal(document.getElementById('saveCardModal'));
                    modal.show();
                } else {
                    // Caso de erro ou resposta inesperada
                    serverError.classList.remove('contribution-hidden');
                    serverError.textContent = data.message || 'Erro ao processar a cobrança';
                    submitButton.disabled = false;
                    submitButton.textContent = 'Criar Contribuição';
                    return;
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                serverError.classList.remove('contribution-hidden');
                serverError.textContent = 'Erro ao criar cobrança: ' + error.message;
                submitButton.disabled = false;
                submitButton.textContent = 'Criar Contribuição';
            }
        });
    });
})();
</script>

<style>
    .border-red-500 {
        border-color: #ef4444 !important;
    }
    .border-green-500 {
        border-color: #22c55e !important;
    }
    .contribution-hidden {
        display: none !important;
    }
</style>
@endsection
