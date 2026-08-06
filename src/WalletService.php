<?php

namespace Auxfin\Mfi;

use GuzzleHttp\Client;
use Exception;

class WalletService
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



    public function getWallet(int $user_id)
    {
        try {
            $token = $this->mfiService->getMfiToken();
            $response = $this->client->get(
                $this->apiUrl . '/api/wallet',
                [
                    'query' => [

                        "user_id" => $user_id,

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
    public function transaction($user_id, $wallet_id, $mfi_id, $amount, $transaction_type, $to_user_id, $account, $remark, $pin = null,$otp = null)
    {
        try {
            $token = $this->mfiService->getMfiToken();

            $connection = null;

//            if ($mfi_id) {
//                $connection = $this->mfiService->checkConnection($mfi_id, 'transaction');
//            }

            $response = $this->client->post(
                $this->apiUrl . '/api/wallet/transfer',
                [
                    'form_params' => [
                        "user_id" => $user_id,
                        "wallet_id" => $wallet_id ?? null,
                        "amount" => $amount,
                        "mfi_id" => $mfi_id,
                        "class" => $connection->class ?? null,
                        "transaction_type" => $transaction_type,
                        "to_user_id" => $to_user_id ?? null,
                        "account_number" => $account ?? null,
                        "remark" => $remark ?? null,
                        "pin" => $pin,
                        "otp" => $otp
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

    public function getWalletTransactions($from_date, $to_date, $user_id, $wallet_id)
    {
        try {
            $token = $this->mfiService->getMfiToken();
            $response = $this->client->get(
                $this->apiUrl . '/api/wallet/transactions',
                [
                    'query' => [
                        "from_date" => $from_date,
                        "to_date" => $to_date,
                        "wallet_id" => $wallet_id,
                        "user_id" => $user_id,

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
}
