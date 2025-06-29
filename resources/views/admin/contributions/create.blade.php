@extends('adminlte::page')

@section('title', __('Registrar Contribuições'))

@section('content_header')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> {{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.contributions.index') }}">{{ __('Contribuições') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Registrar') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="container-fluid">
        <x-adminlte-card title="{{ __('Registrar Contribuições') }}" theme="primary" theme-mode="outline" collapsible>
            <!-- Alertas -->
            @if (session('success'))
                <x-adminlte-alert theme="success" title="{{ __('Sucesso') }}" dismissable>
                    {{ session('success') }}
                </x-adminlte-alert>
            @elseif (session('error'))
                <x-adminlte-alert theme="danger" title="{{ __('Erro') }}" dismissable>
                    {{ session('error') }}
                </x-adminlte-alert>
            @endif

            <form action="{{ route('admin.contributions.store') }}" method="POST" id="contribution-form">
                @csrf

                <!-- Contribuições -->
                <div id="contributions-container">
                    <div class="contribution-row mb-3">
                        <h5>{{ __('Contribuição') }} <span class="row-index">1</span></h5>
                        <div class="row align-items-end">
                            <div class="col-md-3 col-sm-6 col-12">
                                <x-adminlte-input name="contributions[0][member_code]" label="{{ __('Membro') }}"
                                    placeholder="{{ __('Código ou Nome') }}" igroup-size="sm" required
                                    class="member-autocomplete" data-index="0">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text"><i class="fas fa-user"></i></div>
                                    </x-slot>
                                    <input type="hidden" name="contributions[0][member_id]" class="member-id">
                                </x-adminlte-input>
                                @error('contributions.0.member_code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 col-sm-6 col-12">
                                <x-adminlte-select name="contributions[0][community_id]" label="{{ __('Comunidade') }}" igroup-size="sm" required>
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text"><i class="fas fa-church"></i></div>
                                    </x-slot>
                                    <option value="" disabled selected>{{ __('Selecione...') }}</option>
                                    @foreach ($communities as $community)
                                        <option value="{{ $community->id }}">{{ $community->name }}</option>
                                    @endforeach
                                </x-adminlte-select>
                                @error('contributions.0.community_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 col-sm-6 col-12">
                                <x-adminlte-input name="contributions[0][amount]" label="{{ __('Valor') }}" type="text" placeholder="0,00"
                                    igroup-size="sm" required>
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text">R$</div>
                                    </x-slot>
                                </x-adminlte-input>
                                @error('contributions.0.amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 col-sm-6 col-12">
                                <x-adminlte-input name="contributions[0][payment_date]" label="{{ __('Data') }}" type="date"
                                    value="{{ now()->format('Y-m-d') }}" igroup-size="sm" required>
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                                    </x-slot>
                                </x-adminlte-input>
                                @error('contributions.0.payment_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 col-sm-6 col-12">
                                <x-adminlte-select name="contributions[0][payment_method]" label="{{ __('Método') }}" igroup-size="sm" required>
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text"><i class="fas fa-money-bill"></i></div>
                                    </x-slot>
                                    <option value="cash">{{ __('Dinheiro') }}</option>
                                    <option value="deposit">{{ __('Depósito') }}</option>
                                    <option value="transfer">{{ __('Transferência') }}</option>
                                </x-adminlte-select>
                                @error('contributions.0.payment_method')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                <i class="fas fa-trash"></i> {{ __('Remover') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Botão para Adicionar Nova Contribuição -->
                <div class="mb-3">
                    <button type="button" id="add-contribution" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('Adicionar Contribuição') }}
                    </button>
                </div>

                <!-- Botões de Ação -->
                <div class="mt-4">
                    <x-adminlte-button type="submit" theme="primary" label="{{ __('Registrar') }}" icon="fas fa-save" id="submit-button" />
                    <a href="{{ route('admin.contributions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('Voltar') }}
                    </a>
                </div>
            </form>
        </x-adminlte-card>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        .form-group label {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .invalid-feedback {
            font-size: 0.8rem;
        }

        .contribution-row {
            border: 1px solid #dee2e6;
            padding: 1rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
        }

        .ui-autocomplete {
            z-index: 1051 !important;
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .form-group {
            margin-bottom: 0.5rem;
        }

        .input-group-sm > .form-control {
            font-size: 0.875rem;
        }
    </style>
@stop

@section('plugins.Jquery', true)
@section('plugins.JqueryUI', true)
@section('plugins.JqueryMask', true)
@section('plugins.Bootstrap', true)

@section('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function() {
            // Máscara para valor
            $('input[name*="amount"]').mask('000.000.000,00', { reverse: true });

            // Autocomplete para membros
            function initAutocomplete($input, index) {
                $input.autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: "{{ route('admin.members.autocomplete') }}",
                            dataType: "json",
                            data: { term: request.term },
                            success: function(data) {
                                response(data.map(item => ({
                                    label: `${item.name} (${item.code})`,
                                    value: item.code,
                                    id: item.id
                                })));
                            },
                            error: function(xhr, status, error) {
                                console.error('Erro no autocomplete:', status, error, xhr.responseText);
                                response([]);
                            }
                        });
                    },
                    minLength: 2,
                    select: function(event, ui) {
                        $(this).closest('.contribution-row').find('.member-id').val(ui.item.id);
                        $(this).val(ui.item.value);
                        return false;
                    },
                    change: function(event, ui) {
                        if (!ui.item) {
                            $(this).closest('.contribution-row').find('.member-id').val('');
                        }
                    }
                }).on('focus', function() {
                    $(this).autocomplete('search', $(this).val());
                });
            }

            // Inicializar autocomplete na primeira linha
            initAutocomplete($('.member-autocomplete[data-index="0"]'), 0);

            // Adicionar nova contribuição
            let rowIndex = 1;
            $('#add-contribution').click(function() {
                const template = `
                    <div class="contribution-row mb-3">
                        <h5>{{ __('Contribuição') }} <span class="row-index">${rowIndex + 1}</span></h5>
                        <div class="row align-items-end">
                            <div class="col-md-3 col-sm-6 col-12">
                                <x-adminlte-input name="contributions[${rowIndex}][member_code]" label="{{ __('Membro') }}"
                                    placeholder="{{ __('Código ou Nome') }}" igroup-size="sm" required
                                    class="member-autocomplete" data-index="${rowIndex}">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text"><i class="fas fa-user"></i></div>
                                    </x-slot>
                                    <input type="hidden" name="contributions[${rowIndex}][member_id]" class="member-id">
                                </x-adminlte-input>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12">
                                <x-adminlte-select name="contributions[${rowIndex}][community_id]" label="{{ __('Comunidade') }}" igroup-size="sm" required>
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text"><i class="fas fa-church"></i></div>
                                    </x-slot>
                                    <option value="" disabled selected>{{ __('Selecione...') }}</option>
                                    @foreach ($communities as $community)
                                        <option value="{{ $community->id }}">{{ $community->name }}</option>
                                    @endforeach
                                </x-adminlte-select>
                            </div>
                            <div class="col-md-2 col-sm-6 col-12">
                                <x-adminlte-input name="contributions[${rowIndex}][amount]" label="{{ __('Valor') }}" type="text" placeholder="0,00"
                                    igroup-size="sm" required>
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text">R$</div>
                                    </x-slot>
                                </x-adminlte-input>
                            </div>
                            <div class="col-md-2 col-sm-6 col-12">
                                <x-adminlte-input name="contributions[${rowIndex}][payment_date]" label="{{ __('Data') }}" type="date"
                                    value="{{ now()->format('Y-m-d') }}" igroup-size="sm" required>
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                                    </x-slot>
                                </x-adminlte-input>
                            </div>
                            <div class="col-md-2 col-sm-6 col-12">
                                <x-adminlte-select name="contributions[${rowIndex}][payment_method]" label="{{ __('Método') }}" igroup-size="sm" required>
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text"><i class="fas fa-money-bill"></i></div>
                                    </x-slot>
                                    <option value="cash">{{ __('Dinheiro') }}</option>
                                    <option value="deposit">{{ __('Depósito') }}</option>
                                    <option value="transfer">{{ __('Transferência') }}</option>
                                </x-adminlte-select>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-danger btn-sm remove-row">
                                <i class="fas fa-trash"></i> {{ __('Remover') }}
                            </button>
                        </div>
                    </div>
                `;
                $('#contributions-container').append(template);
                $(`input[name="contributions[${rowIndex}][amount]"]`).mask('000.000.000,00', { reverse: true });
                initAutocomplete($(`.member-autocomplete[data-index="${rowIndex}"]`), rowIndex);
                rowIndex++;
                updateRemoveButtons();
            });

            // Remover contribuição
            $(document).on('click', '.remove-row', function() {
                $(this).closest('.contribution-row').remove();
                updateRowIndexes();
                updateRemoveButtons();
            });

            // Atualizar índices das linhas
            function updateRowIndexes() {
                $('.contribution-row').each(function(index) {
                    $(this).find('.row-index').text(index + 1);
                    $(this).find('[name]').each(function() {
                        const name = $(this).attr('name').replace(/contributions\[\d+\]/, `contributions[${index}]`);
                        $(this).attr('name', name);
                    });
                    $(this).find('.member-autocomplete').attr('data-index', index);
                });
                rowIndex = $('.contribution-row').length;
            }

            // Habilitar/desabilitar botões de remover
            function updateRemoveButtons() {
                $('.remove-row').prop('disabled', $('.contribution-row').length === 1);
            }

            // Desabilitar botão de submit durante envio
            $('#contribution-form').on('submit', function() {
                $('#submit-button').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ __("Salvando...") }}');
            });
        });
    </script>
@stop