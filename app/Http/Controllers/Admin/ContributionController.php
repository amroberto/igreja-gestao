<?php

namespace App\Http\Controllers\Admin;

use App\Models\Member;
use App\Models\Community;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\FinancialEntity;
use App\Models\ParochialUnion;
use App\Enums\PaymentMethodEnum;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use NumberToWords\NumberToWords;

class ContributionController extends Controller
{
    private const REQUIRED_DATE_RULE = 'required|date';
    private const EXISTS_FINANCIAL_ENTITY_RULE = 'exists:financial_entities,id';

    public function index()
    {
        $contributions = Contribution::where('status', 'confirmed')
            ->where('financial_entity_id', '1')
            ->orderBy('contribution_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.contributions.index', compact('contributions'));
    }

    public function create()
    {
        $member = Member::find(request()->input('member_id'));

        if ($member) {
            $communities = Community::where('id', $member->community_id)->get();
            $members = Member::where('community_id', $member->community_id)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();
        } else {
            $communities = Community::all();
            $members = Member::orderBy('first_name')
                ->orderBy('last_name')
                ->get();
        }

        $financialEntities = FinancialEntity::where('id', 1)->get();
        $defaultFinancialEntityId = 1;

        return view('admin.contributions.create', compact(
            'communities',
            'financialEntities',
            'defaultFinancialEntityId',
            'members'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'financial_entity_id' => [
                'required',
                'exists:financial_entities,id',
                function ($attribute, $value, $fail) {
                    if ((int)$value !== 1) {
                        $fail('Apenas a entidade financeira ID 1 é permitida.');
                    }
                },
            ],
            'contributions' => 'required|array|min:1',
            'contributions.*.member_id' => 'required|integer|exists:members,id',
            'contributions.*.type' => 'required|in:mensal,periodo',
            'contributions.*.contribution_date' => 'required|date',
            'contributions.*.start_date' => 'required|date_format:Y-m',
            'contributions.*.end_date' => 'nullable|date_format:Y-m|after_or_equal:contributions.*.start_date',
            'contributions.*.amount' => 'required|numeric|min:0.01',
            'contributions.*.payment_method' => 'required|in:' . implode(',', PaymentMethodEnum::values()),
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->contributions as $entry) {
                $member = Member::findOrFail($entry['member_id']);

                // Converte "YYYY-MM" para data completa (primeiro dia do mês para start, último para end)
                $startDate = \Carbon\Carbon::createFromFormat('Y-m', $entry['start_date'])
                    ->startOfMonth()
                    ->format('Y-m-d');

                $endDate = null;
                if ($entry['type'] === 'periodo' && !empty($entry['end_date'])) {
                    $endDate = \Carbon\Carbon::createFromFormat('Y-m', $entry['end_date'])
                        ->endOfMonth()
                        ->format('Y-m-d');
                }

                Contribution::create([
                    'financial_entity_id' => 1,  // fixo
                    'member_id'           => $entry['member_id'],
                    'community_id'        => $member->community_id,
                    'type'                => $entry['type'],
                    'contribution_date'   => $entry['contribution_date'],
                    'start_date'          => $startDate,
                    'end_date'            => $endDate,
                    'amount'              => $entry['amount'],
                    'payment_method'      => $entry['payment_method'],
                    'status'              => 'confirmed',
                ]);
            }

            DB::commit();

            return redirect()->route('admin.contributions.index')
                ->with('success', 'Contribuições registradas com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();

            // Log detalhado para debug
            \Log::error('Falha ao salvar contribuições', [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->except(['_token']),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao salvar as contribuições. Verifique os dados e tente novamente.']);
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

        $pdf = Pdf::loadView('admin.contributions.reports.receipt', compact(
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

        return view('admin.contributions.show', compact('contribution'));
    }

    public function edit(Contribution $contribution)
    {
        $this->authorize('update', $contribution);

        $communityId = Auth::user()->community_id;
        $financialEntities = FinancialEntity::where('community_id', $communityId)->orderBy('name')->get();
        $members = Member::where('community_id', $communityId)->orderBy('name')->get();

        return view('admin.contributions.edit', compact('contribution', 'financialEntities', 'members'));
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

        return redirect()->route('admin.contributions.index')->with('success', 'Contribuição atualizada com sucesso.');
    }

    public function destroy(Contribution $contribution)
    {
        $this->authorize('delete', $contribution);

        $contribution->delete();

        return redirect()->route('admin.contributions.index')->with('success', 'Contribuição excluída com sucesso.');
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

        return view('admin.contributions.reports.by_financial_entity', compact('financialEntities'));
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

        $communityId = Community::all();
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

        $pdf = Pdf::loadView('admin.contributions.reports.by_financial_entity_pdf', compact(
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

        return view('admin.contributions.reports.by_community', compact('communityId'));
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

        $pdf = Pdf::loadView('admin.contributions.reports.by_community_pdf', compact(
            'contributions',
            'parochialUnion',
            'generatedBy',
            'date',
            'request'
        ));

        return $pdf->download("relatorio_contribuicoes_comunidade_{$request->start_date}_a_{$request->end_date}.pdf");
    }
}
