<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected Client $client;

    public function __construct()
    {
        $this->apiUrl = config('services.asaas.url', 'https://api-sandbox.asaas.com');
        $this->apiKey = config('services.asaas.key');
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'timeout' => 10.0,
        ]);

        Log::debug('Inicializando AsaasService', [
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

    /**
     * Cria um customer no Asaas.
     *
     * @param array $data Dados do cliente a serem enviados
     * @return array Resposta da API com o ID do cliente
     * @throws \Exception Se a requisição falhar ou a resposta for inválida
     */
    public function createCustomer(array $data): array
    {
        try {
            // Validar dados obrigatórios
            if (empty($data['name']) || empty($data['cpfCnpj'])) {
                throw new \Exception('Dados obrigatórios ausentes: name ou cpfCnpj.');
            }

            // Montar payload completo
            $payload = [
                'name' => $data['name'],
                'cpfCnpj' => $data['cpfCnpj'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'mobilePhone' => $data['mobilePhone'] ?? null,
                'address' => $data['address'] ?? null,
                'addressNumber' => $data['addressNumber'] ?? null,
                'complement' => $data['complement'] ?? null,
                'province' => $data['province'] ?? null,
                'postalCode' => $data['postalCode'] ?? null,
                'notificationDisabled' => false,
                'additionalEmails' => null,
                'municipalInscription' => null,
                'stateInscription' => null,
                'observations' => null,
                'groupName' => null,
                'company' => null,
                'foreignCustomer' => false,
            ];

            // Log detalhado dos dados enviados
            Log::info('Enviando requisição para criar cliente no Asaas', [
                'data' => array_merge(
                    array_diff_key($payload, array_flip(['cpfCnpj'])),
                    ['cpfCnpj' => substr($data['cpfCnpj'] ?? 'N/A', 0, 4) . '****']
                ),
            ]);

            // Tentar a requisição com até 3 tentativas para erros 5xx
            $response = $this->client->request('POST', '/v3/customers', [
                'headers' => $this->headers(),
                'json' => array_filter($payload, fn($value) => !is_null($value)),
                'retry' => [
                    'max' => 3,
                    'delay' => 1000,
                    'when' => function ($exception) {
                        return $exception instanceof RequestException &&
                               $exception->hasResponse() &&
                               $exception->getResponse()->getStatusCode() >= 500;
                    },
                ],
            ]);

            // Log detalhado da resposta
            $responseData = json_decode($response->getBody()->getContents(), true);
            Log::debug('Resposta da API do Asaas', [
                'status' => $response->getStatusCode(),
                'body' => $responseData ?? 'Sem corpo na resposta',
                'headers' => $response->getHeaders(),
            ]);

            if ($response->getStatusCode() >= 400) {
                $errorMessage = 'Erro na API do Asaas';
                if (is_array($responseData) && isset($responseData['errors'])) {
                    $errorDetails = array_map(function ($error) {
                        return isset($error['description']) ? $error['description'] : json_encode($error);
                    }, $responseData['errors']);
                    $errorMessage .= ': ' . implode(', ', $errorDetails);
                } elseif ($response->getStatusCode() === 401) {
                    $errorMessage .= ': Chave de API inválida ou não autorizada.';
                }
                throw new \Exception($errorMessage);
            }

            if (!is_array($responseData) || !isset($responseData['id'])) {
                throw new \Exception('Resposta inválida da API do Asaas: campo "id" não encontrado.');
            }

            return $responseData;

        } catch (RequestException $e) {
            $errorResponse = $e->hasResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : null;
            Log::error('Erro na requisição à API do Asaas', [
                'message' => $e->getMessage(),
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'N/A',
                'body' => $errorResponse ?? 'Sem corpo na resposta',
            ]);
            throw new \Exception('Falha ao criar cliente no Asaas: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Erro ao criar cliente no Asaas', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}