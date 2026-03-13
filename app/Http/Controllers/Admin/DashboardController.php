<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Member;
use App\Models\Community;
use App\Models\Contribution;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $communities = Community::all();

        $error = null;

        // Comunidade selecionada (ou a primeira por padrão)
        $accessibleCommunityIds = $communities->pluck('id')->toArray();
        $selectedCommunityId = $request->input('community_id');

        if (!$selectedCommunityId || !in_array((int) $selectedCommunityId, $accessibleCommunityIds)) {
            $selectedCommunityId = $accessibleCommunityIds[0] ?? null;
        }
        $selectedCommunityId = (int) $selectedCommunityId;

        $now = Carbon::now();
        $year = $now->year;

        // Métricas básicas
        $membrosCadastrados = Member::where('is_active', true)->count();

        $totalContribuintes = Member::where('is_contributor', true)->count();

        // Contribuições confirmadas
        $totalContribuicoesMes = Contribution::where('status', 'confirmed')
            ->whereMonth('contribution_date', $now->month)
            ->whereYear('contribution_date', $year)
            ->sum('amount');

        $totalContribuicoesAno = Contribution::where('status', 'confirmed')
            ->whereYear('contribution_date', $year)
            ->sum('amount');

        // Gráfico: Contribuições por mês (total geral)
        $contribuicoesPorMesRaw = Contribution::where('status', 'confirmed')
            ->whereYear('contribution_date', $year)
            ->selectRaw('MONTH(contribution_date) as mes, SUM(amount) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $contribuicoesPorMes = [
            'labels' => $contribuicoesPorMesRaw->map(fn($item) => Carbon::create()->month($item->mes)->translatedFormat('M'))->toArray(),
            'data'   => $contribuicoesPorMesRaw->pluck('total')->map(fn($v) => (float) $v)->toArray(),
        ];

        // Gráfico: Crescimento de membros por mês
        $crescimentoMembrosRaw = Member::whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $crescimentoMembros = [
            'labels' => $crescimentoMembrosRaw->map(fn($item) => Carbon::create()->month($item->mes)->translatedFormat('M'))->toArray(),
            'data'   => $crescimentoMembrosRaw->pluck('total')->toArray(),
        ];

        // Faixa etária
        $faixasRaw = Member::selectRaw("
            CASE
                WHEN birth_date IS NULL THEN 'Idade não informada'
                WHEN birth_date >= ? THEN 'Até 10 anos'
                WHEN birth_date < ? AND birth_date >= ? THEN '11 a 14 anos'
                WHEN birth_date < ? AND birth_date >= ? THEN '15 a 29 anos'
                WHEN birth_date < ? AND birth_date >= ? THEN '30 a 59 anos'
                WHEN birth_date < ? AND birth_date >= ? THEN '60 a 80 anos'
                ELSE 'Acima de 80 anos'
            END as faixa,
            COUNT(*) as total
        ", [
            $now->copy()->subYears(10),
            $now->copy()->subYears(10), $now->copy()->subYears(14),
            $now->copy()->subYears(14), $now->copy()->subYears(29),
            $now->copy()->subYears(29), $now->copy()->subYears(59),
            $now->copy()->subYears(59), $now->copy()->subYears(80),
        ])
            ->groupBy('faixa')
            ->pluck('total', 'faixa')
            ->toArray();

        $membrosPorFaixaEtaria = [
            'labels' => [
                'Até 10 anos',
                '11 a 14 anos',
                '15 a 29 anos',
                '30 a 59 anos',
                '60 a 80 anos',
                'Acima de 80 anos',
                'Idade não informada',
            ],
            'data' => [
                $faixasRaw['Até 10 anos']           ?? 0,
                $faixasRaw['11 a 14 anos']          ?? 0,
                $faixasRaw['15 a 29 anos']          ?? 0,
                $faixasRaw['30 a 59 anos']          ?? 0,
                $faixasRaw['60 a 80 anos']          ?? 0,
                $faixasRaw['Acima de 80 anos']      ?? 0,
                $faixasRaw['Idade não informada']   ?? 0,
            ],
        ];

        // Novo: Contribuições por comunidade por mês (gráfico de linhas)
        $contribPorComunidadePorMes = $this->getContribuicoesPorComunidadePorMes($year, $communities);

        return view('admin.dashboard', compact(
            'communities',
            'selectedCommunityId',
            'membrosCadastrados',
            'totalContribuintes',
            'totalContribuicoesMes',
            'totalContribuicoesAno',
            'contribuicoesPorMes',
            'crescimentoMembros',
            'membrosPorFaixaEtaria',
            'contribPorComunidadePorMes',
            'error'
        ));
    }

    /**
     * Retorna os dados formatados para o gráfico de contribuições por comunidade
     */
    private function getContribuicoesPorComunidadePorMes(int $year, $communities): array
    {
        // Filtrar apenas comunidades com id >= 2
        $filteredCommunities = $communities->filter(function ($community) {
            return $community->id >= 2;
        });

        // Se não houver nenhuma comunidade válida, retorna vazio para evitar erros
        if ($filteredCommunities->isEmpty()) {
            return [
                'labels'   => [],
                'datasets' => [],
            ];
        }

        $raw = Contribution::query()
            ->from('contributions as c')
            ->join('members as m', 'c.member_id', '=', 'm.id')
            ->where('c.status', 'confirmed')
            ->whereYear('c.contribution_date', $year)
            ->where('m.community_id', '>=', 2)  // ← reforço na query (opcional, mas mais seguro)
            ->selectRaw('
                MONTH(c.contribution_date) as mes,
                m.community_id,
                SUM(c.amount) as total
            ')
            ->groupBy('mes', 'm.community_id')
            ->orderBy('mes')
            ->get()
            ->groupBy('community_id');

        $months = range(1, 12);
        $datasets = [];

        foreach ($filteredCommunities as $community) {
            $monthlyValues = [];

            foreach ($months as $mes) {
                $value = $raw->get($community->id)
                    ?->firstWhere('mes', $mes)
                    ?->total ?? 0;

                $monthlyValues[] = (float) $value;
            }

            $datasets[] = [
                'label'           => $community->name,
                'data'            => $monthlyValues,
                'borderColor'     => $this->getCommunityColor($community->id),
                'backgroundColor' => $this->getCommunityColor($community->id) . '40',  // um pouco mais visível
                'tension'         => 0.3,
                'fill'            => false,
                'pointRadius'     => 5,
                'pointHoverRadius'=> 8,
                'borderWidth'     => 3,           // ← linhas mais grossas para diferenciar
            ];
        }

        return [
            'labels'   => collect($months)->map(fn($m) => Carbon::create($year, $m, 1)->translatedFormat('M'))->toArray(),
            'datasets' => $datasets,
        ];
    }

    /**
     * Gera cor fixa por ID de comunidade (ciclo de cores)
     */
    private function getCommunityColor(int $communityId): string
    {
        // Paleta com alto contraste e boa diferenciação (12+ cores distintas)
        $colors = [
            '#d32f2f',   // vermelho forte
            '#1976d2',   // azul escuro
            '#388e3c',   // verde médio
            '#f57c00',   // laranja forte
            '#7b1fa2',   // roxo
            '#0288d1',   // azul claro
            '#c2185b',   // rosa forte
            '#689f38',   // verde oliva
            '#f57f17',   // laranja escuro
            '#512da8',   // roxo escuro
            '#d81b60',   // magenta
            '#0097a7',   // ciano
            '#ff5722',   // laranja queimado
            '#4527a0',   // índigo
        ];

        // Usa o ID para ciclar na paleta (começa do índice 0 para id=2)
        $index = ($communityId - 2) % count($colors);

        return $colors[$index];
    }
}