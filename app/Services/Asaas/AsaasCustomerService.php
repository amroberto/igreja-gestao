<?php

namespace App\Services\Asaas;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AsaasCustomerService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.asaas.api_key');
        $this->baseUrl = config('services.asaas.base_url');
        Log::debug('Inicializando AsaasCustomerService', [
            'base_url' => $this->baseUrl,
            'api_key' => substr($this->apiKey, 0, 10) . '...',
        ]);
    }

    public function createCustomer(array $data)
    {
        Log::info('Enviando requisição para criar cliente no Asaas', [
            'data' => array_merge($data, ['cpfCnpj' => substr($data['cpfCnpj'], 0, 4) . '****']),
            'url' => "{$this->baseUrl}/customers",
            'access_token' => substr($this->apiKey, 0, 10) . '...',
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'access_token' => $this->apiKey,
        ])->post("{$this->baseUrl}/customers", $data);

        Log::info('Resposta da API do Asaas (create)', [
            'status' => $response->status(),
            'response' => $response->json(),
            'body' => substr($response->body(), 0, 1000),
        ]);

        if ($response->failed()) {
            Log::error('Falha na requisição para Asaas (create)', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            throw new Exception('Erro na API do Asaas: ' . $response->body());
        }

        return $response->json();
    }

    public function updateCustomer(string $customerId, array $data)
    {
        Log::info('Enviando requisição para atualizar cliente no Asaas', [
            'customer_id' => $customerId,
            'data' => array_merge($data, ['cpfCnpj' => isset($data['cpfCnpj']) ? substr($data['cpfCnpj'], 0, 4) . '****' : 'N/A']),
            'url' => "{$this->baseUrl}/customers/{$customerId}",
            'access_token' => substr($this->apiKey, 0, 10) . '...',
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'access_token' => $this->apiKey,
        ])->put("{$this->baseUrl}/customers/{$customerId}", $data);

        Log::info('Resposta da API do Asaas (update)', [
            'status' => $response->status(),
            'response' => $response->json(),
            'body' => substr($response->body(), 0, 1000),
        ]);

        if ($response->failed()) {
            Log::error('Falha na requisição para Asaas (update)', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            throw new Exception('Erro na API do Asaas: ' . $response->body());
        }

        return $response->json();
    }

    public function verifyCustomer(string $customerId): bool
    {
        Log::info('Verificando customer no Asaas', [
            'customer_id' => $customerId,
            'url' => "{$this->baseUrl}/customers/{$customerId}",
            'access_token' => substr($this->apiKey, 0, 10) . '...',
        ]);

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'access_token' => $this->apiKey,
            ])->get("{$this->baseUrl}/customers/{$customerId}");

            Log::debug('Resposta da API do Asaas (verify)', [
                'customer_id' => $customerId,
                'status' => $response->status(),
                'response' => $response->json(),
                'body' => substr($response->body(), 0, 1000),
            ]);

            return $response->status() === 200 && is_array($response->json()) && isset($response->json()['id']);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar customer no Asaas', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}