<?php

namespace Auxfin\Mfi;

use App\Models\SsoToken;
use Carbon\Carbon;
use GuzzleHttp\Client;

class MfiService
{
    private Client $client;
    private string $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = config('mfi.api');
    }

    public function listMfi(?string $country, ?bool $status)
    {
        try {
            $token = $this->getMfiToken();

            $response = $this->client->get(
                $this->apiUrl . '/api/mfi/list',
                [
                    'query' => [
                        "country" => $country,
                        "enabled" => $status,
                        "per_page" => 100,
                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateMfi(string $mfiId, array $data)
    {
        $token = $this->getMfiToken();

        $existingResponse = $this->client->get(
            $this->apiUrl . '/api/mfi/' . $mfiId,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]
        );
        $existing = json_decode($existingResponse->getBody()->getContents(), true);

        $payload = array_filter(array_merge([
            'name' => $existing['name'] ?? null,
            'country' => $existing['country'] ?? null,
        ], $data), fn ($value) => $value !== null);

        $response = $this->client->post(
            $this->apiUrl . '/api/mfi/upsert',
            [
                'json' => $payload,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        return json_decode($response->getBody()->getContents());
    }

    public function createMfi(array $data)
    {
        $token = $this->getMfiToken();
        $response = $this->client->post(
            $this->apiUrl . '/api/mfi',
            [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        return json_decode($response->getBody()->getContents());
    }

    public function deleteMfi(string $mfiId)
    {
        $token = $this->getMfiToken();
        $response = $this->client->delete(
            $this->apiUrl . '/api/mfi/' . $mfiId,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        return json_decode($response->getBody()->getContents());
    }

    public function listTransactionCharges(?int $mfiId = null)
    {
        $endpoint = $mfiId
            ? "/api/mfi/{$mfiId}/transaction-charges"
            : "/api/transaction-charges";

        try {
            $response = $this->client->get($this->apiUrl . $endpoint, [
                'headers' => ['Authorization' => 'Bearer ' . $this->getMfiToken()],
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to list transaction charges from MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function createTransactionCharge(int $mfiId, array $data)
    {
        try {
            $response = $this->client->post($this->apiUrl . "/api/mfi/{$mfiId}/transaction-charges", [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getMfiToken(),
                    'Content-Type' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to create transaction charge in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function updateTransactionCharge(int $mfiId, int $chargeId, array $data)
    {
        try {
            $response = $this->client->put(
                $this->apiUrl . "/api/mfi/{$mfiId}/transaction-charges/{$chargeId}",
                [
                    'json' => $data,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to update transaction charge in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function deleteTransactionCharge(int $mfiId, int $chargeId)
    {
        try {
            $response = $this->client->delete(
                $this->apiUrl . "/api/mfi/{$mfiId}/transaction-charges/{$chargeId}",
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to delete transaction charge in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function getConnected(string $mfi_id, string $user_id, string $type)
    {
        try {
            $token = $this->getMfiToken();

            $response = $this->client->get(
                $this->apiUrl . '/api/mfi/connected',
                [
                    'query' => [
                        "mfi_id" => $mfi_id,
                        "user_id" => $user_id,
                        "type" => $type
                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function checkConnection(string $mfi_id, string $method)
    {

        try {
            $token = $this->getMfiToken();

            $mfiResponse = $this->client->get($this->apiUrl . "/api/mfi/$mfi_id");

            $mfi = json_decode($mfiResponse->getBody()->getContents());
            $params = (array)json_decode($mfi->connect_params);
            $params["method"] = $method;
            $params["mfi_id"] = $mfi_id;
            $response = $this->client->get(
                $this->apiUrl . '/api/check_connection',
                [
                    'query' => $params,
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function accountSearch(string $group_name, string $mfi_id)
    {
        try {
            $token = $this->getMfiToken();

            $connection = $this->checkConnection($mfi_id, 'groupAccountSearch');


            if (!$connection->class) {
                throw new \Exception("method_not_found");
            }

            $response = $this->client->get(
                $this->apiUrl . '/api/account_search',
                [
                    'query' => [
                        "group_name" => $group_name,
//                        "class" => $connection->class,
                        "mfi_id" => $mfi_id
                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function linkMfi(string $mfi_id, string $user_id, string $account_number, string $account_name, string $phone_number)
    {
        try {
            $token = $this->getMfiToken();

            $response = $this->client->post(
                $this->apiUrl . '/api/mfi/connect',
                [
                    'json' => [
                        "user_id" => $user_id,
                        "account_number" => $account_number,
                        "account_name" => $account_name,
                        "phone_number" => $phone_number,
                        "mfi_id" => $mfi_id
                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token",
                        "Content-Type" => "application/json"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAccountBalance($account_number, $mfi_id)
    {

        try {
            $token = $this->getMfiToken();

            $connection = $this->checkConnection($mfi_id, 'getBalance');

            if (!$connection->class) {
                throw new \Exception("method_not_found");
            }

            $response = $this->client->get(
                $this->apiUrl . '/api/account_balance',
                [
                    'query' => [
                        "account_number" => $account_number,
                        "class" => $connection->class,
                        "mfi_id" => $mfi_id
                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function withdrawAmount($account_number, $mfi_id, $amount)
    {

        try {
            $token = $this->getMfiToken();

            $connection = $this->checkConnection($mfi_id, 'withdrawAmount');


            if (!$connection->class) {
                throw new \Exception("method_not_found");
            }

            $response = $this->client->get(
                $this->apiUrl . '/api/withdraw_amount',
                [
                    'query' => [
                        "account_number" => $account_number,
                        "class" => $connection->class,
                        "mfi_id" => $mfi_id,
                        "amount" => $amount
                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function depositAmount($account_number, $mfi_id, $amount)
    {

        try {
            $token = $this->getMfiToken();

            $connection = $this->checkConnection($mfi_id, 'depositAmount');


            if (!$connection->class) {
                throw new \Exception("method_not_found");
            }

            $response = $this->client->get(
                $this->apiUrl . '/api/deposit_amount',
                [
                    'query' => [
                        "account_number" => $account_number,
                        "class" => $connection->class,
                        "mfi_id" => $mfi_id,
                        "amount" => $amount
                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function getMiniStatement(string $account_number, string $from_date, string $to_date, string $mfi_id)
    {
        try {
            $token = $this->getMfiToken();

            $connection = $this->checkConnection($mfi_id, 'miniStatement');

            if (!$connection->class) {
                throw new \Exception("method_not_found");
            }

            $response = $this->client->get(
                $this->apiUrl . '/api/mini_statement',
                [
                    'query' => [
                        "account_number" => $account_number,
                        "class" => $connection->class,
                        'from_date' => $from_date,
                        'to_date' => $to_date,
                        'mfi_id'=>$mfi_id
                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getLoanHistory(string $account_number, string $from_date, string $to_date, string $mfi_id)
    {
        try {


            $token = $this->getMfiToken();

            $connection = $this->checkConnection($mfi_id, 'groupLoanHistory');

            if (!$connection->class) {
                throw new \Exception("method_not_found");
            }

            $response = $this->client->get(
                $this->apiUrl . '/api/loan_history',
                [
                    'query' => [
                        "account_number" => $account_number,
                        "class" => $connection->class,
                        "from_date" => $from_date,
                        "to_date" => $to_date,
                        'mfi_id'=>$mfi_id
                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }

    }

    public function applyLoan($mfi_id, $user_id, $purpose, $amount, $repayment_period, $requested_by, $account_number)
    {
        try {
            $token = $this->getMfiToken();


            $connection = $this->checkConnection($mfi_id, 'applyLoan');

            if (!$connection->class) {
                throw new \Exception("method_not_found");
            }

            $response = $this->client->post(
                $this->apiUrl . '/api/loan/apply',
                [
                    'form_params' => [
                        "account_number" => $account_number,
                        "class" => $connection->class,
                        "mfi_id" => $mfi_id,
                        "user_id" => $user_id,
                        "purpose" => $purpose,
                        "amount" => $amount,
                        "repayment_period" => $repayment_period,
                        "requested_by" => $requested_by
                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }




    public function getMfiToken()
    {
        try {
            $client_id = config('mfi.client_id');
            $client_secret = config('mfi.client_secret');
            $ssoToken = SsoToken::where('product_name', 'Mfi')->first();

            if ($ssoToken) {
                $expires_in = Carbon::parse($ssoToken->expires_in);
                if ($expires_in->isPast()) {
                    $response = $this->client->post($this->apiUrl . "/oauth/token", [
                        "form_params" => [
                            "grant_type" => "client_credentials",
                            "client_id" => $client_id,
                            "client_secret" => $client_secret,
                            "scope" => "*",
                        ],
                    ]);
                    $newToken = json_decode((string)$response->getBody(), true);
                    $token = $newToken['access_token'];
                    $expires_in = $newToken['expires_in'];
                    $ssoToken->update(['access_token' => $token, 'expires_in' => Carbon::now()->addSeconds($expires_in)]);
                }
            } else {
                $response = $this->client->post($this->apiUrl . "/oauth/token", [
                    "form_params" => [
                        "grant_type" => "client_credentials",
                        "client_id" => $client_id,
                        "client_secret" => $client_secret,
                        "scope" => "*",
                    ],
                ]);
                $newToken = json_decode((string)$response->getBody(), true);
                $token = $newToken['access_token'];
                $expires_in = $newToken['expires_in'];
                $ssoToken = SsoToken::create(['access_token' => $token, 'expires_in' => Carbon::now()->addSeconds($expires_in), 'product_name' => 'Mfi', 'refresh_token' => $newToken['refresh_token'] ?? null]);
            }

            return $ssoToken->access_token;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function listLoanApplications($mfi_id, $user_id)
    {
        try {
            $token = $this->getMfiToken();



            $response = $this->client->get(
                $this->apiUrl . '/api/loan/list_application',
                [
                    "query"=>[
                        "mfi_id"=>$mfi_id,
                        "user_id"=>$user_id
                    ]
                    ,
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function listLoans($mfi_id, $user_id)
    {
        try {
            $token = $this->getMfiToken();



            $response = $this->client->get(
                $this->apiUrl . '/api/loan/list_loan',
                [
                    "query"=>[
                        "mfi_id"=>$mfi_id,
                        "user_id"=>$user_id
                    ]
                    ,
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function validateLoan($mfi_id, $user_id,$amount,$application_id,$note,$repayment_period,$interest_rate,$account_number)
    {
        try {
            $token = $this->getMfiToken();



            $response = $this->client->post(
                $this->apiUrl . '/api/loan/validate',
                [
                    "form_params"=>[
                        "loan_application_id"=>$application_id,
                        "note"=>$note,
                        "account_number"=>$account_number,
                        "amount"=>$amount,
                        "interest_rate"=>$interest_rate,
                        "repayment_period"=>$repayment_period,
                        "user_id"=>$user_id,
                        "mfi_id"=>$mfi_id

                    ]
                    ,
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function rejectLoan($application_id,$note,$account_number)
    {
        try {
            $token = $this->getMfiToken();



            $response = $this->client->post(
                $this->apiUrl . '/api/loan/reject',
                [
                    "form_params"=>[
                        "loan_application_id"=>$application_id,
                        "note"=>$note,
                        "account_number"=>$account_number,

                    ]
                    ,
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getTransactionCharge(int $mfiId, float $amount, ?string $transactionType = null)
    {
        try {
            $token = $this->getMfiToken();

            $query = [
                'amount' => $amount,
            ];
            if ($transactionType !== null) {
                $query['transaction_type'] = $transactionType;
            }

            $response = $this->client->get(
                $this->apiUrl . "/api/mfi/$mfiId/transaction-charge",
                [
                    'query' => $query,
                    'headers' => [
                        'Authorization' => "Bearer $token",
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function listTransactionChargeRecords(?int $mfiId = null, array $filters = []): array
    {
        $endpoint = $mfiId
            ? "/api/mfi/{$mfiId}/transaction-charge-records"
            : "/api/transaction-charge-records";

        try {
            $queryParams = $filters;
            $response = $this->client->get($this->apiUrl . $endpoint, [
                'query' => $queryParams,
                'headers' => ['Authorization' => 'Bearer ' . $this->getMfiToken()],
            ]);

            $decoded = json_decode($response->getBody()->getContents(), true);
            // Returns paginated response: ['data' => [...], 'current_page' => ..., 'total' => ..., 'links' => ..., 'meta' => ...]
            return is_array($decoded) ? $decoded : (array)$decoded;
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to list transaction charge records from MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    // SMS Gateway Charges (Amount-Based)

    public function listSmsGatewayChargesAmount(int $mfiId, int $smsGatewayId)
    {
        try {
            $response = $this->client->get(
                $this->apiUrl . "/api/mfi/{$mfiId}/sms-gateway/{$smsGatewayId}/charges-amount",
                [
                    'headers' => ['Authorization' => 'Bearer ' . $this->getMfiToken()],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to list SMS gateway charges from MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function createSmsGatewayChargeAmount(?int $organizationId, int $mfiId, int $smsGatewayId, array $data)
    {
        $data['organization_id'] = $organizationId;
        
        try {
            $response = $this->client->post(
                $this->apiUrl . "/api/mfi/{$mfiId}/sms-gateway/{$smsGatewayId}/charges-amount",
                [
                    'json' => $data,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to create SMS gateway charge in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function updateSmsGatewayChargeAmount(int $mfiId, int $smsGatewayId, int $chargeId, array $data)
    {
        try {
            $response = $this->client->put(
                $this->apiUrl . "/api/mfi/{$mfiId}/sms-gateway/{$smsGatewayId}/charges-amount/{$chargeId}",
                [
                    'json' => $data,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to update SMS gateway charge in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function deleteSmsGatewayChargeAmount(int $mfiId, int $smsGatewayId, int $chargeId)
    {
        try {
            $response = $this->client->delete(
                $this->apiUrl . "/api/mfi/{$mfiId}/sms-gateway/{$smsGatewayId}/charges-amount/{$chargeId}",
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to delete SMS gateway charge in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function toggleSmsGatewayChargeAmount(int $mfiId, int $smsGatewayId, int $chargeId)
    {
        try {
            $response = $this->client->post(
                $this->apiUrl . "/api/mfi/{$mfiId}/sms-gateway/{$smsGatewayId}/charges-amount/{$chargeId}/toggle",
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to toggle SMS gateway charge in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function calculateSmsGatewayCharge(?int $organizationId, int $mfiId, int $smsGatewayId, float $amount)
    {
        try {
            $response = $this->client->post(
                $this->apiUrl . "/api/mfi/{$mfiId}/sms-gateway/{$smsGatewayId}/calculate-charge",
                [
                    'json' => [
                        'organization_id' => $organizationId,
                        'amount' => $amount,
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to calculate SMS gateway charge from MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    // Payment Gateways

    public function listPaymentGateways()
    {
        try {
            $response = $this->client->get(
                $this->apiUrl . '/api/payment-gateways',
                [
                    'headers' => ['Authorization' => 'Bearer ' . $this->getMfiToken()],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to list payment gateways from MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function getPaymentGateway(int $id)
    {
        try {
            $response = $this->client->get(
                $this->apiUrl . "/api/payment-gateways/{$id}",
                [
                    'headers' => ['Authorization' => 'Bearer ' . $this->getMfiToken()],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to get payment gateway from MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function createPaymentGateway(array $data)
    {
        try {
            $response = $this->client->post(
                $this->apiUrl . '/api/payment-gateways',
                [
                    'json' => $data,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to create payment gateway in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function updatePaymentGateway(int $id, array $data)
    {
        try {
            $response = $this->client->put(
                $this->apiUrl . "/api/payment-gateways/{$id}",
                [
                    'json' => $data,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to update payment gateway in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function deletePaymentGateway(int $id)
    {
        try {
            $response = $this->client->delete(
                $this->apiUrl . "/api/payment-gateways/{$id}",
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to delete payment gateway in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function togglePaymentGateway(int $id)
    {
        try {
            $response = $this->client->post(
                $this->apiUrl . "/api/payment-gateways/{$id}/toggle",
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to toggle payment gateway in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    // Payment Gateway Charges

    public function listPaymentGatewayCharges(int $mfiId, int $paymentGatewayId)
    {
        try {
            $response = $this->client->get(
                $this->apiUrl . "/api/mfi/{$mfiId}/payment-gateway/{$paymentGatewayId}/charges",
                [
                    'headers' => ['Authorization' => 'Bearer ' . $this->getMfiToken()],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to list payment gateway charges from MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function createPaymentGatewayCharge(?int $organizationId, int $mfiId, int $paymentGatewayId, array $data)
    {
        $data['organization_id'] = $organizationId;
        
        try {
            $response = $this->client->post(
                $this->apiUrl . "/api/mfi/{$mfiId}/payment-gateway/{$paymentGatewayId}/charges",
                [
                    'json' => $data,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to create payment gateway charge in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function updatePaymentGatewayCharge(int $mfiId, int $paymentGatewayId, int $chargeId, array $data)
    {
        try {
            $response = $this->client->put(
                $this->apiUrl . "/api/mfi/{$mfiId}/payment-gateway/{$paymentGatewayId}/charges/{$chargeId}",
                [
                    'json' => $data,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to update payment gateway charge in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function deletePaymentGatewayCharge(int $mfiId, int $paymentGatewayId, int $chargeId)
    {
        try {
            $response = $this->client->delete(
                $this->apiUrl . "/api/mfi/{$mfiId}/payment-gateway/{$paymentGatewayId}/charges/{$chargeId}",
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to delete payment gateway charge in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function togglePaymentGatewayCharge(int $mfiId, int $paymentGatewayId, int $chargeId)
    {
        try {
            $response = $this->client->post(
                $this->apiUrl . "/api/mfi/{$mfiId}/payment-gateway/{$paymentGatewayId}/charges/{$chargeId}/toggle",
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to toggle payment gateway charge in MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function calculatePaymentGatewayCharge(?int $organizationId, int $mfiId, int $paymentGatewayId, float $amount)
    {
        try {
            $response = $this->client->post(
                $this->apiUrl . "/api/mfi/{$mfiId}/payment-gateway/{$paymentGatewayId}/calculate-charge",
                [
                    'json' => [
                        'organization_id' => $organizationId,
                        'amount' => $amount,
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getMfiToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $exception) {
            $body = $exception->getResponse()?->getBody()->getContents();
            throw new \RuntimeException(
                'Unable to calculate payment gateway charge from MFI service: ' . ($body ?: $exception->getMessage()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }
}
