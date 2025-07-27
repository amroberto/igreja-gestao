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

    /**
     * Create a Pix charge in Asaas.
     *
     * @param Member $member
     * @param array $payload
     * @return array
     * @throws Exception
     */
    public function createPixCharge(Member $member, array $payload): array
    {
        $payload = array_merge($payload, [
            'billingType' => 'PIX',
            'customer' => $member->member_asaas_id,
        ]);

        Log::info('Enviando requisição para criar cobrança Pix no Asaas', [
            'data' => array_merge($payload, ['cpfCnpj' => isset($payload['cpfCnpj']) ? substr($payload['cpfCnpj'], 0, 4) . '****' : 'N/A']),
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'access_token' => $this->apiKey,
        ])->post("{$this->baseUrl}/payments", $payload);

        Log::info('Resposta da API do Asaas (create Pix)', [
            'status' => $response->status(),
            'response' => $response->json(),
            'body' => $response->body(),
        ]);

        if ($response->failed()) {
            Log::error('Falha na requisição para criar Pix no Asaas', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            throw new Exception('Erro na API do Asaas: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Create a credit card payment in Asaas.
     *
     * @param Member $member
     * @param array $payload
     * @return array
     * @throws Exception
     */
    public function payWithCreditCard(Member $member, array $payload): array
    {
        $payload = array_merge($payload, [
            'billingType' => 'CREDIT_CARD',
            'customer' => $member->member_asaas_id,
        ]);

        Log::info('Enviando requisição para criar pagamento com cartão de crédito no Asaas', [
            'data' => array_merge($payload, ['cpfCnpj' => isset($payload['cpfCnpj']) ? substr($payload['cpfCnpj'], 0, 4) . '****' : 'N/A']),
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'access_token' => $this->apiKey,
        ])->post("{$this->baseUrl}/payments", $payload);

        Log::info('Resposta da API do Asaas (create credit card)', [
            'status' => $response->status(),
            'response' => $response->json(),
            'body' => $response->body(),
        ]);

        if ($response->failed()) {
            Log::error('Falha na requisição para criar pagamento com cartão de crédito no Asaas', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            throw new Exception('Erro na API do Asaas: ' . $response->body());
        }

        return $response->json();
    }
}
