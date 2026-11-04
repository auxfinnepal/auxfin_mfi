<?php

namespace Auxfin\Mfi;

use GuzzleHttp\Client;

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

    /**
     * Process wallet transaction
     * Maps auxfin_mfi parameters to mfi_connect wallet transfer format
     *
     * @param int|null $userId User ID (optional if wallet_id provided)
     * @param int|null $walletId Wallet ID (optional if user_id provided)
     * @param int|null $mfiId MFI ID for charge calculation
     * @param float $amount Transaction amount
     * @param string $transactionType Transaction type (e.g., wallet_to_wallet, wallet_to_mfi)
     * @param int|null $toUserId Recipient user ID (optional if toWalletId provided)
     * @param int|null $toWalletId Recipient wallet ID (optional if toUserId provided)
     * @param float|null $charge Manual charge amount (optional, auto-calculated if null)
     * @return mixed Transaction result
     * @throws \Exception
     */
    public function transaction(
        ?int $userId,
        ?int $walletId,
        ?int $mfiId,
        float $amount,
        string $transactionType,
        ?int $toUserId = null,
        ?int $toWalletId = null,
        ?float $charge = null
    ) {
        try {
            $token = $this->mfiService->getMfiToken();

            // Validate and resolve wallet IDs
            $fromWalletId = $walletId;
            $toWalletIdResolved = $toWalletId;

            // If wallet_id not provided, try to get it from user_id
            if ($fromWalletId === null && $userId !== null) {
                $wallet = $this->getWallet($userId);
                if ($wallet && isset($wallet->id)) {
                    $fromWalletId = $wallet->id;
                }
            }

            // If to_wallet_id not provided, try to get it from to_user_id
            if ($toWalletIdResolved === null && $toUserId !== null) {
                $toWallet = $this->getWallet($toUserId);
                if ($toWallet && isset($toWallet->id)) {
                    $toWalletIdResolved = $toWallet->id;
                }
            }

            // Build request payload matching mfi_connect expected format
            $payload = [
                'from_wallet_id' => $fromWalletId,
                'to_wallet_id' => $toWalletIdResolved,
                'mfi_id' => $mfiId,
                'amount' => $amount,
                'transaction_type' => $transactionType,
            ];

            // Add optional fields only if provided
            if ($userId !== null) {
                $payload['user_id'] = $userId;
            }

            // Add charge if explicitly provided
            if ($charge !== null) {
                $payload['charge'] = $charge;
            }

            $response = $this->client->post(
                $this->apiUrl . '/api/wallet/transfer',
                [
                    'form_params' => $payload,
                    'headers' => [
                        "Authorization" => "Bearer $token",
                        "Content-Type" => "application/json",
                    ],
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
