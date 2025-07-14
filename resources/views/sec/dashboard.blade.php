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
                <x-adminlte-button type="submit" theme="primary" label="{{ __('Filtrar') }}" icon="fas fa-filter" class="btn-sm" />
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
                <x-adminlte-small-box title="{{ $membrosCadastrados }}" text="{{ __('Membros Cadastrados') }}" icon="fas fa-users" theme="info" url="{{ route('sec.members.index') }}" />
            </div>
            <div class="col-md-3">
                <x-adminlte-small-box title="{{ $totalContribuintes }}" text="{{ __('Contribuintes') }}" icon="fas fa-hand-holding-usd" theme="success" url="#" />
            </div>
            <div class="col-md-3">
                <x-adminlte-small-box title="{{ number_format($contribuicoesMes, 2, ',', '.') }}" text="{{ __('Contribuições Mês') }}" icon="fas fa-money-bill-wave" theme="warning" url="#" />
            </div>
            <div class="col-md-3">
                <x-adminlte-small-box title="{{ number_format($contribuicoesAno, 2, ',', '.') }}" text="{{ __('Contribuições Ano') }}" icon="fas fa-piggy-bank" theme="danger" url="#" />
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row mt-4">
            <div class="col-md-6">
                <x-adminlte-card title="{{ __('Contribuições por Mês') }}" theme="secondary" collapsible>
                    <canvas id="contribuicoesChart" height="200"></canvas>
                </x-adminlte-card>
            </div>
            <div class="col-md-6">
                <x-adminlte-card title="{{ __('Membros por Faixa Etária') }}" theme="secondary" collapsible>
                    <canvas id="faixaEtariaChart" height="200"></canvas>
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
            // Gráfico de Contribuições (inalterado)
            new Chart(document.getElementById('contribuicoesChart'), {
                type: 'bar',
                data: {
                    labels: @json($contribuicoesPorMes['labels']),
                    datasets: [{
                        label: '{{ __('Contribuições') }}',
                        data: @json($contribuicoesPorMes['data']),
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Gráfico de Membros por Faixa Etária (barras horizontais)
            new Chart(document.getElementById('faixaEtariaChart'), {
                type: 'bar',
                data: {
                    labels: @json($membrosPorFaixaEtaria['labels']),
                    datasets: [{
                        label: '{{ __('Membros') }}',
                        data: @json($membrosPorFaixaEtaria['data']),
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y', // Define barras horizontais
                    scales: {
                        x: { // Eixo X (valores) para barras horizontais
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: '{{ __('Quantidade de Membros') }}'
                            }
                        },
                        y: { // Eixo Y (rótulos)
                            title: {
                                display: true,
                                text: '{{ __('Faixa Etária') }}'
                            }
                        }
                    },
                    plugins: {
                        datalabels: {
                            anchor: 'end', // Posiciona o rótulo no final da barra
                            align: 'end', // Alinha o rótulo à direita da barra
                            formatter: (value) => value, // Exibe o valor numérico
                            color: '#000', // Cor do texto
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                }
            });
        });
    </script>
@stop