<?php

namespace App\Http\Controllers\Sec;

use Carbon\Carbon;
use App\Models\Member;
use App\Models\Community;
use App\Models\Contribution;
use App\Enums\PaymentMethodEnum;
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
                'error'                          => 'Nenhuma comunidade associada ao usuário.',
                'communities'                    => collect(),
                'membrosCadastrados'             => 0,
                'totalContribuintes'             => 0,
                'totalContribuicoesMes'           => 0,
                'totalContribuicoesAno'           => 0,
                'contribuicoesPorMes'            => ['labels' => [], 'data' => []],
                'crescimentoMembros'             => ['labels' => [], 'data' => []],
                'membrosPorFaixaEtaria'          => ['labels' => [], 'data' => []],
                'contribuicoesPorMetodoPagamento' => ['labels' => [], 'data' => []],
                'contribuicoesPorEntidade'       => ['labels' => [], 'data' => []],
                'selectedCommunityId'            => null,
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

        // Contribuições confirmadas/pagas no mês e no ano
        $totalContribuicoesMes = Contribution::where('community_id', $selectedCommunityId)
            ->where(function ($q) {
                $q->where('status', 'confirmed')->orWhere('is_paid', true);
            })
            ->whereRaw('MONTH(COALESCE(paid_at, contribution_date)) = ?', [$now->month])
            ->whereRaw('YEAR(COALESCE(paid_at, contribution_date)) = ?', [$now->year])
            ->sum('amount');

        $totalContribuicoesAno = Contribution::where('community_id', $selectedCommunityId)
            ->where(function ($q) {
                $q->where('status', 'confirmed')->orWhere('is_paid', true);
            })
            ->whereRaw('YEAR(COALESCE(paid_at, contribution_date)) = ?', [$now->year])
            ->sum('amount');

        // 1. Gráfico: Contribuições por mês
        $contribuicoesPorMesRaw = Contribution::where('community_id', $selectedCommunityId)
            ->where(function ($q) {
                $q->where('status', 'confirmed')->orWhere('is_paid', true);
            })
            ->whereRaw('YEAR(COALESCE(paid_at, contribution_date)) = ?', [$now->year])
            ->selectRaw('MONTH(COALESCE(paid_at, contribution_date)) as mes, SUM(amount) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $contribuicoesPorMes = [
            'labels' => $contribuicoesPorMesRaw->map(fn($item) => Carbon::create()->month($item->mes)->translatedFormat('M'))->toArray(),
            'data'   => $contribuicoesPorMesRaw->pluck('total')->map(fn($v) => (float) $v)->toArray(),
        ];

        // 2. Gráfico: Contribuições por Método de Pagamento no ano (Tratamento seguro para Enum Cast)
        $contribuicoesPorMetodoRaw = Contribution::where('community_id', $selectedCommunityId)
            ->where(function ($q) {
                $q->where('status', 'confirmed')->orWhere('is_paid', true);
            })
            ->whereRaw('YEAR(COALESCE(paid_at, contribution_date)) = ?', [$now->year])
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        $metodoLabels = [];
        $metodoData = [];

        foreach ($contribuicoesPorMetodoRaw as $item) {
            $paymentMethod = $item->payment_method;

            if ($paymentMethod instanceof PaymentMethodEnum) {
                $label = method_exists($paymentMethod, 'label') ? $paymentMethod->label() : $paymentMethod->name;
            } elseif (is_string($paymentMethod) || is_int($paymentMethod)) {
                $enum = PaymentMethodEnum::tryFrom($paymentMethod);
                $label = $enum && method_exists($enum, 'label') ? $enum->label() : ucfirst((string) $paymentMethod);
            } else {
                $label = 'Outro';
            }

            $metodoLabels[] = $label;
            $metodoData[] = (float) $item->total;
        }

        $contribuicoesPorMetodoPagamento = [
            'labels' => $metodoLabels,
            'data'   => $metodoData,
        ];

        // 3. Gráfico: Contribuições por Entidade Financeira no ano
        $contribuicoesPorEntidadeRaw = Contribution::from('contributions as c')
            ->join('financial_entities as fe', 'c.financial_entity_id', '=', 'fe.id')
            ->where('c.community_id', $selectedCommunityId)
            ->where(function ($q) {
                $q->where('c.status', 'confirmed')->orWhere('c.is_paid', true);
            })
            ->whereRaw('YEAR(COALESCE(c.paid_at, c.contribution_date)) = ?', [$now->year])
            ->selectRaw('fe.name as entidade, SUM(c.amount) as total')
            ->groupBy('fe.name')
            ->get();

        $contribuicoesPorEntidade = [
            'labels' => $contribuicoesPorEntidadeRaw->pluck('entidade')->toArray(),
            'data'   => $contribuicoesPorEntidadeRaw->pluck('total')->map(fn($v) => (float) $v)->toArray(),
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

        // Faixa etária
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
            'contribuicoesPorMetodoPagamento',
            'contribuicoesPorEntidade',
            'crescimentoMembros',
            'membrosPorFaixaEtaria',
            'error'
        ));
    }
}