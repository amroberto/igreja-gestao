<?php
namespace App\Services\Asaas;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AsaasClient
{
    protected $client;
    protected $apiKey;
    public $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.asaas.api_key');
        $this->baseUrl = config('services.asaas.base_url', 'https://sandbox.asaas.com/api/v3');

        if (empty($this->apiKey)) {
            Log::error('Chave API do Asaas não configurada');
            throw new \Exception('Chave API do Asaas não configurada');
        }

        $this->client = new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout' => 10.0,
            'http_errors' => false,
        ]);

        Log::debug('Inicializando AsaasClient', [
            'apiUrl' => $this->baseUrl,
            'apiKey' => substr($this->apiKey, 0, 6) . '****',
        ]);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'access_token' => $this->apiKey,
        ];
    }
}