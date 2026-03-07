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
            $selectedCommunityId = $accessibleCommunityIds[0];
        }
        $selectedCommunityId = (int) $selectedCommunityId;

        $now = Carbon::now();

        // Métricas básicas
        $membrosCadastrados = Member::where('is_active', true)->count();

        $totalContribuintes = Member::where('is_contributor', true)
            ->count();

        // Contribuições confirmadas
        $totalContribuicoesMes = Contribution::where('status', 'confirmed')
            ->whereMonth('contribution_date', $now->month)
            ->whereYear('contribution_date', $now->year)
            ->sum('amount');

        $totalContribuicoesAno = Contribution::where('status', 'confirmed')
            ->whereYear('contribution_date', $now->year)
            ->sum('amount');

        // Gráfico: Contribuições por mês
        $contribuicoesPorMesRaw = Contribution::where('status', 'confirmed')
            ->whereYear('contribution_date', $now->year)
            ->selectRaw('MONTH(contribution_date) as mes, SUM(amount) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $contribuicoesPorMes = [
            'labels' => $contribuicoesPorMesRaw->map(fn($item) => Carbon::create()->month($item->mes)->translatedFormat('M'))->toArray(),
            'data'   => $contribuicoesPorMesRaw->pluck('total')->map(fn($v) => (float) $v)->toArray(),
        ];

        // Gráfico: Crescimento de membros por mês
        $crescimentoMembrosRaw = Member::whereYear('created_at', $now->year)
            ->selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $crescimentoMembros = [
            'labels' => $crescimentoMembrosRaw->map(fn($item) => Carbon::create()->month($item->mes)->translatedFormat('M'))->toArray(),
            'data'   => $crescimentoMembrosRaw->pluck('total')->toArray(),
        ];

        // Faixa etária – uma única query eficiente
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
                $faixasRaw['Até 10 anos'] ?? 0,
                $faixasRaw['11 a 14 anos'] ?? 0,
                $faixasRaw['15 a 29 anos'] ?? 0,
                $faixasRaw['30 a 59 anos'] ?? 0,
                $faixasRaw['60 a 80 anos'] ?? 0,
                $faixasRaw['Acima de 80 anos'] ?? 0,
                $faixasRaw['Idade não informada'] ?? 0,
            ],
        ];

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
            'error'
        ));
    }
}
