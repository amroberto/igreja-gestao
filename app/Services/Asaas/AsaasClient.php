<?php

namespace App\Services\Asaas;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AsaasClient
{
    protected string $apiUrl;
    protected string $apiKey;
    protected Client $client;

    public function __construct()
    {
        $this->apiUrl = config('services.asaas.api_url', 'https://sandbox.asaas.com/api/v3');
        $this->apiKey = config('services.asaas.api_key');
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'timeout' => 10.0,
        ]);

        Log::debug('Inicializando AsaasClient', [
            'apiUrl' => $this->apiUrl,
            'apiKey' => substr($this->apiKey, 0, 4) . '****',
        ]);
    }

    protected function headers(): array
    {
        return [
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'access_token' => $this->apiKey,
        ];
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getHeaders(): array
    {
        return $this->headers();
    }
}
