@extends('adminlte::page')

@section('title', 'Dar Baixa em Contribuição por Documento')

@section('content_header')
    <h1>Dar Baixa em Contribuição</h1>
@stop

@section('content')
    <form method="POST" action="{{ route('sec.contributions.payoff.store') }}" id="payoff-form">
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
            @include('sec.contributions.partials._payoff_entry', ['index' => 0])
        </div>

        <a href="{{ route('sec.contributions.index') }}" class="btn btn-secondary mt-3">
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
        const typeSelect = row.querySelector('.payoff-type');
        const endDateInput = row.querySelector('.end-date-input');

        if (!docId) {
            clearRow(row);
            return;
        }

        // Gera a URL dinâmica com o ID do documento
        const baseUrl = "{{ route('sec.contributions.find-pending-doc', ['id' => ':id']) }}";
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
            
            // Preenche o valor liberando para edição (se for maior que 0, formata com 2 casas decimais)
            const rawAmount = parseFloat(data.amount);
            amountInput.value = rawAmount > 0 ? rawAmount.toFixed(2) : '';

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

    // Gerencia alteração do tipo (Mensal vs Período) e restrição de data final
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

    // Eventos na tabela de entradas (delegação de eventos)
    container.addEventListener('change', function (e) {
        if (e.target.classList.contains('document-number-input')) {
            fetchDocumentData(e.target);
        }

        if (e.target.classList.contains('payoff-type')) {
            const row = e.target.closest('.payoff-row');
            updateTypeFields(row);
        }
    });

    // Remover Linha
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

        // Limpa inputs na nova linha
        newRow.querySelectorAll('input').forEach(i => {
            if (i.type !== 'date' || !i.classList.contains('paid-at-input')) {
                i.value = '';
            }
        });
        clearRow(newRow);

        // Atualiza os índices do array 'payoffs[X]' no atributo 'name'
        newRow.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${index}]`);
        });

        container.appendChild(newRow);
        updateTypeFields(newRow);
    });
});
</script>
@endpush