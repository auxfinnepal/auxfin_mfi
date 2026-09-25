<?php

namespace Auxfin\Mfi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;

class SmsGatewayService
{
    private Client $client;
    private string $apiUrl;
    private MfiService $mfiService;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = config('mfi.api');
        $this->mfiService = new MfiService();
    }

    private function getAuthHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->mfiService->getMfiToken(),
        ];
    }

    private function getJsonHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->mfiService->getMfiToken(),
            'Content-Type' => 'application/json',
        ];
    }

    private function handleError(ClientException | ServerException | TransferException $e): void
    {
        $status = $e->getCode();
        $body = '';
        if ($e instanceof ClientException || $e instanceof ServerException) {
            $body = $e->getResponse()?->getBody()->getContents() ?? '';
        }

        if ($status === 404) {
            return;
        }

        $message = 'sms_gateway_request_failed';
        if ($body) {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                if (isset($decoded['message'])) {
                    $message = $decoded['message'];
                } elseif (isset($decoded['error'])) {
                    $message = is_string($decoded['error']) ? $decoded['error'] : (is_array($decoded['error']) ? json_encode($decoded['error']) : (string) $decoded['error']);
                } elseif (isset($decoded['errors']) && is_array($decoded['errors'])) {
                    $firstError = reset($decoded['errors']);
                    $message = is_string($firstError) ? $firstError : ($decoded['message'] ?? 'sms_gateway_request_failed');
                }
            }
        }

        $code = $status >= 400 && $status < 500 ? $status : 500;
        throw new \Exception($message, $code);
    }

    public function listGateways(?bool $enabled = null)
    {
        try {
            $query = [];
            if ($enabled !== null) {
                $query['enabled'] = $enabled ? 'true' : 'false';
            }

            $response = $this->client->get($this->apiUrl . '/api/sms-gateways', [
                'query' => $query,
                'headers' => $this->getAuthHeaders(),
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (ClientException | ServerException | TransferException $e) {
            if ($e instanceof ClientException && $e->getCode() === 404) {
                return [];
            }
            $this->handleError($e);
        }
    }

    public function getGateway(int $id)
    {
        try {
            $response = $this->client->get($this->apiUrl . '/api/sms-gateways/' . $id, [
                'headers' => $this->getAuthHeaders(),
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (ClientException | ServerException | TransferException $e) {
            if ($e instanceof ClientException && $e->getCode() === 404) {
                return null;
            }
            $this->handleError($e);
        }
    }

    public function createGateway(array $data)
    {
        try {
            $response = $this->client->post($this->apiUrl . '/api/sms-gateways', [
                'json' => $data,
                'headers' => $this->getJsonHeaders(),
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (ClientException | ServerException | TransferException $e) {
            $this->handleError($e);
        }
    }

    public function updateGateway(int $id, array $data)
    {
        try {
            $response = $this->client->put($this->apiUrl . '/api/sms-gateways/' . $id, [
                'json' => $data,
                'headers' => $this->getJsonHeaders(),
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (ClientException | ServerException | TransferException $e) {
            if ($e instanceof ClientException && $e->getCode() === 404) {
                return null;
            }
            $this->handleError($e);
        }
    }

    public function deleteGateway(int $id): bool
    {
        try {
            $response = $this->client->delete($this->apiUrl . '/api/sms-gateways/' . $id, [
                'headers' => $this->getAuthHeaders(),
            ]);

            return true;
        } catch (ClientException | ServerException | TransferException $e) {
            if ($e instanceof ClientException && $e->getCode() === 404) {
                return false;
            }
            $this->handleError($e);
        }
    }

    public function toggleGateway(int $id)
    {
        try {
            $response = $this->client->post($this->apiUrl . '/api/sms-gateways/' . $id . '/toggle', [
                'headers' => $this->getAuthHeaders(),
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (ClientException | ServerException | TransferException $e) {
            if ($e instanceof ClientException && $e->getCode() === 404) {
                return null;
            }
            $this->handleError($e);
        }
    }

    public function assignToMfi(int $gatewayId, int $mfiId, ?int $userId = null): bool
    {
        try {
            $response = $this->client->post($this->apiUrl . '/api/sms-gateways/' . $gatewayId . '/mfi/' . $mfiId . '/assign', [
                'json' => ['user_id' => $userId],
                'headers' => $this->getJsonHeaders(),
            ]);

            return true;
        } catch (ClientException | ServerException | TransferException $e) {
            if ($e instanceof ClientException && $e->getCode() === 404) {
                return false;
            }
            $this->handleError($e);
        }
    }

    public function removeFromMfi(int $gatewayId, int $mfiId, ?int $userId = null): bool
    {
        try {
            $response = $this->client->delete($this->apiUrl . '/api/sms-gateways/' . $gatewayId . '/mfi/' . $mfiId . '/remove', [
                'json' => ['user_id' => $userId],
                'headers' => $this->getJsonHeaders(),
            ]);

            return true;
        } catch (ClientException | ServerException | TransferException $e) {
            if ($e instanceof ClientException && $e->getCode() === 404) {
                return false;
            }
            $this->handleError($e);
        }
    }

    public function getGatewaysForMfi(int $mfiId)
    {
        try {
            $response = $this->client->get($this->apiUrl . '/api/mfi/' . $mfiId . '/sms-gateway', [
                'headers' => $this->getAuthHeaders(),
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (ClientException | ServerException | TransferException $e) {
            if ($e instanceof ClientException && $e->getCode() === 404) {
                return null;
            }
            $this->handleError($e);
        }
    }

    public function listChargesByGateway(int $gatewayId)
    {
        try {
            $response = $this->client->get($this->apiUrl . '/api/sms-gateways/' . $gatewayId . '/charges', [
                'headers' => $this->getAuthHeaders(),
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (ClientException | ServerException | TransferException $e) {
            if ($e instanceof ClientException && $e->getCode() === 404) {
                return [];
            }
            $this->handleError($e);
        }
    }

    public function createChargeByGateway(int $gatewayId, array $data)
    {
        try {
            $response = $this->client->post($this->apiUrl . '/api/sms-gateways/' . $gatewayId . '/charges', [
                'json' => $data,
                'headers' => $this->getJsonHeaders(),
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (ClientException | ServerException | TransferException $e) {
            $this->handleError($e);
        }
    }

    public function updateChargeByGateway(int $gatewayId, int $chargeId, array $data)
    {
        try {
            $response = $this->client->put($this->apiUrl . '/api/sms-gateways/' . $gatewayId . '/charges/' . $chargeId, [
                'json' => $data,
                'headers' => $this->getJsonHeaders(),
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (ClientException | ServerException | TransferException $e) {
            if ($e instanceof ClientException && $e->getCode() === 404) {
                return null;
            }
            $this->handleError($e);
        }
    }

    public function deleteChargeByGateway(int $gatewayId, int $chargeId): bool
    {
        try {
            $response = $this->client->delete($this->apiUrl . '/api/sms-gateways/' . $gatewayId . '/charges/' . $chargeId, [
                'headers' => $this->getAuthHeaders(),
            ]);

            return true;
        } catch (ClientException | ServerException | TransferException $e) {
            if ($e instanceof ClientException && $e->getCode() === 404) {
                return false;
            }
            $this->handleError($e);
        }
    }

    public function toggleChargeByGateway(int $gatewayId, int $chargeId)
    {
        try {
            $response = $this->client->post($this->apiUrl . '/api/sms-gateways/' . $gatewayId . '/charges/' . $chargeId . '/toggle', [
                'headers' => $this->getAuthHeaders(),
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (ClientException | ServerException | TransferException $e) {
            if ($e instanceof ClientException && $e->getCode() === 404) {
                return null;
            }
            $this->handleError($e);
        }
    }
}
