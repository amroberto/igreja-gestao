<?php

namespace App\Services\Asaas;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasCustomerService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.asaas.api_key');
        $this->baseUrl = config('services.asaas.base_url');
    }

    public function createCustomer(array $data)
    {
        Log::info('Enviando requisição para criar cliente no Asaas', [
            'data' => array_merge($data, ['cpfCnpj' => substr($data['cpfCnpj'], 0, 4) . '****'])
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'access_token' => $this->apiKey,
        ])->post("{$this->baseUrl}/api/v3/customers", $data);

        Log::info('Resposta da API do Asaas (create)', [
            'status' => $response->status(),
            'response' => $response->json(),
            'body' => $response->body(),
        ]);

        if ($response->failed()) {
            Log::error('Falha na requisição para Asaas (create)', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            throw new \Exception('Erro na API do Asaas: ' . $response->body());
        }

        return $response->json();
    }

    public function updateCustomer(string $customerId, array $data)
    {
        Log::info('Enviando requisição para atualizar cliente no Asaas', [
            'customer_id' => $customerId,
            'data' => array_merge($data, ['cpfCnpj' => isset($data['cpfCnpj']) ? substr($data['cpfCnpj'], 0, 4) . '****' : 'N/A'])
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'access_token' => $this->apiKey,
        ])->put("{$this->baseUrl}/api/v3/customers/{$customerId}", $data);

        Log::info('Resposta da API do Asaas (update)', [
            'status' => $response->status(),
            'response' => $response->json(),
            'body' => $response->body(),
        ]);

        if ($response->failed()) {
            Log::error('Falha na requisição para Asaas (update)', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            throw new \Exception('Erro na API do Asaas: ' . $response->body());
        }

        return $response->json();
    }
}