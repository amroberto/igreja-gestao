<?php

namespace App\Http\Controllers\Member;

use App\Models\Member;
use App\Models\Community;
use App\Models\Contribution;
use Illuminate\Http\Request;
use App\Models\FinancialEntity;
use App\Enums\PaymentMethodEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\Asaas\AsaasPaymentService;
use App\Services\Asaas\AsaasCustomerService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ContributionController extends Controller
{
    protected $asaasPaymentService;
    protected $asaasCustomerService;

    public function __construct(AsaasPaymentService $asaasPaymentService, AsaasCustomerService $asaasCustomerService)
    {
        $this->asaasPaymentService = $asaasPaymentService;
        $this->asaasCustomerService = $asaasCustomerService;
    }

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
        $member = Auth::user()->member;
        if (!$member) {
            Log::warning('Usuário ID: ' . Auth::id() . ' não possui Member associado.');
            return redirect()->route('member.dashboard')->with('error', 'Nenhum perfil de membro encontrado.');
        }

        $communities = Community::where('id', '!=', 1)->get();

        return view('member.contributions.create', compact('member', 'communities'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'member_id' => 'required|exists:members,id',
                'community_id' => 'required|exists:communities,id',
                'contribution_date' => 'required|date|after_or_equal:today',
                'period_type' => 'required|in:current_month,period',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'payment_method' => 'required|in:boleto_bancario,pix,creditcard',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string|max:255',
                'creditCard.number' => 'nullable|required_if:payment_method,creditcard|regex:/^\d{13,19}$/',
                'creditCard.expiryMonth' => 'nullable|required_if:payment_method,creditcard|integer|between:1,12',
                'creditCard.expiryYear' => 'nullable|required_if:payment_method,creditcard|integer|between:' . date('Y') . ',2099',
                'creditCard.ccv' => 'nullable|required_if:payment_method,creditcard|regex:/^\d{3,4}$/',
                'creditCard.holderName' => 'nullable|required_if:payment_method,creditcard|string|max:255',
                'creditCard.holderInfo.name' => 'nullable|required_if:payment_method,creditcard|string|max:255',
                'creditCard.holderInfo.email' => 'nullable|required_if:payment_method,creditcard|email',
                'creditCard.holderInfo.cpfCnpj' => 'nullable|required_if:payment_method,creditcard|string',
                'creditCard.holderInfo.postalCode' => 'nullable|required_if:payment_method,creditcard|string',
                'creditCard.holderInfo.addressNumber' => 'nullable|required_if:payment_method,creditcard|string',
                'creditCard.holderInfo.phone' => 'nullable|required_if:payment_method,creditcard|string',
                'creditCard.holderInfo.mobilePhone' => 'nullable|required_if:payment_method,creditcard|string',
            ]);

            $member = Member::findOrFail($validated['member_id']);

            if (!$member->member_asaas_id) {
                throw new \Exception('ID do cliente Asaas não encontrado para o membro.');
            }

            $paymentData = [
                'customer' => $member->member_asaas_id,
                'value' => (float) $validated['amount'],
                'description' => $validated['description'] ?? 'Contribuição de ' . $member->name,
            ];

            // Vencimento padrão para 3 dias
            $dueDate = now()->addDays(3)->format('Y-m-d');

            $asaasResponse = null;
            if ($validated['payment_method'] === 'boleto_bancario') {
                $paymentData['billingType'] = 'BOLETO';
                $asaasResponse = $this->asaasPaymentService->createBoletoCharge($member, $paymentData, $dueDate);
            } elseif ($validated['payment_method'] === 'pix') {
                $paymentData['billingType'] = 'PIX';
                $asaasResponse = $this->asaasPaymentService->createPixCharge($member, $paymentData, $dueDate);
                Log::debug('Resposta do Asaas para cobrança PIX', ['asaas_response' => $asaasResponse]);
            } elseif ($validated['payment_method'] === 'creditcard') {
                $paymentData['billingType'] = 'CREDIT_CARD';
                $creditCardData = [
                    'holderName' => $validated['creditCard']['holderName'],
                    'number' => $validated['creditCard']['number'],
                    'expiryMonth' => sprintf('%02d', $validated['creditCard']['expiryMonth']),
                    'expiryYear' => $validated['creditCard']['expiryYear'],
                    'ccv' => $validated['creditCard']['ccv'],
                    'holderInfo' => $validated['creditCard']['holderInfo'],
                ];
                $asaasResponse = $this->asaasPaymentService->payWithCreditCard($member, $paymentData, $creditCardData);
            } else {
                throw new \Exception('Método de pagamento inválido: ' . $validated['payment_method']);
            }

            $paymentMethod = match ($validated['payment_method']) {
                'boleto_bancario' => PaymentMethodEnum::BankSlip->value,
                'pix' => PaymentMethodEnum::Pix->value,
                'creditcard' => PaymentMethodEnum::CreditCard->value,
                default => throw new \Exception('Método de pagamento inválido: ' . $validated['payment_method']),
            };

            DB::beginTransaction();
            $payment = Contribution::create([
                'contribution_date' => $validated['contribution_date'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'financial_entity_id' => FinancialEntity::where('name', 'On-line')->first()->id,
                'member_id' => $validated['member_id'],
                'community_id' => $validated['community_id'],
                'payment_method' => $paymentMethod,
                'amount' => $validated['amount'],
                'due_date' => $dueDate,
                'status' => 'pending',
                'asaas_payment_id' => $asaasResponse['id'] ?? null,
                'asaas_boleto_url' => $asaasResponse['bankSlipUrl'] ?? null,
                'asaas_pix_qr_code' => $asaasResponse['pixQrCode'] ?? $asaasResponse['qrCode'] ?? $asaasResponse['pix']['qrCode'] ?? null,
                'asaas_pix_payload' => $asaasResponse['encodedValue'] ?? $asaasResponse['payload'] ?? $asaasResponse['pix']['payload'] ?? null,
                'description' => $validated['description'],
                'created_at' => now(),
                'is_paid' => false,
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contribuição criada com sucesso',
                'bankSlipUrl' => $asaasResponse['bankSlipUrl'] ?? null,
                'pixQrCode' => $asaasResponse['pixQrCode'] ?? $asaasResponse['qrCode'] ?? $asaasResponse['pix']['qrCode'] ?? null,
                'pixPayload' => $asaasResponse['encodedValue'] ?? $asaasResponse['payload'] ?? $asaasResponse['pix']['payload'] ?? null,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar contribuição: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar contribuição: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Contribution $contribution)
    {
        $member = Auth::user()->member;

        if (!$member || $contribution->member_id !== $member->id) {
            return redirect()->route('member.contributions.index')->with('error', 'Ação não autorizada.');
        }

        if ($contribution->is_paid || in_array($contribution->status?->value ?? $contribution->status, ['confirmed', 'paid'])) {
            return redirect()->route('member.contributions.index')->with('error', 'Contribuições pagas não podem ser editadas.');
        }

        $communities = Community::where('id', '!=', 1)->get();

        return view('member.contributions.edit', compact('contribution', 'member', 'communities'));
    }

    /**
     * Ponto de entrada padrão para o recurso de atualização do REST
     */
    public function update(Request $request, Contribution $contribution)
    {
        return $this->updateDueDate($request, $contribution);
    }

    /**
     * Permite alterar termos de contribuição e reflete as alterações no Asaas
     */
    public function updateDueDate(Request $request, Contribution $contribution)
    {
        $member = Auth::user()->member;
        if (!$member || $contribution->member_id !== $member->id) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Ação não autorizada.'], 403);
            }
            return redirect()->back()->with('error', 'Ação não autorizada.');
        }

        if ($contribution->is_paid || in_array($contribution->status?->value ?? $contribution->status, ['confirmed', 'paid'])) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Contribuições já pagas não podem ser alteradas.'], 422);
            }
            return redirect()->back()->with('error', 'Contribuições já pagas não podem ser alteradas.');
        }

        $validator = Validator::make($request->all(), [
            'community_id'   => 'required|exists:communities,id',
            'due_date'       => 'required|date|after_or_equal:today',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:boleto_bancario,pix',
            'type'           => 'nullable|string',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'description'    => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro nos dados preenchidos no formulário.',
                    'errors'  => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $newDueDate   = Carbon::parse($request->due_date);
            $newAmount    = (float) $request->amount;
            $type         = $request->input('type', $contribution->type ?? 'mensal');
            $startDate    = $request->input('start_date', $contribution->start_date);
            $endDate      = $type === 'periodo' ? $request->input('end_date') : null;

            $paymentMethod = match ($request->payment_method) {
                'boleto_bancario' => PaymentMethodEnum::BankSlip->value,
                'pix'             => PaymentMethodEnum::Pix->value,
                default           => PaymentMethodEnum::BankSlip->value,
            };

            // Se existir cobrança ativa no Asaas, envia atualização
            if (!empty($contribution->asaas_payment_id)) {
                $paymentData = [
                    'value'       => $newAmount,
                    'dueDate'     => $newDueDate->format('Y-m-d'),
                    'description' => $request->input('description', $contribution->description),
                    'billingType' => $request->payment_method === 'pix' ? 'PIX' : 'BOLETO',
                ];

                $asaasResponse = $this->asaasPaymentService->updateCharge($contribution->asaas_payment_id, $paymentData);

                if (!empty($asaasResponse['id'])) {
                    $contribution->asaas_boleto_url  = $asaasResponse['bankSlipUrl'] ?? $contribution->asaas_boleto_url;
                    $contribution->asaas_pix_qr_code = $asaasResponse['pixQrCode'] ?? $asaasResponse['qrCode'] ?? $contribution->asaas_pix_qr_code;
                    $contribution->asaas_pix_payload = $asaasResponse['encodedValue'] ?? $asaasResponse['payload'] ?? $contribution->asaas_pix_payload;
                }
            }

            // Atualiza registros locais
            $contribution->community_id      = $request->community_id;
            $contribution->contribution_date = $newDueDate;
            $contribution->amount            = $newAmount;
            $contribution->payment_method    = $paymentMethod;

            if (\Illuminate\Support\Facades\Schema::hasColumn('contributions', 'due_date')) {
                $contribution->due_date = $newDueDate;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('contributions', 'type')) {
                $contribution->type = $type;
            }
            if ($startDate && \Illuminate\Support\Facades\Schema::hasColumn('contributions', 'start_date')) {
                $contribution->start_date = $startDate;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('contributions', 'end_date')) {
                $contribution->end_date = $endDate;
            }
            if ($request->has('description') && \Illuminate\Support\Facades\Schema::hasColumn('contributions', 'description')) {
                $contribution->description = $request->description;
            }

            $contribution->save();

            // Retorno para AJAX
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Contribuição atualizada e reemitida com sucesso!',
                    'bankSlipUrl' => $contribution->asaas_boleto_url,
                    'pixQrCode'   => $contribution->asaas_pix_qr_code,
                    'pixPayload'  => $contribution->asaas_pix_payload,
                    'redirect'    => route('member.contributions.index'),
                ], 200);
            }

            return redirect()->route('member.contributions.index')->with('success', 'Contribuição atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar contribuição: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar cobrança: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Erro ao atualizar contribuição: ' . $e->getMessage());
        }
    }

    /**
     * Exibe a view com o QR Code do Pix (pix_qrcode.blade.php)
     */
    public function showPix(Contribution $contribution)
    {
        $member = Auth::user()->member;
        if (!$member || $contribution->member_id !== $member->id) {
            abort(403);
        }

        $pixCode = $contribution->asaas_pix_payload ?? $contribution->asaas_pix_qr_code;

        // Se o Pix estiver vazio no banco, busca direto no Asaas via API e salva
        if (empty($pixCode) && !empty($contribution->asaas_payment_id)) {
            try {
                $pixData = $this->asaasPaymentService->getPixQrCode($contribution->asaas_payment_id);
                if (!empty($pixData['payload']) || !empty($pixData['encodedImage'])) {
                    $pixCode = $pixData['payload'] ?? $pixData['encodedImage'];

                    $contribution->asaas_pix_qr_code = $pixData['encodedImage'] ?? $contribution->asaas_pix_qr_code;
                    $contribution->asaas_pix_payload = $pixData['payload'] ?? $contribution->asaas_pix_payload;
                    $contribution->save();
                }
            } catch (\Exception $e) {
                Log::error('Erro ao buscar QR Code Pix na view showPix: ' . $e->getMessage());
            }
        }

        return view('member.contributions.pix_qrcode', compact('contribution', 'pixCode'));
    }

    /**
     * Redireciona diretamente para a URL do PDF do Boleto no Asaas
     */
    public function showBoleto(Contribution $contribution)
    {
        $member = Auth::user()->member;
        if (!$member || $contribution->member_id !== $member->id) {
            abort(403);
        }

        if (empty($contribution->asaas_boleto_url)) {
            return redirect()->route('member.contributions.index')->with('error', 'URL do boleto não encontrada.');
        }

        return redirect()->away($contribution->asaas_boleto_url);
    }
}