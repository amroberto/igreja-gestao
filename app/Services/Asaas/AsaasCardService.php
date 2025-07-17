<?php

namespace App\Services\Asaas;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class AsaasCardService
{
    protected Client $client;
    protected array $headers;

    public function __construct(AsaasClient $asaasClient)
    {
        $this->client = $asaasClient->getClient();
        $this->headers = $asaasClient->getHeaders();
    }

    public function storeCard(array $data): array
    {
        try {
            if (empty($data['customer']) || empty($data['creditCardToken'])) {
                throw new \Exception('Parâmetros obrigatórios ausentes: customer ou creditCardToken.');
            }

            $response = $this->client->post('/v3/creditCard/tokenize', [
                'headers' => $this->headers,
                'json' => [
                    'customer' => $data['customer'],
                    'creditCardToken' => $data['creditCardToken'],
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (!isset($responseData['id'])) {
                throw new \Exception('Falha ao registrar cartão: resposta inválida.');
            }

            return $responseData;
        } catch (RequestException $e) {
            $errorResponse = $e->hasResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : null;
            Log::error('Erro ao armazenar cartão no Asaas', [
                'message' => $e->getMessage(),
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'N/A',
                'body' => $errorResponse ?? 'Sem corpo na resposta',
            ]);
            throw new \Exception('Falha ao armazenar cartão no Asaas: ' . $e->getMessage());
        }
    }
}
