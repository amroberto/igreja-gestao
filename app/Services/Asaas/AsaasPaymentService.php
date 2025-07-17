<?php

namespace App\Services\Asaas;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class AsaasPaymentService
{
    protected Client $client;
    protected array $headers;

    public function __construct(AsaasClient $asaasClient)
    {
        $this->client = $asaasClient->getClient();
        $this->headers = $asaasClient->getHeaders();
    }

    public function createPayment(array $payload): array
    {
        try {
            $response = $this->client->post('/v3/payments', [
                'headers' => $this->headers,
                'json' => array_filter($payload, fn($value) => !is_null($value)),
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (!isset($responseData['id'])) {
                throw new \Exception('Erro ao criar pagamento no Asaas: resposta inválida.');
            }

            return $responseData;
        } catch (RequestException $e) {
            $errorResponse = $e->hasResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : null;
            Log::error('Erro ao criar pagamento no Asaas', [
                'message' => $e->getMessage(),
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'N/A',
                'body' => $errorResponse ?? 'Sem corpo na resposta',
            ]);
            throw new \Exception('Falha ao criar pagamento no Asaas: ' . $e->getMessage());
        }
    }

    public function createPixCharge(array $contribution): array
    {
        $payload = [
            'customer' => $contribution['customer_id'],
            'billingType' => 'PIX',
            'value' => $contribution['amount'],
            'dueDate' => now()->addDays(1)->format('Y-m-d'),
            'description' => $contribution['description'] ?? 'Contribuição',
            'externalReference' => $contribution['id'] ?? null,
        ];

        return $this->createPayment($payload);
    }

    public function createBoletoCharge(array $contribution, string $dueDate): array
    {
        $payload = [
            'customer' => $contribution['customer_id'],
            'billingType' => 'BOLETO',
            'value' => $contribution['amount'],
            'dueDate' => $dueDate,
            'description' => $contribution['description'] ?? 'Contribuição',
            'externalReference' => $contribution['id'] ?? null,
        ];

        return $this->createPayment($payload);
    }

    public function payWithCreditCard(array $contribution, array $card): array
    {
        $payload = [
            'customer' => $contribution['customer_id'],
            'billingType' => 'CREDIT_CARD',
            'value' => $contribution['amount'],
            'dueDate' => now()->addDays(1)->format('Y-m-d'),
            'description' => $contribution['description'] ?? 'Contribuição',
            'externalReference' => $contribution['id'] ?? null,
            'creditCardToken' => $card['token'],
        ];

        return $this->createPayment($payload);
    }
}
