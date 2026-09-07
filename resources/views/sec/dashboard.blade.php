@extends('adminlte::page')

@section('title', __('Dashboard'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>{{ __('Dashboard') }}</h1>
    @if (!$error)
    <form method="GET" action="{{ route('sec.dashboard') }}" class="form-inline">
        <div class="input-group input-group-sm">
            <select name="community_id" class="form-control" style="width: 200px;">
                @foreach ($communities as $community)
                <option value="{{ $community->id }}" {{ $community->id == $selectedCommunityId ? 'selected' : '' }}>
                    {{ $community->name }}
                </option>
                @endforeach
            </select>
            <div class="input-group-append">
                <x-adminlte-button type="submit" theme="primary" label="{{ __('Filtrar') }}" icon="fas fa-filter"
                    class="btn-sm" />
            </div>
        </div>
    </form>
    @endif
</div>
@stop

@section('content')
<div class="container-fluid">
    <x-adminlte-card title="{{ __('Dashboard') }}" theme="primary" theme-mode="outline" collapsible>
        <!-- Alertas -->
        @if ($error)
        <x-adminlte-alert theme="danger" title="{{ __('Erro') }}" dismissable>
            {{ $error }}
        </x-adminlte-alert>
        @endif

        @if (!$error)
        <!-- Cartões de Métricas -->
        <div class="row">
            <div class="col-md-3">
                <x-adminlte-small-box title="{{ $membrosCadastrados }}" text="{{ __('Membros Cadastrados') }}"
                    icon="fas fa-users" theme="info" url="{{ route('sec.members.index') }}" />
            </div>
            <div class="col-md-3">
                <x-adminlte-small-box title="{{ $totalContribuintes }}" text="{{ __('Contribuintes') }}"
                    icon="fas fa-hand-holding-usd" theme="success" url="#" />
            </div>
            <div class="col-md-3">
                <x-adminlte-small-box title="{{ number_format($totalContribuicoesMes, 2, ',', '.') }}"
                    text="{{ __('Contribuições Mês') }}" icon="fas fa-money-bill-wave" theme="warning" url="#" />
            </div>
            <div class="col-md-3">
                <x-adminlte-small-box title="{{ number_format($totalContribuicoesAno, 2, ',', '.') }}"
                    text="{{ __('Contribuições Ano') }}" icon="fas fa-piggy-bank" theme="danger" url="#" />
            </div>
        </div>

        <!-- Linha 1 de Gráficos: Contribuições por Mês e Faixa Etária -->
        <div class="mt-4 row">
            <div class="col-md-6">
                <x-adminlte-card title="{{ __('Contribuições por Mês - :year', ['year' => $year ?? now()->year]) }}"
                    theme="secondary" collapsible>
                    <canvas id="contribuicoesChart" height="200"></canvas>
                </x-adminlte-card>
            </div>
            <div class="col-md-6">
                <x-adminlte-card title="{{ __('Membros por Faixa Etária') }}" theme="secondary" collapsible>
                    <canvas id="faixaEtariaChart" height="200"></canvas>
                </x-adminlte-card>
            </div>
        </div>

        <!-- Linha 2 de Gráficos: Método de Pagamento e Entidade Financeira -->
        <div class="row">
            <div class="col-md-6">
                <x-adminlte-card title="{{ __('Contribuições por Método de Pagamento') }}" theme="secondary" collapsible>
                    <canvas id="metodoPagamentoChart" height="200"></canvas>
                </x-adminlte-card>
            </div>
            <div class="col-md-6">
                <x-adminlte-card title="{{ __('Contribuições por Entidade Financeira') }}" theme="secondary" collapsible>
                    <canvas id="entidadeFinanceiraChart" height="200"></canvas>
                </x-adminlte-card>
            </div>
        </div>
        @endif
    </x-adminlte-card>
</div>
@stop

@section('plugins.Chartjs', true)

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

<script>
    // Registrar o plugin DataLabels
    Chart.register(ChartDataLabels);

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Gráfico de Contribuições por Mês (Barras Verticais)
        new Chart(document.getElementById('contribuicoesChart'), {
            type: 'bar',
            data: {
                labels: @json($contribuicoesPorMes['labels']),
                datasets: [{
                    label: '{{ __('Contribuições') }}',
                    data: @json($contribuicoesPorMes['data']),
                    backgroundColor: 'rgba(0, 123, 255, 0.6)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: '{{ __('Valor (R$)') }}'
                        }
                    }
                },
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: (value) => value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
                        color: '#000',
                        font: { weight: 'bold' }
                    }
                }
            }
        });

        // 2. Gráfico de Membros por Faixa Etária (Barras Horizontais)
        new Chart(document.getElementById('faixaEtariaChart'), {
            type: 'bar',
            data: {
                labels: @json($membrosPorFaixaEtaria['labels']),
                datasets: [{
                    label: '{{ __('Membros') }}',
                    data: @json($membrosPorFaixaEtaria['data']),
                    backgroundColor: [
                        'rgba(255, 65, 105, 0.6)',
                        'rgba(0, 150, 200, 0.6)',
                        'rgba(255, 180, 0, 0.6)',
                        'rgba(0, 180, 120, 0.6)',
                        'rgba(150, 100, 255, 0.6)',
                        'rgba(255, 140, 0, 0.6)',
                        'rgba(128, 128, 128, 0.6)'
                    ],
                    borderColor: [
                        'rgba(255, 65, 105, 1)',
                        'rgba(0, 150, 200, 1)',
                        'rgba(255, 180, 0, 1)',
                        'rgba(0, 180, 120, 1)',
                        'rgba(150, 100, 255, 1)',
                        'rgba(255, 140, 0, 1)',
                        'rgba(128, 128, 128, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: '{{ __('Quantidade de Membros') }}'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: '{{ __('Faixa Etária') }}'
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        formatter: (value) => value,
                        color: '#000',
                        font: { weight: 'bold' }
                    }
                }
            }
        });

        // 3. Gráfico de Métodos de Pagamento (Doughnut)
        new Chart(document.getElementById('metodoPagamentoChart'), {
            type: 'doughnut',
            data: {
                labels: @json($contribuicoesPorMetodoPagamento['labels']),
                datasets: [{
                    data: @json($contribuicoesPorMetodoPagamento['data']),
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(0, 123, 255, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(23, 162, 184, 0.7)',
                        'rgba(108, 117, 125, 0.7)',
                        'rgba(232, 62, 140, 0.7)'
                    ],
                    borderColor: [
                        '#28a745', '#007bff', '#ffc107', '#17a2b8', '#6c757d', '#e83e8c'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    datalabels: {
                        formatter: (value) => value > 0 ? value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : '',
                        color: '#fff',
                        font: { weight: 'bold', size: 11 }
                    }
                }
            }
        });

        // 4. Gráfico de Entidades Financeiras (Pie)
        new Chart(document.getElementById('entidadeFinanceiraChart'), {
            type: 'pie',
            data: {
                labels: @json($contribuicoesPorEntidade['labels']),
                datasets: [{
                    data: @json($contribuicoesPorEntidade['data']),
                    backgroundColor: [
                        'rgba(23, 162, 184, 0.7)',
                        'rgba(32, 201, 151, 0.7)',
                        'rgba(111, 66, 193, 0.7)',
                        'rgba(253, 126, 20, 0.7)',
                        'rgba(232, 62, 140, 0.7)',
                        'rgba(52, 58, 64, 0.7)'
                    ],
                    borderColor: [
                        '#17a2b8', '#20c997', '#6f42c1', '#fd7e14', '#e83e8c', '#343a40'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    datalabels: {
                        formatter: (value) => value > 0 ? value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : '',
                        color: '#fff',
                        font: { weight: 'bold', size: 11 }
                    }
                }
            }
        });
    });
</script>
@stop