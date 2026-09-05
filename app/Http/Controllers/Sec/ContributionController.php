<?php

namespace App\Http\Controllers\Sec;

use App\Enums\PaymentMethodEnum;
use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Contribution;
use App\Models\FinancialEntity;
use App\Models\Member;
use App\Models\MonthlyConfig;
use App\Models\ParochialUnion;
use Illuminate\Validation\Rules\Enum;
use App\Models\UserCommunity;
use App\Services\BatchContributionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NumberToWords\NumberToWords;

class ContributionController extends Controller
{
    private const REQUIRED_DATE_RULE = 'required|date';
    private const EXISTS_FINANCIAL_ENTITY_RULE = 'exists:financial_entities,id';

    public function index()
    {
        $communityId = Auth::user()->community_id;

        $contributions = Contribution::where('community_id', $communityId)->where('status', 'confirmed')
            ->orderBy('contribution_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('sec.contributions.index', compact('contributions'));
    }

    public function create()
    {
        $communityId = Auth::user()->community_id;

        // Verifica se o usuário tem uma comunidade associada
        if (!$communityId) {
            return redirect()->back()->with('error', 'Usuário não está associado a uma comunidade.');
        }

        // Filtra entidades financeiras pela comunidade
        $financialEntities = FinancialEntity::where('community_id', $communityId)
            ->orderBy('name')
            ->get();

        // Define a entidade financeira padrão
        $defaultFinancialEntityId = $financialEntities->count() === 1
            ? $financialEntities->first()->id
            : ($financialEntities->count() > 1 ? $financialEntities->first()->id : null);

        // Busca membros da comunidade
        $members = Member::where('community_id', $communityId)->orderBy('first_name')->get();

        return view('sec.contributions.create', compact('communityId', 'financialEntities', 'defaultFinancialEntityId', 'members'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $communityId = $user->community_id;

        // Garante que o usuário "sec" esteja associado a uma comunidade
        if (! $communityId) {
            return redirect()->back()
                ->with('error', 'Usuário não está associado a uma comunidade.');
        }

        // Validação dos campos
        $request->validate([
            'financial_entity_id'            => [
                'required',
                'exists:financial_entities,id',
                function ($attr, $value, $fail) use ($communityId) {
                    if (! FinancialEntity::where('id', $value)
                        ->where('community_id', $communityId)
                        ->exists()) {
                        $fail('A entidade financeira selecionada é inválida para a sua comunidade.');
                    }
                },
            ],
            'contributions'                  => 'required|array|min:1',
            'contributions.*.member_id'      => 'required|exists:members,id',
            'contributions.*.type'           => 'required|in:mensal,periodo',
            'contributions.*.contribution_date' => 'required|date',
            'contributions.*.start_date'     => 'required|date',
            'contributions.*.end_date'       => 'nullable|date|after_or_equal:contributions.*.start_date',
            'contributions.*.amount'         => 'required|numeric|min:0.01',
            'contributions.*.payment_method' => ['required', new Enum(PaymentMethodEnum::class)],
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->input('contributions') as $entry) {
                Contribution::create([
                    'financial_entity_id' => $request->input('financial_entity_id'),
                    'member_id'           => $entry['member_id'],
                    'community_id'        => $communityId,
                    'type'                => $entry['type'],
                    'contribution_date'   => $entry['contribution_date'], // Data de criação/lançamento
                    'paid_at'             => $entry['contribution_date'], // Alimenta com a mesma data do lançamento
                    'is_paid'             => true,                        // Marca como pago
                    'start_date'          => $entry['start_date'],
                    'end_date'            => $entry['type'] === 'periodo'
                        ? ($entry['end_date'] ?? null)
                        : null,
                    'amount'              => $entry['amount'],
                    'payment_method'      => $entry['payment_method'],
                    'status'              => 'confirmed',
                ]);
            }

            DB::commit();

            return redirect()
                ->route('sec.contributions.index')
                ->with('success', 'Contribuições registradas com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao registrar contribuições: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao registrar contribuições.'])
                ->withInput();
        }
    }

    public function generateReceipt($id)
    {
        $contribution = Contribution::with('member')->findOrFail($id);

        $receiptNumber = str_pad($contribution->id, 6, '0', STR_PAD_LEFT);

        // Valor por extenso
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('pt_BR');
        $valor = number_format($contribution->amount, 2, '.', '');
        [$reais, $centavos] = explode('.', $valor);

        $valorPorExtenso = ucfirst($numberTransformer->toWords((int)$reais)) . ' reais';
        if ((int)$centavos > 0) {
            $valorPorExtenso .= ' e ' . $numberTransformer->toWords((int)$centavos) . ' centavos';
        }

        $date = now()->format('d/m/Y');
        $generatedBy = Auth::user()->name;

        $parochialUnion = ParochialUnion::first();

        $pdf = Pdf::loadView('sec.contributions.reports.receipt', compact(
            'contribution',
            'receiptNumber',
            'valorPorExtenso',
            'date',
            'parochialUnion',
            'generatedBy'
        ));

        return $pdf->download("recibo_{$receiptNumber}-CodMe{$contribution->member->id}.pdf");
    }

    public function show(Contribution $contribution)
    {
        $this->authorize('view', $contribution);

        return view('sec.contributions.show', compact('contribution'));
    }

    public function edit(Contribution $contribution)
    {
        $this->authorize('update', $contribution);

        $communityId = Auth::user()->community_id;
        $financialEntities = FinancialEntity::where('community_id', $communityId)->orderBy('name')->get();
        $members = Member::where('community_id', $communityId)->orderBy('name')->get();

        return view('sec.contributions.edit', compact('contribution', 'financialEntities', 'members'));
    }

    public function update(Request $request, Contribution $contribution)
    {
        $this->authorize('update', $contribution);

        $communityId = Auth::user()->community_id;

        $request->validate([
            'financial_entity_id' => [
                'required',
                'exists:financial_entities,id',
                function ($attribute, $value, $fail) use ($communityId) {
                    if (!FinancialEntity::where('id', $value)->where('community_id', $communityId)->exists()) {
                        $fail('A entidade financeira selecionada é inválida.');
                    }
                },
            ],
            'contribution_date' => self::REQUIRED_DATE_RULE,
            'amount' => 'required|numeric|min:0.01',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'required|in:mensal,periodo',
            'payment_method' => 'required|in:' . implode(',', PaymentMethodEnum::values()),
        ]);

        $member = Member::where('id', $request->member_id)->where('community_id', $communityId)->first();
        if (!$member) {
            return redirect()->back()->withErrors(['member_id' => 'Membro inválido para esta comunidade.']);
        }

        $contribution->update([
            'financial_entity_id' => $request->financial_entity_id,
            'member_id' => $request->member_id,
            'community_id' => $communityId,
            'type' => $request->type,
            'contribution_date' => $request->contribution_date,
            'amount' => $request->amount,
            'start_date' => $request->start_date,
            'end_date' => $request->type === 'periodo' ? ($request->end_date ?? null) : null,
            'payment_method' => $request->payment_method,
        ]);

        return redirect()->route('sec.contributions.index')->with('success', 'Contribuição atualizada com sucesso.');
    }

    public function destroy(Contribution $contribution)
    {
        $this->authorize('delete', $contribution);

        $contribution->delete();

        return redirect()->route('sec.contributions.index')->with('success', 'Contribuição excluída com sucesso.');
    }

    public function reportByFinancialEntity()
    {
        $communityId = Auth::user()->community_id;
        if (!$communityId) {
            return redirect()->back()->with('error', 'Usuário não está associado a uma comunidade.');
        }

        $financialEntities = FinancialEntity::where('community_id', $communityId)
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        return view('sec.contributions.reports.by_financial_entity', compact('financialEntities'));
    }

    public function generateReportByFinancialEntity(Request $request)
    {
        $request->validate([
            'financial_entity_id' => [
                'required',
                'exists:financial_entities,id',
                function ($attribute, $value, $fail) use ($request) {
                    $communityId = Auth::user()->community_id;
                    if (!FinancialEntity::where('id', $value)->where('community_id', $communityId)->exists()) {
                        $fail('A entidade financeira selecionada é inválida.');
                    }
                },
            ],
            'start_date' => self::REQUIRED_DATE_RULE,
            'end_date' => self::REQUIRED_DATE_RULE . '|after_or_equal:start_date',
        ]);

        $communityId = Auth::user()->community_id;
        $financialEntity = FinancialEntity::where('id', $request->financial_entity_id)
            ->where('community_id', $communityId)
            ->firstOrFail();

        $contributions = Contribution::with(['member', 'financial_entity'])
            ->where('community_id', $communityId)
            ->where('financial_entity_id', $request->financial_entity_id)
            ->whereBetween('contribution_date', [$request->start_date, $request->end_date])
            ->orderBy('contribution_date', 'asc')
            ->get();

        // Debug para verificar $contributions
        Log::info('Contributions data (by_financial_entity):', [
            'count' => $contributions->count(),
            'first_item' => $contributions->first() ? $contributions->first()->toArray() : null,
        ]);

        $parochialUnion = ParochialUnion::first();

        // Debug detalhado para o logo
        Log::info('ParochialUnion data (by_financial_entity):', [
            'parochial_union_exists' => !is_null($parochialUnion),
            'logo' => $parochialUnion ? $parochialUnion->logo : null,
            'storage_path' => $parochialUnion && $parochialUnion->logo ? storage_path('app/public/' . $parochialUnion->logo) : null,
            'storage_exists' => $parochialUnion && $parochialUnion->logo ? file_exists(storage_path('app/public/' . $parochialUnion->logo)) : false,
            'public_path' => $parochialUnion && $parochialUnion->logo ? public_path('storage/' . $parochialUnion->logo) : null,
            'public_exists' => $parochialUnion && $parochialUnion->logo ? file_exists(public_path('storage/' . $parochialUnion->logo)) : false,
            'public_url' => $parochialUnion && $parochialUnion->logo ? asset('storage/' . $parochialUnion->logo) : null,
        ]);

        if (!$parochialUnion) {
            Log::warning('ParochialUnion não encontrada para o relatório por entidade financeira.');
        }

        $generatedBy = Auth::user()->name;
        $date = now()->format('d/m/Y');

        // Verificar se $contributions é uma coleção
        if (!$contributions instanceof \Illuminate\Database\Eloquent\Collection) {
            Log::error('Contributions is not a collection', ['contributions' => $contributions]);
            return response()->json(['error' => 'Erro ao carregar contribuições'], 500);
        }

        $pdf = Pdf::loadView('sec.contributions.reports.by_financial_entity_pdf', compact(
            'contributions',
            'financialEntity',
            'parochialUnion',
            'generatedBy',
            'date',
            'request'
        ));

        return $pdf->download("relatorio_contribuicoes_{$financialEntity->name}_{$request->start_date}_a_{$request->end_date}.pdf");
    }

    public function reportByCommunity()
    {
        $communityId = Auth::user()->community_id;
        if (!$communityId) {
            return redirect()->back()->with('error', 'Usuário não está associado a uma comunidade.');
        }

        return view('sec.contributions.reports.by_community', compact('communityId'));
    }

    public function generateReportByCommunity(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_date' => self::REQUIRED_DATE_RULE,
            'end_date' => self::REQUIRED_DATE_RULE . '|after_or_equal:start_date',
        ]);
        $communityId = Auth::user()->community_id;

        $contributions = Contribution::with(['member', 'financial_entity'])
            ->where('community_id', $communityId)
            ->whereBetween('contribution_date', [$request->start_date, $request->end_date])
            ->orderBy('contribution_date', 'asc')
            ->get();

        $parochialUnion = ParochialUnion::first();

        Log::info('ParochialUnion data (community report):', [
            'exists' => !is_null($parochialUnion),
            'logo' => $parochialUnion ? $parochialUnion->logo : null,
            'logo_exists' => $parochialUnion && $parochialUnion->logo ? file_exists(public_path('storage/' . $parochialUnion->logo)) : false,
        ]);

        $generatedBy = Auth::user()->name;
        $date = now()->format('d/m/Y');

        $pdf = Pdf::loadView('sec.contributions.reports.by_community_pdf', compact(
            'contributions',
            'parochialUnion',
            'generatedBy',
            'date',
            'request'
        ));

        return $pdf->download("relatorio_contribuicoes_comunidade_{$request->start_date}_a_{$request->end_date}.pdf");
    }

    public function showBatchForm()
    {
        $user = Auth::user();

        // Busca as comunidades às quais o usuário tem acesso
        $communities = $user->communities;

        return view('sec.contributions.batch_form', compact('communities'));
    }

    /**
     * Processa o lote e retorna o arquivo ZIP para download.
     */
    public function processBatchGenerate(Request $request, BatchContributionService $batchService)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'month'        => 'required|integer|between:1,12',
            'year'         => 'required|integer|min:2020|max:2099',
            'community_id' => 'required|exists:communities,id',
        ]);

        $communityId = (int) $request->input('community_id');
        $month       = (int) $request->input('month');
        $year        = (int) $request->input('year');

        // Monta a data no formato Y-m-01 para buscar no banco
        $referenceDate = Carbon::createFromDate($year, $month, 1)->format('Y-m-01');

        // Validação de existência da configuração para a comunidade
        $configExists = MonthlyConfig::where('community_id', $communityId)
            ->whereDate('reference_date', $referenceDate)
            ->exists();

        if (!$configExists) {
            $meses = [
                1 => 'Janeiro',
                2 => 'Fevereiro',
                3 => 'Março',
                4 => 'Abril',
                5 => 'Maio',
                6 => 'Junho',
                7 => 'Julho',
                8 => 'Agosto',
                9 => 'Setembro',
                10 => 'Outubro',
                11 => 'Novembro',
                12 => 'Dezembro'
            ];

            return redirect()->back()
                ->withInput()
                ->with('error', "Não há configuração de cartas de contribuição cadastrada para {$meses[$month]}/{$year} nesta comunidade.");
        }

        try {
            $zipFilePath = $batchService->processBatch($communityId, $month, $year);

            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            // Registra o erro detalhado no arquivo storage/logs/laravel.log
            Log::error("Erro na geração em lote: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocorreu um erro ao processar as contribuições: ' . $e->getMessage());
        }
    }
}
