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
        document.addEventListener('DOMContentLoaded', function () {
            const entriesContainer = document.getElementById('contribution-entries');
            const addEntryBtn = document.getElementById('add-entry-btn');
            const form = document.getElementById('contribution-form');

            // Função para formatar número no formato brasileiro (1.234,56)
            function formatNumber(value) {
                const num = parseFloat(value) || 0;
                return num.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Função para desformatar número (converte 1.234,56 para 1234.56)
            function unformatNumber(value) {
                return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
            }

            // Função para aplicar formatação visual ao campo de valor
            function formatAmountField(input) {
                const rawValue = parseFloat(input.value) || 0;
                input.dataset.displayValue = formatNumber(rawValue);
                input.value = rawValue.toFixed(2); // Mantém o valor numérico para o backend
            }

            // Função para definir o mês/ano atual nos campos de Mês Inicial
            function setDefaultStartDate(row) {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const defaultDate = `${year}-${month}`;

                const startDateInput = row.querySelector('input[type="month"][name$="[start_date]"]');
                if (startDateInput && !startDateInput.value) {
                    startDateInput.value = defaultDate;
                }
            }

            // Função para atualizar visibilidade dos campos de data
            function updateDateFields(row) {
                const tipoSelect = row.querySelector('.contribution-type');
                const startDateGroup = row.querySelector('.start-date-group');
                const endDateGroup = row.querySelector('.end-date-group');

                if (tipoSelect.value === 'mensal') {
                    startDateGroup.style.display = 'block';
                    endDateGroup.style.display = 'none';
                    row.querySelector('[name$="[end_date]"]').value = '';
                } else {
                    startDateGroup.style.display = 'block';
                    endDateGroup.style.display = 'block';
                }

                setDefaultStartDate(row);
            }

            // Função para buscar o nome do membro
            function fetchMemberName(input, row) {
                const id = input.value.trim();
                const nameInput = row.querySelector('.member-name-display');

                if (id === '') {
                    nameInput.value = '';
                    return;
                }

                fetch(`/sec/members/name-by-code/${id}`)
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
                        alert(`Membro com o ID ${id} não foi encontrado.\nVerifique se o número está correto e tente novamente.`);
                    });
            }

            // Evento para formatar o campo de valor (visual apenas)
            entriesContainer.addEventListener('input', function (e) {
                if (e.target.name && e.target.name.includes('[amount]')) {
                    const input = e.target;
                    input.value = input.value.replace(/[^0-9.]/g, ''); // Permite apenas números e ponto
                }
            });

            entriesContainer.addEventListener('blur', function (e) {
                if (e.target.name && e.target.name.includes('[amount]')) {
                    formatAmountField(e.target);
                }
            }, true);

            // Evento para mudanças nos campos
            entriesContainer.addEventListener('change', function (e) {
                if (e.target.classList.contains('contribution-type')) {
                    const row = e.target.closest('.contribution-row');
                    updateDateFields(row);
                }

                if (e.target.classList.contains('member-id-input')) {
                    const row = e.target.closest('.contribution-row');
                    fetchMemberName(e.target, row);
                }
            });

            // Evento para remover linha
            entriesContainer.addEventListener('click', function (e) {
                if (e.target.closest('.remove-entry-btn')) {
                    const row = e.target.closest('.contribution-row');
                    if (entriesContainer.children.length > 1) {
                        row.remove();
                    } else {
                        alert('Deve haver pelo menos uma linha de contribuição.');
                    }
                }
            });

            // Evento para adicionar nova linha
            addEntryBtn.addEventListener('click', function () {
                const firstRow = entriesContainer.querySelector('.contribution-row');
                const newRow = firstRow.cloneNode(true);

                // Limpa os valores dos inputs na nova linha
                newRow.querySelectorAll('input, select').forEach(el => {
                    if (['text', 'number', 'date', 'month'].includes(el.type)) {
                        el.value = '';
                    } else if (el.tagName === 'SELECT') {
                        el.selectedIndex = 0;
                    }
                });

                // Atualiza os índices dos campos
                const index = entriesContainer.children.length;
                newRow.querySelectorAll('input, select').forEach(el => {
                    if (el.name) {
                        el.name = el.name.replace(/\[\d+\]/, `[${index}]`);
                    }
                    if (el.id) {
                        el.id = el.id.replace(/\d+/, index);
                    }
                });

                entriesContainer.appendChild(newRow);
                updateDateFields(newRow);
            });

            // Antes do envio, garante que os valores de amount estão no formato correto
            form.addEventListener('submit', function (e) {
                entriesContainer.querySelectorAll('input[name$="[amount]"]').forEach(input => {
                    const rawValue = parseFloat(input.value) || 0;
                    input.value = rawValue.toFixed(2); // Garante formato numérico (ex.: 1234.56)
                });
            });

            // Define o mês/ano atual para a linha inicial
            updateDateFields(entriesContainer.querySelector('.contribution-row'));
        });
    </script>
@endpush
