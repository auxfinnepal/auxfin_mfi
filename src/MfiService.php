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

    public function listMfi(string $country, bool $status)
    {
        try {
            $token = $this->getMfiToken();

            $response = $this->client->get(
                $this->apiUrl . '/api/mfi/list',
                [
                    'query' => [
                        "country" => $country,
                        "enabled" => $status
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
                        'to_date' => $to_date
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
                        "to_date" => $to_date
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
                $ssoToken = SsoToken::create(['access_token' => $token, 'expires_in' => Carbon::now()->addSeconds($expires_in), 'product_name' => 'Mfi', 'refresh_token' => $newToken['refresh_token']]);
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
}
