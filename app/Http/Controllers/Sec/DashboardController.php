<?php

namespace App\Http\Controllers\Sec;

use Carbon\Carbon;
use App\Models\Member;
use App\Models\Community;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $communities = $user->communities;

        $error = null;

        if ($communities->isEmpty()) {
            return view('sec.dashboard', [
                'error'                 => 'Nenhuma comunidade associada ao usuário.',
                'communities'           => collect(),
                'membrosCadastrados'    => 0,
                'totalContribuintes'    => 0,
                'totalContribuicoesMes'  => 0,
                'totalContribuicoesAno'  => 0,
                'contribuicoesPorMes'   => ['labels' => [], 'data' => []],
                'crescimentoMembros'    => ['labels' => [], 'data' => []],
                'membrosPorFaixaEtaria' => ['labels' => [], 'data' => []],
                'selectedCommunityId'   => null,
            ]);
        }

        // Comunidade selecionada (ou a primeira por padrão)
        $accessibleCommunityIds = $communities->pluck('id')->toArray();
        $selectedCommunityId = $request->input('community_id');

        if (!$selectedCommunityId || !in_array((int) $selectedCommunityId, $accessibleCommunityIds)) {
            $selectedCommunityId = $accessibleCommunityIds[0];
        }
        $selectedCommunityId = (int) $selectedCommunityId;

        $now = Carbon::now();

        // Métricas básicas
        $membrosCadastrados = Member::where('community_id', $selectedCommunityId)->count();

        $totalContribuintes = Member::where('community_id', $selectedCommunityId)
            ->where('is_contributor', true)
            ->count();

        // Contribuições confirmadas
        $totalContribuicoesMes = Contribution::where('community_id', $selectedCommunityId)
            ->where('status', 'confirmed')
            ->whereMonth('contribution_date', $now->month)
            ->whereYear('contribution_date', $now->year)
            ->sum('amount');

        $totalContribuicoesAno = Contribution::where('community_id', $selectedCommunityId)
            ->where('status', 'confirmed')
            ->whereYear('contribution_date', $now->year)
            ->sum('amount');

        // Gráfico: Contribuições por mês
        $contribuicoesPorMesRaw = Contribution::where('community_id', $selectedCommunityId)
            ->where('status', 'confirmed')
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
        $crescimentoMembrosRaw = Member::where('community_id', $selectedCommunityId)
            ->whereYear('created_at', $now->year)
            ->selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $crescimentoMembros = [
            'labels' => $crescimentoMembrosRaw->map(fn($item) => Carbon::create()->month($item->mes)->translatedFormat('M'))->toArray(),
            'data'   => $crescimentoMembrosRaw->pluck('total')->toArray(),
        ];

        // Faixa etária – uma única query eficiente
        $faixasRaw = Member::where('community_id', $selectedCommunityId)
            ->selectRaw("
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

        return view('sec.dashboard', compact(
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