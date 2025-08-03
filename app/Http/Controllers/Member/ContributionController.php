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
                'contribution_date' => 'required|date',
                'period_type' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date',
                'payment_method' => 'required|string|in:BankSlip,Pix,CreditCard',
                'amount' => 'required|numeric',
                'description' => 'nullable|string',
            ]);

            $member = Member::findOrFail($validated['member_id']);
            $asaasCustomerService = new AsaasCustomerService();
            $asaasPaymentService = new AsaasPaymentService();

            // Verificar ou criar cliente no Asaas
            if (!$member->member_asaas_id || !$asaasCustomerService->verifyCustomer($member->member_asaas_id)) {
                $asaasData = [
                    'name' => $member->name,
                    'email' => $member->email,
                    'cpfCnpj' => $member->cpf,
                    'postalCode' => $member->cep,
                    'address' => $member->address,
                    'addressNumber' => $member->number,
                    'phone' => $member->phone,
                    'mobilePhone' => $member->cellphone,
                    'externalReference' => (string) $member->id,
                ];
                $asaasCustomer = $asaasCustomerService->createCustomer($asaasData);
                $member->member_asaas_id = $asaasCustomer['id'];
                $member->save();
            }

            // Criar cobrança no Asaas
            $paymentData = [
                'customer' => $member->member_asaas_id,
                'value' => $validated['amount'],
                'description' => $validated['description'],
            ];
            $dueDate = $validated['contribution_date'];
            
            // se método de pagamento dor boleto, cria boleto no Asaas
            if ($validated['payment_method'] === 'BankSlip') {
                $paymentData['billingType'] = 'BOLETO';
                $asaasResponse = $asaasPaymentService->createBoletoCharge($member, $paymentData, $dueDate);
            } elseif ($validated['payment_method'] === 'Pix') {
                $paymentData['billingType'] = 'PIX';
                $asaasResponse = $asaasPaymentService->createPixCharge($member, $paymentData, $dueDate);
            } elseif ($validated['payment_method'] === 'CreditCard') {
                $paymentData['billingType'] = 'CREDIT_CARD';
                $asaasResponse = $asaasPaymentService->createCreditCardCharge($member, $paymentData, $dueDate);
            } else {
                throw new \Exception('Método de pagamento inválido: ' . $validated['payment_method']);
            }

            //$asaasResponse = $asaasPaymentService->createBoletoCharge($member, $paymentData, $dueDate);

            // Mapear payment_method para o valor do enum
            $paymentMethod = match ($validated['payment_method']) {
                'BankSlip' => PaymentMethodEnum::BankSlip->value,
                'Pix' => PaymentMethodEnum::Pix->value,
                'CreditCard' => PaymentMethodEnum::CreditCard->value,
                default => throw new \Exception('Método de pagamento inválido: ' . $validated['payment_method']),
            };

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
                'asaas_payment_id' => $asaasResponse['id'],
                'asaas_boleto_url' => $asaasResponse['bankSlipUrl'],
                'asaas_pix_code' => $asaasResponse['pixCode'] ?? null,
                'asaas_pix_qr_code' => $asaasResponse['pixQrCode'] ?? null,
                'description' => $validated['description'],
                'created_at' => now(),
            ]);
            DB::commit();

            Log::info('Contribuição criada com sucesso', [
                'contribution_id' => $payment->id,
                'asaas_payment_id' => $asaasResponse['id'],
                'member_id' => $member->id,
                'bankSlipUrl' => $asaasResponse['bankSlipUrl'] ?? 'N/A',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contribuição criada com sucesso',
                'data' => $payment,
                'bankSlipUrl' => $asaasResponse['bankSlipUrl'] ?? null,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar contribuição', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'validated' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar contribuição: ' . $e->getMessage(),
            ], 500);
        }
    }
}
