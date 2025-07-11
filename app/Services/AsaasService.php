<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;

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
            $this->validateCustomerData($data);

            $payload = $this->buildCustomerPayload($data);

            $this->logCustomerRequest($payload, $data);

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

            $responseData = json_decode($response->getBody()->getContents(), true);

            $this->logCustomerResponse($response, $responseData);

            if ($response->getStatusCode() >= 400) {
                throw new \Exception($this->buildCustomerErrorMessage($response, $responseData));
            }

            if (!is_array($responseData) || !isset($responseData['id'])) {
                throw new \Exception('Resposta inválida da API do Asaas: campo "id" não encontrado.');
            }

            return $responseData;
        } catch (RequestException $e) {
            $errorResponse = $e->hasResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : null;
            Log::error('Erro na requisição à API do Asaas para criação', [
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

    private function validateCustomerData(array $data): void
    {
        if (empty($data['name']) || empty($data['cpfCnpj'])) {
            throw new \Exception('Dados obrigatórios ausentes: name ou cpfCnpj.');
        }
    }

    private function buildCustomerPayload(array $data): array
    {
        return [
            'name' => $data['name'],
            'cpfCnpj' => $data['cpfCnpj'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'mobilePhone' => $data['cellphone'] ?? null,
            'address' => $data['address'] ?? null,
            'addressNumber' => $data['number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'province' => $data['province'] ?? null,
            'postalCode' => $data['postalCode'] ?? null,
            'notificationDisabled' => false,
            'externalReference' => $data['externalReference'] ?? null,
            'observations' => $data['observations'] ?? null,
            'foreignCustomer' => false,
            'groupName' => null,
            'company' => null,
        ];
    }

    private function logCustomerRequest(array $payload, array $data): void
    {
        Log::info('Enviando requisição para criar cliente no Asaas', [
            'data' => array_merge(
                array_diff_key($payload, array_flip(['cpfCnpj'])),
                ['cpfCnpj' => substr($data['cpfCnpj'] ?? 'N/A', 0, 4) . '****']
            ),
        ]);
    }

    private function logCustomerResponse($response, $responseData): void
    {
        Log::debug('Resposta da API do Asaas para criação', [
            'status' => $response->getStatusCode(),
            'body' => $responseData ?? 'Sem corpo na resposta',
            'headers' => $response->getHeaders(),
        ]);
    }

    private function buildCustomerErrorMessage($response, $responseData): string
    {
        $errorMessage = 'Erro na API do Asaas';
        if (is_array($responseData) && isset($responseData['errors'])) {
            $errorDetails = array_map(function ($error) {
                return isset($error['description']) ? $error['description'] : json_encode($error);
            }, $responseData['errors']);
            $errorMessage .= ': ' . implode(', ', $errorDetails);
        } elseif ($response->getStatusCode() === 401) {
            $errorMessage .= ': Chave de API inválida ou não autorizada.';
        }
        return $errorMessage;
    }

    /**
     * Atualiza um customer no Asaas.
     *
     * @param string $customerId ID do cliente no Asaas
     * @param array $payload Dados do cliente a serem enviados
     * @return array Resposta da API
     * @throws \Exception Se a requisição falhar ou a resposta for inválida
     */
    public function updateCustomer(string $customerId, array $payload): array
    {
        try {
            $this->validateCustomerData($payload);

            $updatePayload = $this->buildUpdateCustomerPayload($payload);

            $this->logUpdateCustomerRequest($customerId, $updatePayload);

            $response = $this->client->request('PUT', "/v3/customers/{$customerId}", [
                'headers' => $this->headers(),
                'json' => array_filter($updatePayload, fn($value) => !is_null($value)),
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

            $responseData = json_decode($response->getBody()->getContents(), true);

            $this->logUpdateCustomerResponse($customerId, $response, $responseData);

            if ($response->getStatusCode() >= 400) {
                throw new \Exception($this->buildUpdateCustomerErrorMessage($customerId, $response, $responseData));
            }

            if (!is_array($responseData) || !isset($responseData['id'])) {
                throw new \Exception('Resposta inválida da API do Asaas: campo "id" não encontrado.');
            }

            return $responseData;
        } catch (RequestException $e) {
            $errorResponse = $e->hasResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : null;
            Log::error('Erro na requisição à API do Asaas para atualização', [
                'customerId' => $customerId,
                'message' => $e->getMessage(),
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'N/A',
                'body' => $errorResponse ?? 'Sem corpo na resposta',
            ]);
            throw new \Exception('Falha ao atualizar cliente no Asaas: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar cliente no Asaas', [
                'customerId' => $customerId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function buildUpdateCustomerPayload(array $payload): array
    {
        return [
            'name' => $payload['name'],
            'cpfCnpj' => $payload['cpfCnpj'],
            'email' => $payload['email'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'mobilePhone' => $payload['cellphone'] ?? null,
            'address' => $payload['address'] ?? null,
            'addressNumber' => $payload['number'] ?? null,
            'complement' => $payload['complement'] ?? null,
            'province' => $payload['province'] ?? null,
            'postalCode' => $payload['postalCode'] ?? null,
            'notificationDisabled' => false,
            'externalReference' => $payload['externalReference'] ?? null,
            'observations' => $payload['observations'] ?? null,
            'foreignCustomer' => false,
            'groupName' => null,
            'company' => null,
        ];
    }

    private function logUpdateCustomerRequest(string $customerId, array $payload): void
    {
        Log::info('Enviando requisição para atualizar cliente no Asaas', [
            'customerId' => $customerId,
            'payload' => array_merge(
                array_diff_key($payload, array_flip(['cpfCnpj'])),
                ['cpfCnpj' => substr($payload['cpfCnpj'] ?? 'N/A', 0, 4) . '****']
            ),
        ]);
    }

    private function logUpdateCustomerResponse(string $customerId, $response, $responseData): void
    {
        Log::debug('Resposta da API do Asaas para atualização', [
            'customerId' => $customerId,
            'status' => $response->getStatusCode(),
            'body' => $responseData ?? 'Sem corpo na resposta',
            'headers' => $response->getHeaders(),
        ]);
    }

    private function buildUpdateCustomerErrorMessage(string $customerId, $response, $responseData): string
    {
        $errorMessage = 'Erro na API do Asaas ao atualizar cliente';
        if (is_array($responseData) && isset($responseData['errors'])) {
            $errorDetails = array_map(function ($error) {
                return isset($error['description']) ? $error['description'] : json_encode($error);
            }, $responseData['errors']);
            $errorMessage .= ': ' . implode(', ', $errorDetails);
        } elseif ($response->getStatusCode() === 401) {
            $errorMessage .= ': Chave de API inválida ou não autorizada.';
        } elseif ($response->getStatusCode() === 404) {
            $errorMessage .= ': Cliente não encontrado no Asaas (ID: ' . $customerId . ').';
        }
        return $errorMessage;
    }


    /**
     * [Description for getCustomer]
     *
     * @param string $customerId
     * 
     * @return array|null
     * 
     */
    public function getCustomer(string $customerId): ?array
    {
        try {
            $response = $this->client->request('GET', "/v3/customers/{$customerId}", [
                'headers' => $this->headers(),
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() === 200 && isset($responseData['id'])) {
                return $responseData;
            }

            return null;
        } catch (RequestException $e) {
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                return null; // Cliente não encontrado
            }

            Log::error('Erro ao consultar cliente no Asaas', [
                'customerId' => $customerId,
                'message' => $e->getMessage(),
                'status' => $e->getResponse()?->getStatusCode(),
            ]);

            throw new \Exception('Erro ao consultar cliente no Asaas: ' . $e->getMessage());
        }
    }

    public function createPayment(array $payload): array
    {
        $response = $this->client->post('/v3/payments', [
            'headers' => $this->headers(),
            'json' => $payload,
        ]);

        $data = json_decode($response->getBody(), true);

        if (!isset($data['id'])) {
            throw new \Exception('Erro ao criar pagamento no Asaas');
        }

        return $data;
    }

    /**
     * Armazena um cartão de crédito tokenizado no Asaas.
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function storeCard(array $data): array
    {
        try {
            if (empty($data['customer']) || empty($data['creditCardToken'])) {
                throw new \Exception('Parâmetros obrigatórios ausentes: customer ou creditCardToken.');
            }

            $response = $this->client->post('/v3/myAccount/creditCard', [
                'headers' => $this->headers(),
                'json' => [
                    'customer' => $data['customer'],
                    'creditCardToken' => $data['creditCardToken'],
                    // você pode passar 'billingAddress' e outros dados se quiser
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (!isset($responseData['id'])) {
                throw new \Exception('Falha ao registrar cartão: resposta inválida.');
            }

            return $responseData;
        } catch (\Exception $e) {
            Log::error('Erro ao armazenar cartão no Asaas', [
                'erro' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
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
            'externalReference' => $contribution['id'],
        ];

        return $this->createPayment($payload);
    }

    public function createBoletoCharge(array $contribution, string $dueDate): array
    {
        $payload = [
            'customer' => $contribution['customer_id'],
            'billingType' => 'BOLETO_BANCARIO',
            'value' => $contribution['amount'],
            'dueDate' => $dueDate,
            'description' => $contribution['description'] ?? 'Contribuição',
            'externalReference' => $contribution['id'],
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
            'externalReference' => $contribution['id'],
            'creditCardToken' => $card['token'], // Token do cartão armazenado
        ];

        return $this->createPayment($payload);
    }
}
