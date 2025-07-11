<?php

namespace App\Http\Controllers\Member;

use Carbon\Carbon;
use App\Models\Community;
use App\Models\CreditCard;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\AsaasService;

class ContributionController extends Controller
{
    public function index()
    {
        try {
            $member = Auth::user()->member;
            if (!$member) {
                Log::warning('Usuário ID: ' . Auth::id() . ' não possui Member associado.');
                return redirect()->route('member.dashboard')->with('error', 'Nenhum perfil de membro encontrado.');
            }

            $contributions = Contribution::where('member_id', $member->id)
                ->orderByDesc('created_at')
                ->paginate(10);

            return view('member.contributions.index', compact('contributions'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar contribuições: ' . $e->getMessage());
            return redirect()->route('member.dashboard')->with('error', 'Erro ao carregar contribuições.');
        }
    }

    public function create()
    {
        $communities = Community::where('id', '!=', 1)->get();
        $member = Auth::user()->member;
        if (!$member) {
            Log::warning('Usuário ID: ' . Auth::id() . ' não possui Member associado.');
            return redirect()->route('member.dashboard')->with('error', 'Nenhum perfil de membro encontrado.');
        }

        $creditCards = $member->creditCards()->get();

        return view('member.contributions.create', compact('member', 'communities', 'creditCards'));
    }

    public function store(Request $request)
    {
        $member = Auth::user()->member;

        $request->validate([
            'community_id'    => 'required|exists:communities,id',
            'start_date'      => 'required|date_format:Y-m',
            'amount'          => 'required|numeric|min:0.01',
            'payment_method'  => 'required|in:pix,creditcard,boleto_bancario',
            'description'     => 'nullable|string|max:255',
            'credit_card_id'  => 'nullable|exists:credit_cards,id',
        ]);

        try {
            $data = $request->only([
                'community_id',
                'start_date',
                'end_date',
                'amount',
                'payment_method',
                'description',
                'credit_card_id'
            ]);

            $contributionDate = now();
            $startDate = Carbon::createFromFormat('Y-m', $data['start_date'])->startOfMonth();
            $endDate = $data['end_date'] ? Carbon::createFromFormat('Y-m', $data['end_date'])->endOfMonth() : null;

            $contribution = Contribution::create([
                'member_id'         => $member->id,
                'community_id'      => $data['community_id'],
                'contribution_date' => $contributionDate,
                'period_type'       => $data['end_date'] ? 'range' : 'monthly',
                'start_date'        => $startDate->toDateString(),
                'end_date'          => $endDate?->toDateString(),
                'amount'            => $data['amount'],
                'payment_method'    => $data['payment_method'],
            ]);

            if (empty($member->member_asaas_id)) {
                throw new \Exception('ID do cliente Asaas não encontrado para o membro.');
            }

            $asaasService = new AsaasService();
            $paymentPayload = [
                'customer_id'  => $member->member_asaas_id,
                'amount'       => $contribution->amount,
                'description'  => $contribution->description ?? 'Contribuição',
                'id'           => $contribution->id,
            ];

            $responseType = null;
            $responseData = null;

            switch ($data['payment_method']) {
                case 'pix':
                    $responseData = $asaasService->createPixCharge($paymentPayload);
                    $responseType = 'pix';
                    break;

                case 'boleto_bancario':
                    $dueDate = now()->addWeekdays(3)->format('Y-m-d');
                    $responseData = $asaasService->createBoletoCharge($paymentPayload, $dueDate);
                    $responseType = 'boleto';
                    break;

                case 'creditcard':
                    $card = CreditCard::find($data['credit_card_id']);
                    if (!$card || $card->member_id != $member->id) {
                        return back()->withErrors(['credit_card_id' => 'Cartão inválido.'])->withInput();
                    }
                    $paymentPayload['creditCardToken'] = $card->token;
                    $asaasService->payWithCreditCard($paymentPayload, $card);
                    $responseType = 'creditcard';
                    break;
            }

            // Um único return baseado no tipo
            return match ($responseType) {
                'pix' => view('member.contributions.pix', ['pix' => $responseData]),
                'boleto' => redirect()->away($responseData['bankSlipUrl']),
                'creditcard' => redirect()->route('member.contributions.index')->with('success', 'Pagamento com cartão realizado com sucesso.'),
                default => redirect()->route('member.contributions.index')->with('success', 'Contribuição registrada com sucesso.'),
            };
        } catch (\Exception $e) {
            Log::error('Erro ao registrar contribuição: ' . $e->getMessage());
            return back()->with('error', 'Erro ao registrar contribuição.')->withInput();
        }
    }
}
