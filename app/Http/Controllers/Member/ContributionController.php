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
            // Validação dos dados
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

            // Verificar se member_asaas_id existe
            if (!$member->member_asaas_id) {
                throw new \Exception('ID do cliente Asaas não encontrado para o membro.');
            }

            // Criar cobrança no Asaas
            $paymentData = [
                'customer' => $member->member_asaas_id,
                'value' => (float) $validated['amount'],
                'description' => $validated['description'] ?? 'Contribuição de ' . $member->name,
            ];
            // vencimento em 3 dias úteis
            $dueDate = now()->addDays(3)->format('Y-m-d');

            // Selecionar método de pagamento
            $asaasResponse = null;
            if ($validated['payment_method'] === 'boleto_bancario') {
                $paymentData['billingType'] = 'BOLETO';
                $asaasResponse = $this->asaasPaymentService->createBoletoCharge($member, $paymentData, $dueDate);
            } elseif ($validated['payment_method'] === 'pix') {
                $paymentData['billingType'] = 'PIX';
                $asaasResponse = $this->asaasPaymentService->createPixCharge($member, $paymentData, $dueDate);
                // Log detalhado da resposta bruta
                Log::debug('Resposta completa do Asaas para cobrança PIX', [
                    'asaas_response' => $asaasResponse,
                    'pixQrCode' => $asaasResponse['pixQrCode'] ?? $asaasResponse['qrCode'] ?? $asaasResponse['pix']['qrCode'] ?? 'N/A',
                    'encodedValue' => $asaasResponse['encodedValue'] ?? $asaasResponse['payload'] ?? $asaasResponse['pix']['payload'] ?? 'N/A',
                ]);
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

            // Mapear payment_method para o valor do enum
            $paymentMethod = match ($validated['payment_method']) {
                'boleto_bancario' => PaymentMethodEnum::BankSlip->value,
                'pix' => PaymentMethodEnum::Pix->value,
                'creditcard' => PaymentMethodEnum::CreditCard->value,
                default => throw new \Exception('Método de pagamento inválido: ' . $validated['payment_method']),
            };

            // Log dos dados antes de salvar no banco
            Log::debug('Preparando para salvar contribuição no banco', [
                'member_id' => $member->id,
                'asaas_response' => $asaasResponse,
                'payment_method' => $paymentMethod,
                'asaas_pix_qr_code' => $asaasResponse['pixQrCode'] ?? $asaasResponse['qrCode'] ?? $asaasResponse['pix']['qrCode'] ?? null,
                'asaas_pix_payload' => $asaasResponse['encodedValue'] ?? $asaasResponse['payload'] ?? $asaasResponse['pix']['payload'] ?? null,
            ]);

            // Salvar no banco
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

            Log::info('Contribuição criada com sucesso', [
                'contribution_id' => $payment->id,
                'asaas_payment_id' => $asaasResponse['id'] ?? 'N/A',
                'member_id' => $member->id,
                'bankSlipUrl' => $asaasResponse['bankSlipUrl'] ?? 'N/A',
                'pixQrCode' => $asaasResponse['pixQrCode'] ?? $asaasResponse['qrCode'] ?? $asaasResponse['pix']['qrCode'] ?? 'N/A',
                'pixPayload' => $asaasResponse['encodedValue'] ?? $asaasResponse['payload'] ?? $asaasResponse['pix']['payload'] ?? 'N/A',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contribuição criada com sucesso',
                'bankSlipUrl' => $asaasResponse['bankSlipUrl'] ?? null,
                'pixQrCode' => $asaasResponse['pixQrCode'] ?? $asaasResponse['qrCode'] ?? $asaasResponse['pix']['qrCode'] ?? null,
                'pixPayload' => $asaasResponse['encodedValue'] ?? $asaasResponse['payload'] ?? $asaasResponse['pix']['payload'] ?? null,
            ], 201);
        } catch (ValidationException $e) {
            Log::error('Erro de validação ao criar contribuição', [
                'errors' => $e->errors(),
                'validated' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar contribuição', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'validated' => $request->all(),
                'asaas_response' => isset($asaasResponse) ? $asaasResponse : 'N/A',
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar contribuição: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function showPix(Contribution $contribution)
    {
        // Garante que o usuário só veja o PIX da própria contribuição
        if ($contribution->user_id !== auth()->id()) {
            abort(403);
        }

        $pixCode = $contribution->asaas_pix_qr_code;
        $pixPayload = $contribution->asaas_pix_payload;

        return view('member.contributions.pix', compact('contribution', 'pixCode', 'pixPayload'));
    }
}
