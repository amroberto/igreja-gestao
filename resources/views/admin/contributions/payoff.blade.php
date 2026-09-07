@extends('adminlte::page')

@section('title', 'Dar Baixa em Contribuição por Documento')

@section('plugins.jqueryMask', true)

@section('content_header')
    <h1>Dar Baixa em Contribuição</h1>
@stop

@section('content')
    <form method="POST" action="{{ route('admin.contributions.payoff.store') }}" id="payoff-form">
        @csrf

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Informe o <strong>Número do Documento</strong> (impresso no carnê/boleto em lote). Ao sair do campo, os dados serão carregados para que você possa conferir o valor, ajustar se necessário e registrar o pagamento.
        </div>

        @if (session('error'))
            <x-adminlte-alert theme="danger" title="Erro" dismissable>
                {{ session('error') }}
            </x-adminlte-alert>
        @endif

        <div id="payoff-entries">
            @include('admin.contributions.partials._payoff_entry', ['index' => 0])
        </div>

        <a href="{{ route('admin.contributions.index') }}" class="btn btn-secondary mt-3">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>

        <button type="submit" class="btn btn-success mt-3">
            <i class="fas fa-check-circle"></i> Confirmar Baixa(s)
        </button>

        <button type="button" class="btn btn-primary mt-3" id="add-entry-btn">
            <i class="fas fa-plus"></i> Dar Baixa em Mais de Um Documento
        </button>
    </form>
@stop

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('payoff-entries');
    const addBtn = document.getElementById('add-entry-btn');

    // Inicializa a máscara de moeda R$
    function applyMoneyMask(element) {
        if (typeof $.fn.mask !== 'undefined') {
            $(element).find('.money-mask').mask('#.##0,00', { reverse: true });
        }
    }

    // Formata o float do banco em string BRL (ex: 150.5 -> "150,50")
    function formatMoneyBRL(value) {
        return parseFloat(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Aplica a máscara na linha inicial
    applyMoneyMask(container);

    // Função de busca do documento via AJAX/Fetch
    function fetchDocumentData(input) {
        const row = input.closest('.payoff-row');
        const docId = input.value.trim();

        const memberDisplay = row.querySelector('.member-display');
        const amountInput = row.querySelector('.amount-input');
        const startDateDisplay = row.querySelector('.start-date-display');
        const startDateHidden = row.querySelector('.start-date-hidden-input');
        const contributionIdInput = row.querySelector('.contribution-id-input');
        const paymentMethodSelect = row.querySelector('.payment-method-input');
        const endDateInput = row.querySelector('.end-date-input');

        if (!docId) {
            clearRow(row);
            return;
        }

        const baseUrl = "{{ route('admin.contributions.find_pending_doc', ['id' => ':id']) }}";
        const requestUrl = baseUrl.replace(':id', encodeURIComponent(docId));

        fetch(requestUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('O servidor não retornou uma resposta JSON válida.');
            }

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Erro ao buscar documento.');
            }
            return data;
        })
        .then(data => {
            contributionIdInput.value = data.id;
            memberDisplay.value = `[Cód. ${data.member_id}] ${data.member_name}`;
            
            // Preenche e formata o valor com máscara
            const rawAmount = parseFloat(data.amount);
            if (rawAmount > 0) {
                amountInput.value = formatMoneyBRL(rawAmount);
                $(amountInput).trigger('input');
            } else {
                amountInput.value = '';
            }

            startDateDisplay.value = data.start_date || '';
            startDateHidden.value = data.start_date || '';

            if (data.payment_method) {
                paymentMethodSelect.value = data.payment_method;
            }

            if (data.start_date) {
                endDateInput.min = data.start_date;
            }
        })
        .catch(err => {
            clearRow(row);
            alert(err.message);
            input.value = '';
            input.focus();
        });
    }

    function clearRow(row) {
        row.querySelector('.contribution-id-input').value = '';
        row.querySelector('.member-display').value = '';
        row.querySelector('.amount-input').value = '';
        row.querySelector('.start-date-display').value = '';
        row.querySelector('.start-date-hidden-input').value = '';
    }

    function updateTypeFields(row) {
        const typeSelect = row.querySelector('.payoff-type');
        const endDateContainer = row.querySelector('.end-date-container');
        const endDateInput = row.querySelector('.end-date-input');
        const startDateHidden = row.querySelector('.start-date-hidden-input').value;

        if (typeSelect.value === 'periodo') {
            endDateContainer.style.display = 'block';
            endDateInput.required = true;
            if (startDateHidden) {
                endDateInput.min = startDateHidden;
            }
        } else {
            endDateContainer.style.display = 'none';
            endDateInput.required = false;
            endDateInput.value = '';
        }
    }

    container.addEventListener('change', function (e) {
        if (e.target.classList.contains('document-number-input')) {
            fetchDocumentData(e.target);
        }

        if (e.target.classList.contains('payoff-type')) {
            const row = e.target.closest('.payoff-row');
            updateTypeFields(row);
        }
    });

    container.addEventListener('click', function (e) {
        if (e.target.closest('.remove-entry-btn')) {
            const row = e.target.closest('.payoff-row');
            if (container.children.length > 1) {
                row.remove();
            } else {
                alert('É necessário ter pelo menos uma linha no formulário.');
            }
        }
    });

    // Adicionar Nova Linha
    addBtn.addEventListener('click', function () {
        const firstRow = container.querySelector('.payoff-row');
        const newRow = firstRow.cloneNode(true);
        const index = container.children.length;

        newRow.querySelectorAll('input').forEach(i => {
            if (i.type !== 'date' || !i.classList.contains('paid-at-input')) {
                i.value = '';
            }
        });
        clearRow(newRow);

        newRow.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${index}]`);
        });

        container.appendChild(newRow);
        applyMoneyMask(newRow);
        updateTypeFields(newRow);
    });

    // Converte os valores formatados ("1.250,50") em float ("1250.50") antes de enviar
    document.getElementById('payoff-form').addEventListener('submit', function (e) {
        container.querySelectorAll('.amount-input').forEach(input => {
            if (input.value) {
                let cleanValue = input.value.replace(/\./g, '').replace(',', '.');
                input.value = cleanValue;
            }
        });
    });
});
</script>
@endpush