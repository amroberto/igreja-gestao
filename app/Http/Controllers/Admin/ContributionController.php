<?php

namespace App\Http\Controllers\Admin;

use App\Models\Member;
use App\Exports\ContributionsByFinancialEntityExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContributionsByCommunityExport;
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
use Dompdf\Dompdf;
use Dompdf\Options;

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
        $financialEntities = FinancialEntity::orderBy('name')
            ->select('id', 'name')
            ->get();

        return view('admin.contributions.reports.by_financial_entity', compact('financialEntities'));
    }

    // ====================== RELATÓRIO POR ENTIDADE FINANCEIRA ======================
    public function generateReportByFinancialEntity(Request $request)
    {
        $request->validate([
            'financial_entity_id' => 'required|exists:financial_entities,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $format = $request->input('format', 'pdf');

        $financialEntity = FinancialEntity::findOrFail($request->financial_entity_id);

        $contributions = Contribution::with(['member.community', 'financial_entity'])
            ->where('financial_entity_id', $request->financial_entity_id)
            ->where('status', 'confirmed')
            ->whereBetween('contribution_date', [$request->start_date, $request->end_date])
            ->orderBy('contribution_date', 'asc')
            ->get();

        $parochialUnion = ParochialUnion::first();
        $generatedBy = Auth::user()->name;
        $date = now()->format('d/m/Y');

        if ($format === 'excel') {
            return Excel::download(
                new ContributionsByFinancialEntityExport($contributions, $request, $financialEntity),
                "relatorio_contribuicoes_{$financialEntity->name}_{$request->start_date}_a_{$request->end_date}.xlsx"
            );
        }

        if ($format === 'html') {
            return view('admin.contributions.reports.by_financial_entity_html', compact(
                'contributions', 'financialEntity', 'parochialUnion', 'generatedBy', 'date', 'request'
            ));
        }

        // PDF (padrão)
        $pdf = Pdf::loadView('admin.contributions.reports.by_financial_entity_pdf', compact(
            'contributions', 'financialEntity', 'parochialUnion', 'generatedBy', 'date', 'request'
        ));

        return $pdf->download("relatorio_contribuicoes_{$financialEntity->name}_{$request->start_date}_a_{$request->end_date}.pdf");
    }

    public function generateReportByPeriod(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $format = $request->input('format', 'pdf');

        $contributions = Contribution::with(['member.community', 'financial_entity'])
            ->where('status', 'confirmed')
            ->whereBetween('contribution_date', [$request->start_date, $request->end_date])
            ->orderBy('contribution_date', 'asc')
            ->get();

        $parochialUnion = ParochialUnion::first();
        $generatedBy = Auth::user()->name;
        $date = now()->format('d/m/Y');

        if ($format === 'excel') {
            return $this->exportToExcel($contributions, $request);
        }

        if ($format === 'html') {
            return view('admin.contributions.reports.by_period_html', compact(
                'contributions',
                'parochialUnion',
                'generatedBy',
                'date',
                'request'
            ));
        }

        // Default: PDF
        $pdf = Pdf::loadView('admin.contributions.reports.by_period_pdf', compact(
            'contributions',
            'parochialUnion',
            'generatedBy',
            'date',
            'request'
        ));

        $filename = "relatorio_contribuicoes_todas_comunidades_{$request->start_date}_a_{$request->end_date}.pdf";

        return $pdf->download($filename);
    }

    private function exportToExcel($contributions, $request)
    {
        return Excel::download(
            new \App\Exports\ContributionsByPeriodExport($contributions, $request),
            "relatorio_contribuicoes_todas_comunidades_{$request->start_date}_a_{$request->end_date}.xlsx"
        );
    }

    public function reportByPeriod()
    {
        return view('admin.contributions.reports.by_period');
    }

    public function reportByCommunity()
    {
        $communities = Community::all();
        return view('admin.contributions.reports.by_community', compact('communities'));
    }

    // ====================== RELATÓRIO POR COMUNIDADE ======================
    public function generateReportByCommunity(Request $request)
    {
        $request->validate([
            'community_id' => 'required|exists:communities,id',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
        ]);

        $format = $request->input('format', 'pdf');

        $community = Community::findOrFail($request->community_id);

        $contributions = Contribution::with(['member.community', 'financial_entity'])
            ->where('community_id', $request->community_id)
            ->whereBetween('contribution_date', [$request->start_date, $request->end_date])
            ->orderBy('contribution_date', 'asc')
            ->get();

        $parochialUnion = ParochialUnion::first();
        $generatedBy = Auth::user()->name;
        $date = now()->format('d/m/Y');

        if ($format === 'excel') {
            return Excel::download(
                new ContributionsByCommunityExport($contributions, $request, $community),
                "relatorio_contribuicoes_{$community->name}_{$request->start_date}_a_{$request->end_date}.xlsx"
            );
        }

        if ($format === 'html') {
            return view('admin.contributions.reports.by_community_html', compact(
                'contributions', 'community', 'parochialUnion', 'generatedBy', 'date', 'request'
            ));
        }

        // PDF
        $pdf = Pdf::loadView('admin.contributions.reports.by_community_pdf', compact(
            'contributions', 'community', 'parochialUnion', 'generatedBy', 'date', 'request'
        ));

        return $pdf->download("relatorio_contribuicoes_{$community->name}_{$request->start_date}_a_{$request->end_date}.pdf");
    }
}
