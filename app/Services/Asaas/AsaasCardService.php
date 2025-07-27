<?php
namespace App\Services\Asaas;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AsaasCardService
{
    protected $apiKey;
    protected $baseUrl;
    protected $customerService;

    public function __construct(AsaasCustomerService $customerService)
    {
        $this->apiKey = config('services.asaas.api_key');
        $this->baseUrl = config('services.asaas.base_url');
        $this->customerService = $customerService;
    }

    public function createCharge(string $customerId, array $paymentData): array
    {
        try {
            if (!$this->customerService->verifyCustomer($customerId)) {
                Log::error('Customer ID inválido ou não encontrado ao criar cobrança', [
                    'customer_id' => $customerId,
                ]);
                throw new \Exception('Customer ID inválido ou não encontrado');
            }

            $requiredFields = ['value', 'dueDate', 'description', 'billingType'];
            $missingFields = array_filter($requiredFields, fn($field) => empty($paymentData[$field]));
            if (!empty($missingFields)) {
                Log::error('Campos obrigatórios ausentes ao criar cobrança', [
                    'customer_id' => $customerId,
                    'missing_fields' => $missingFields,
                ]);
                throw new \Exception('Campos obrigatórios ausentes: ' . implode(', ', $missingFields));
            }

            if (!in_array($paymentData['billingType'], ['BOLETO', 'PIX', 'CREDIT_CARD'])) {
                Log::error('Tipo de cobrança inválido', [
                    'customer_id' => $customerId,
                    'billingType' => $paymentData['billingType'],
                ]);
                throw new \Exception('Tipo de cobrança inválido');
            }

            $requestBody = [
                'customer' => $customerId,
                'billingType' => $paymentData['billingType'],
                'value' => (float) $paymentData['value'],
                'dueDate' => $paymentData['dueDate'], // Formato: YYYY-MM-DD
                'description' => $paymentData['description'],
                'externalReference' => $paymentData['externalReference'] ?? null,
            ];

            if ($paymentData['billingType'] === 'CREDIT_CARD') {
                $requiredCardFields = ['holderName', 'number', 'expiryMonth', 'expiryYear', 'ccv'];
                $missingCardFields = array_filter($requiredCardFields, fn($field) => empty($paymentData['creditCard'][$field]));
                if (!empty($missingCardFields)) {
                    Log::error('Campos de cartão obrigatórios ausentes', [
                        'customer_id' => $customerId,
                        'missing_fields' => $missingCardFields,
                    ]);
                    throw new \Exception('Campos de cartão obrigatórios ausentes: ' . implode(', ', $missingCardFields));
                }

                $requestBody['creditCard'] = [
                    'holderName' => $paymentData['creditCard']['holderName'],
                    'number' => $paymentData['creditCard']['number'],
                    'expiryMonth' => $paymentData['creditCard']['expiryMonth'],
                    'expiryYear' => $paymentData['creditCard']['expiryYear'],
                    'ccv' => $paymentData['creditCard']['ccv'],
                ];

                $requestBody['creditCardHolderInfo'] = [
                    'name' => $paymentData['creditCard']['holderInfo']['name'],
                    'cpfCnpj' => preg_replace('/\D/', '', $paymentData['creditCard']['holderInfo']['cpfCnpj']),
                    'email' => $paymentData['creditCard']['holderInfo']['email'] ?? null,
                    'postalCode' => $paymentData['creditCard']['holderInfo']['postalCode'],
                    'addressNumber' => $paymentData['creditCard']['holderInfo']['addressNumber'],
                    'addressComplement' => $paymentData['creditCard']['holderInfo']['addressComplement'] ?? null,
                    'phone' => $paymentData['creditCard']['holderInfo']['phone'],
                    'mobilePhone' => $paymentData['creditCard']['holderInfo']['mobilePhone'] ?? null,
                ];
            }

            Log::info('Enviando requisição para criar cobrança no Asaas', [
                'customer_id' => $customerId,
                'url' => "{$this->baseUrl}/api/v3/payments",
                'request_body' => array_merge($requestBody, [
                    'creditCard' => isset($requestBody['creditCard']) ? array_merge($requestBody['creditCard'], [
                        'number' => substr($requestBody['creditCard']['number'], 0, 4) . '****',
                        'ccv' => '***',
                    ]) : null,
                ]),
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->post("{$this->baseUrl}/api/v3/payments", $requestBody);

            Log::info('Resposta da API do Asaas (createCharge)', [
                'customer_id' => $customerId,
                'status' => $response->status(),
                'response' => $response->json(),
                'body' => $response->body(),
            ]);

            if ($response->failed()) {
                Log::error('Falha na requisição para Asaas (createCharge)', [
                    'customer_id' => $customerId,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                throw new \Exception('Erro na API do Asaas: ' . $response->body());
            }

            $data = $response->json();

            Log::info('Cobrança criada com sucesso no Asaas', [
                'customer_id' => $customerId,
                'payment_id' => $data['id'],
                'billing_type' => $paymentData['billingType'],
                'boleto_url' => $data['bankSlipUrl'] ?? 'N/A',
                'pix_qr_code' => $data['encodedImage'] ?? 'N/A',
                'pix_copy_paste' => $data['payload'] ?? 'N/A',
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('Erro ao criar cobrança no Asaas', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Erro ao criar cobrança: ' . $e->getMessage());
        }
    }
}
