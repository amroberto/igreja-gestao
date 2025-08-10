<?php

namespace App\Http\Controllers\Sec;

use Carbon\Carbon;
use App\Models\Member;
use App\Models\Community;
use Illuminate\View\View;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $communities = $user->communities;
        $error = null;

        if ($communities->isEmpty()) {
            return view('sec.dashboard', [
                'error' => 'Nenhuma comunidade associada ao usuário.',
                'communities' => collect(),
                'membrosCadastrados' => 0,
                'totalContribuintes' => 0,
                'contribuicoesMes' => 0,
                'contribuicoesAno' => 0,
                'contribuicoesPorMes' => ['labels' => [], 'data' => []],
                'crescimentoMembros' => ['labels' => [], 'data' => []],
                'selectedCommunityId' => null
            ]);
        }

        // Get selected community
        $selectedCommunityId = $request->input('community_id');
        $accessibleCommunityIds = $communities->pluck('id')->toArray();

        // Default to first community if none selected or invalid
        if (!$selectedCommunityId || !in_array((int) $selectedCommunityId, $accessibleCommunityIds)) {
            $selectedCommunityId = $accessibleCommunityIds[0];
        } else {
            $selectedCommunityId = (int) $selectedCommunityId;
        }

        // Metrics for selected community
        $membrosCadastrados = Member::where('community_id', $selectedCommunityId)->count();

        $totalContribuintes = Member::where('community_id', $selectedCommunityId)
            ->where('is_contributor', true)
            ->count();

        $contribuicoesMes = Contribution::where('contributions.community_id', $selectedCommunityId)
            ->where('contributions.status', 'confirmed')
            ->whereMonth('contributions.contribution_date', Carbon::now()->month)
            ->whereYear('contributions.contribution_date', Carbon::now()->year)
            ->sum('contributions.amount');

        $contribuicoesAno = Contribution::where('contributions.community_id', $selectedCommunityId)
            ->where('contributions.status', 'confirmed')
            ->whereYear('contributions.contribution_date', Carbon::now()->year)
            ->sum('contributions.amount');

        $contribuicoesPorMes = Contribution::where('contributions.community_id', $selectedCommunityId)
            ->where('contributions.status', 'confirmed')
            ->whereYear('contributions.contribution_date', Carbon::now()->year)
            ->selectRaw('MONTH(contributions.contribution_date) as mes, SUM(contributions.amount) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => Carbon::create()->month($item->mes)->translatedFormat('M'),
                    'total' => $item->total
                ];
            });
        $contribuicoesPorMes = [
            'labels' => $contribuicoesPorMes->pluck('label')->toArray(),
            'data' => $contribuicoesPorMes->pluck('total')->toArray()
        ];

        $crescimentoMembros = Member::where('community_id', $selectedCommunityId)
            ->whereYear('created_at', Carbon::now()->year)
            ->selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => Carbon::create()->month($item->mes)->translatedFormat('M'),
                    'total' => $item->total
                ];
            });
        $crescimentoMembros = [
            'labels' => $crescimentoMembros->pluck('label')->toArray(),
            'data' => $crescimentoMembros->pluck('total')->toArray()
        ];

        $membrosAte10anos = Member::where('community_id', $selectedCommunityId)
            ->where('birth_date', '>=', Carbon::now()->subYears(10))
            ->count();
        $membros11a14anos = Member::where('community_id', $selectedCommunityId)
            ->where('birth_date', '<', Carbon::now()->subYears(10))
            ->where('birth_date', '>=', Carbon::now()->subYears(14))
            ->count();
        $membros15a29anos = Member::where('community_id', $selectedCommunityId)->where('birth_date', '<', Carbon::now()->subYears(14))
            ->where('birth_date', '>=', Carbon::now()->subYears(29))
            ->count();
        $membros30a59anos = Member::where('community_id', $selectedCommunityId)
            ->where('birth_date', '<', Carbon::now()->subYears(29))
            ->where('birth_date', '>=', Carbon::now()->subYears(59))
            ->count();
        $membros60a80anos = Member::where('community_id', $selectedCommunityId)
            ->where('birth_date', '<', Carbon::now()->subYears(59))
            ->where('birth_date', '>=', Carbon::now()->subYears(80))
            ->count();
        $membrosAcima80anos = Member::where('community_id', $selectedCommunityId)
            ->where('birth_date', '<', Carbon::now()->subYears(80))
            ->count();

        $membrosPorFaixaEtaria = [
            'labels' => ['Até 10 anos', '11 a 14 anos', '15 a 29 anos', '30 a 59 anos', '60 a 80 anos', 'Acima de 80 anos'],
            'data' => [
                $membrosAte10anos,
                $membros11a14anos,
                $membros15a29anos,
                $membros30a59anos,
                $membros60a80anos,
                $membrosAcima80anos
            ]
        ];

        return view('sec.dashboard', compact(
            'communities',
            'membrosCadastrados',
            'totalContribuintes',
            'contribuicoesMes',
            'contribuicoesAno',
            'contribuicoesPorMes',
            'crescimentoMembros',
            'selectedCommunityId',
            'membrosAte10anos',
            'membros11a14anos',
            'membros15a29anos',
            'membros30a59anos',
            'membros60a80anos',
            'membrosAcima80anos',
            'membrosPorFaixaEtaria', // Adicione esta linha
            'error'
        ));
    }
}
