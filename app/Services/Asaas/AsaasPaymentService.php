<?php

namespace App\Services\Asaas;

use Exception;
use App\Models\Member;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AsaasPaymentService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.asaas.api_key');
        $this->baseUrl = config('services.asaas.base_url', 'https://api-sandbox.asaas.com/v3');
        Log::debug('Inicializando AsaasPaymentService', [
            'base_url' => $this->baseUrl,
            'api_key' => substr($this->apiKey, 0, 10) . '...',
        ]);

        if (empty($this->apiKey)) {
            Log::error('Chave da API Asaas está vazia');
            throw new \Exception('Chave da API Asaas não configurada.');
        }
    }

    public function createBoletoCharge($member, $paymentData, $dueDate)
    {
        try {
            $payload = [
                'customer' => $paymentData['customer'],
                'billingType' => 'BOLETO',
                'value' => (float) $paymentData['value'],
                'dueDate' => $dueDate,
                'description' => $paymentData['description'] ?? null,
            ];

            Log::debug('Criando cobrança de boleto com payload', [
                'payload' => $payload,
                'url' => "{$this->baseUrl}/payments",
                'member_id' => $member->id,
                'access_token' => substr($this->apiKey, 0, 10) . '...',
            ]);

            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/payments", $payload);

            Log::debug('Resposta da API do Asaas (createBoleto)', [
                'status_code' => $response->status(),
                'headers' => $response->headers(),
                'body' => substr($response->body(), 0, 1000),
            ]);

            if (!$response->json()) {
                Log::error('Resposta da API Asaas não é JSON válida', [
                    'member_id' => $member->id,
                    'status_code' => $response->status(),
                    'body' => substr($response->body(), 0, 1000),
                ]);
                throw new \Exception('Resposta da API Asaas não é JSON válida: ' . substr($response->body(), 0, 1000));
            }

            if ($response->failed()) {
                Log::error('Erro ao criar cobrança de boleto', [
                    'member_id' => $member->id,
                    'status_code' => $response->status(),
                    'response' => $response->json(),
                ]);
                throw new \Exception('Erro na API do Asaas: ' . json_encode($response->json()));
            }

            $data = $response->json();
            Log::info('Cobrança de boleto criada com sucesso', [
                'payment_id' => $data['id'],
                'member_id' => $member->id,
            ]);
            return $data;
        } catch (\Exception $e) {
            Log::error('Exceção em createBoletoCharge', [
                'member_id' => $member->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function createPixCharge(Member $member, array $paymentData, $dueDate): array
    {
        try {
            $payload = [
                'customer' => $member->member_asaas_id,
                'billingType' => 'PIX',
                'value' => (float) $paymentData['value'],
                'dueDate' => $dueDate,
                'description' => $paymentData['description'] ?? null,
                'cpfCnpj' => $member->cpf, // Incluído para consistência com a documentação
            ];

            Log::info('Enviando requisição para criar cobrança Pix no Asaas', [
                'data' => array_merge($payload, ['cpfCnpj' => substr($member->cpf, 0, 4) . '****']),
                'member_id' => $member->id,
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->post("{$this->baseUrl}/payments", $payload);

            Log::info('Resposta da API do Asaas (create Pix)', [
                'status' => $response->status(),
                'response' => $response->json(),
                'body' => substr($response->body(), 0, 1000),
            ]);

            if ($response->failed()) {
                Log::error('Falha na requisição para criar Pix no Asaas', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'member_id' => $member->id,
                ]);
                throw new Exception('Erro na API do Asaas: ' . ($response->json()['errors'][0]['description'] ?? 'Erro desconhecido'));
            }

            $data = $response->json();
            $paymentId = $data['id'];

            // Buscar QR Code e payload no endpoint /payments/{id}/pixQrCode
            Log::debug('Buscando QR Code PIX para pagamento', [
                'payment_id' => $paymentId,
                'url' => "{$this->baseUrl}/payments/{$paymentId}/pixQrCode",
                'member_id' => $member->id,
            ]);

            $pixResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->get("{$this->baseUrl}/payments/{$paymentId}/pixQrCode");

            if ($pixResponse->successful()) {
                $pixData = $pixResponse->json();
                Log::debug('Resposta da API do Asaas para QR Code PIX', [
                    'payment_id' => $paymentId,
                    'pix_response' => $pixData,
                    'encodedImage' => $pixData['encodedImage'] ?? 'N/A',
                    'payload' => $pixData['payload'] ?? 'N/A',
                ]);

                // Mapear os campos para o formato esperado pelo ContributionController
                $data['pixQrCode'] = $pixData['encodedImage'] ?? $pixData['pixQrCode'] ?? null;
                $data['encodedValue'] = $pixData['payload'] ?? $pixData['encodedValue'] ?? null;
            } else {
                Log::error('Erro ao buscar QR Code PIX no Asaas', [
                    'payment_id' => $paymentId,
                    'status' => $pixResponse->status(),
                    'response' => $pixResponse->body(),
                    'member_id' => $member->id,
                ]);
                // Continuar com pixQrCode e encodedValue como null para não interromper o fluxo
                $data['pixQrCode'] = null;
                $data['encodedValue'] = null;
            }

            return [
                'id' => $data['id'],
                'pixQrCode' => $data['pixQrCode'] ?? null,
                'encodedValue' => $data['encodedValue'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Exceção em createPixCharge', [
                'member_id' => $member->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function createCreditCardCharge(Member $member, array $paymentData, string $dueDate): array
    {
        try {
            $payload = [
                'customer' => $member->member_asaas_id,
                'billingType' => 'CREDIT_CARD',
                'value' => (float) $paymentData['value'],
                'dueDate' => $dueDate,
                'description' => $paymentData['description'] ?? null,
            ];

            Log::info('Enviando requisição para criar cobrança com cartão de crédito no Asaas', [
                'data' => array_merge($payload, ['cpfCnpj' => substr($member->cpf, 0, 4) . '****']),
                'member_id' => $member->id,
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->post("{$this->baseUrl}/payments", $payload);

            Log::info('Resposta da API do Asaas (create credit card)', [
                'status' => $response->status(),
                'response' => $response->json(),
                'body' => substr($response->body(), 0, 1000),
            ]);

            if ($response->failed()) {
                Log::error('Falha na requisição para criar pagamento com cartão de crédito no Asaas', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'member_id' => $member->id,
                ]);
                throw new Exception('Erro na API do Asaas: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Exceção em createCreditCardCharge', [
                'member_id' => $member->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function payWithCreditCard(Member $member, array $paymentData, array $creditCardData): array
    {
        try {
            $payload = [
                'customer' => $member->member_asaas_id,
                'billingType' => 'CREDIT_CARD',
                'value' => (float) $paymentData['value'],
                'dueDate' => date('Y-m-d'),
                'description' => $paymentData['description'] ?? null,
                'creditCard' => [
                    'holderName' => $creditCardData['holderName'],
                    'number' => $creditCardData['number'],
                    'expiryMonth' => sprintf('%02d', $creditCardData['expiryMonth']),
                    'expiryYear' => $creditCardData['expiryYear'],
                    'ccv' => $creditCardData['ccv'],
                ],
                'creditCardHolderInfo' => $creditCardData['holderInfo'],
            ];

            Log::info('Enviando requisição para criar pagamento com cartão de crédito no Asaas', [
                'data' => array_merge($payload, [
                    'creditCard' => [
                        'holderName' => $payload['creditCard']['holderName'],
                        'number' => substr($payload['creditCard']['number'], 0, 4) . '****' . substr($payload['creditCard']['number'], -4),
                        'expiryMonth' => $payload['creditCard']['expiryMonth'],
                        'expiryYear' => $payload['creditCard']['expiryYear'],
                        'ccv' => '***',
                    ],
                    'cpfCnpj' => substr($member->cpf, 0, 4) . '****',
                ]),
                'member_id' => $member->id,
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->post("{$this->baseUrl}/payments", $payload);

            Log::info('Resposta da API do Asaas (create credit card)', [
                'status' => $response->status(),
                'response' => $response->json(),
                'body' => substr($response->body(), 0, 1000),
            ]);

            if ($response->failed()) {
                Log::error('Falha na requisição para criar pagamento com cartão de crédito no Asaas', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'member_id' => $member->id,
                ]);
                throw new Exception('Erro na API do Asaas: ' . $response->body());
            }

            $data = $response->json();
            return [
                'id' => $data['id'],
                'cardId' => $data['creditCard']['cardId'] ?? null,
                'lastDigits' => $data['creditCard']['lastDigits'] ?? substr($creditCardData['number'], -4),
                'brand' => $data['creditCard']['brand'] ?? 'Unknown',
            ];
        } catch (\Exception $e) {
            Log::error('Exceção em payWithCreditCard', [
                'member_id' => $member->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function tokenizeCreditCard(Member $member, array $creditCardData): array
    {
        try {
            $payload = [
                'customer' => $member->member_asaas_id,
                'creditCard' => [
                    'holderName' => $creditCardData['holderName'],
                    'number' => $creditCardData['number'],
                    'expiryMonth' => sprintf('%02d', $creditCardData['expiryMonth']),
                    'expiryYear' => $creditCardData['expiryYear'],
                    'ccv' => $creditCardData['ccv'],
                ],
                'creditCardHolderInfo' => $creditCardData['holderInfo'],
            ];

            Log::info('Enviando requisição para tokenizar cartão no Asaas', [
                'data' => array_merge($payload, [
                    'creditCard' => [
                        'holderName' => $payload['creditCard']['holderName'],
                        'number' => substr($payload['creditCard']['number'], 0, 4) . '****' . substr($payload['creditCard']['number'], -4),
                        'expiryMonth' => $payload['creditCard']['expiryMonth'],
                        'expiryYear' => $payload['creditCard']['expiryYear'],
                        'ccv' => '***',
                    ],
                    'cpfCnpj' => substr($member->cpf, 0, 4) . '****',
                ]),
                'member_id' => $member->id,
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->post("{$this->baseUrl}/creditCard/tokenize", $payload);

            Log::info('Resposta da API do Asaas (tokenize credit card)', [
                'status' => $response->status(),
                'response' => $response->json(),
                'body' => substr($response->body(), 0, 1000),
            ]);

            if ($response->failed()) {
                Log::error('Falha na requisição para tokenizar cartão no Asaas', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'member_id' => $member->id,
                ]);
                throw new Exception('Erro na API do Asaas: ' . ($response->json()['errors'][0]['description'] ?? 'Erro desconhecido'));
            }

            $data = $response->json();
            return [
                'cardId' => $data['creditCardToken'],
                'lastDigits' => $data['lastDigits'] ?? substr($creditCardData['number'], -4),
                'brand' => $data['brand'] ?? 'Unknown',
                'expirationDate' => sprintf('%02d/%s', $creditCardData['expiryMonth'], substr($creditCardData['expiryYear'], -2)),
            ];
        } catch (\Exception $e) {
            Log::error('Exceção em tokenizeCreditCard', [
                'member_id' => $member->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
