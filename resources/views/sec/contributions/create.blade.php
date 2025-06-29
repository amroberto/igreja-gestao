@extends('adminlte::page')

@section('title', 'Registrar Contribuições')

@section('content_header')
<h1>Registrar Contribuições</h1>
@stop

@section('content')
<form method="POST" action="{{ route('sec.contributions.store') }}" id="contribution-form">
    @csrf
    <div class="alert alert-info">
        Preencha os dados das contribuições abaixo. Você pode adicionar várias linhas conforme necessário.
        Utilize o código do membro para buscar automaticamente o nome.
    </div>

    <!-- Entidade Financeira -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-body">
            <div class="form-group">
                <label for="financial_entity_id">{{ __('Entidade Financeira') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                    </div>
                    <select name="financial_entity_id" id="financial_entity_id" class="form-control @error('financial_entity_id') is-invalid @enderror" required>
                        @if ($financialEntities->isEmpty())
                        <option value="">{{ __('Nenhuma entidade disponível') }}</option>
                        @else
                        @foreach ($financialEntities as $entity)
                        <option value="{{ $entity->id }}" {{ $defaultFinancialEntityId == $entity->id ? 'selected' : '' }}>
                            {{ $entity->name }}
                        </option>
                        @endforeach
                        @endif
                    </select>
                </div>
                @error('financial_entity_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Contribuições Dinâmicas -->
    <div id="contribution-entries">
        {{-- Linha de contribuição padrão (index = 0) --}}
        @include('sec.contributions.partials._contribution_entry', ['index' => 0])

    </div>

    <a href="{{ route('sec.contributions.index') }}" class="btn btn-secondary mt-3">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>

    <button type="submit" class="btn btn-success mt-3">
        <i class="fas fa-save"></i> Salvar
    </button>

    <button type="button" class="btn btn-primary mt-3" id="add-entry-btn">
        <i class="fas fa-plus"></i> Adicionar Linha
    </button>
    
</form>
@stop

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const entriesContainer = document.getElementById('contribution-entries');
        const addEntryBtn = document.getElementById('add-entry-btn');

        // Função para atualizar visibilidade dos campos de data
        function updateDateFields(row) {
            const tipoSelect = row.querySelector('.contribution-type');
            const startDateGroup = row.querySelector('.start-date-group');
            const endDateGroup = row.querySelector('.end-date-group');

            if (tipoSelect.value === 'mensal') {
                startDateGroup.style.display = 'block';
                endDateGroup.style.display = 'none';
                // Limpar campo end_date para mensal
                row.querySelector('[name$="[end_date]"]').value = '';
            } else {
                startDateGroup.style.display = 'block';
                endDateGroup.style.display = 'block';
            }
        }

        // Busca o nome do membro pelo código
        function fetchMemberName(input, row) {
            const code = input.value.trim();
            const nameInput = row.querySelector('.member-name-display');

            if (code === '') {
                nameInput.value = '';
                return;
            }

            fetch(`/sec/members/name-by-code/${code}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Membro não encontrado');
                    }
                    return response.json();
                })
                .then(data => {
                    nameInput.value = data.name || '';
                })
                .catch(() => {
                    nameInput.value = '';
                    alert('Membro não encontrado para o código informado.');
                });
        }

        // Delegação de eventos
        entriesContainer.addEventListener('change', function(e) {
            // Atualizar campos de data
            if (e.target.classList.contains('contribution-type')) {
                const row = e.target.closest('.contribution-row');
                updateDateFields(row);
            }

            // Buscar nome do membro
            if (e.target.classList.contains('member-id-input')) {
                const row = e.target.closest('.contribution-row');
                fetchMemberName(e.target, row);
            }
        });

        // Remover linha
        entriesContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-entry-btn')) {
                const row = e.target.closest('.contribution-row');
                if (entriesContainer.children.length > 1) {
                    row.remove();
                } else {
                    alert('Deve haver pelo menos uma linha de contribuição.');
                }
            }
        });

        // Adicionar nova linha
        addEntryBtn.addEventListener('click', function() {
            const firstRow = entriesContainer.querySelector('.contribution-row');
            const newRow = firstRow.cloneNode(true);

            // Limpar valores
            newRow.querySelectorAll('input, select').forEach(el => {
                if (el.type === 'text' || el.type === 'number' || el.type === 'date' || el.type === 'month') {
                    el.value = '';
                } else if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                }
            });

            // Atualizar índices
            const index = entriesContainer.children.length;
            newRow.querySelectorAll('input, select').forEach(el => {
                if (el.name) {
                    el.name = el.name.replace(/\[\d+\]/, `[${index}]`);
                }
                if (el.id) {
                    el.id = el.id.replace(/\d+/, index);
                }
            });

            updateDateFields(newRow);
            entriesContainer.appendChild(newRow);
        });

        // Inicializar primeira linha
        updateDateFields(entriesContainer.querySelector('.contribution-row'));
    });

</script>
@endpush
