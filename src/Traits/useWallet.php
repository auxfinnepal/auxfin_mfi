<?php

namespace Auxfin\Mfi\Traits;

use Auxfin\Mfi\WalletService;

trait useWallet
{
    private WalletService $walletService;


    public function __construct()
    {
        $this->walletService = new WalletService();
    }

    public function getWallet(int $user_id)
    {
        try {
            return $this->walletService->getWallet($user_id);
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * Process wallet transaction
     *
     * @param int|null $userId User ID (optional if walletId provided)
     * @param int|null $walletId Wallet ID (optional if userId provided)
     * @param int|null $mfiId MFI ID for charge calculation
     * @param float $amount Transaction amount
     * @param string $transactionType Transaction type (e.g., wallet_to_wallet, wallet_to_mfi)
     * @param int|null $toUserId Recipient user ID (optional if toWalletId provided)
     * @param int|null $toWalletId Recipient wallet ID (optional if toUserId provided)
     * @param float|null $charge Manual charge amount (optional)
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
        ?float $charge = 0
    ) {
        try {
            return $this->walletService->transaction(
                $userId,
                $walletId,
                $mfiId,
                $amount,
                $transactionType,
                $toUserId,
                $toWalletId,
                $charge
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getWalletTransactionsData($from_date, $to_date, $user_id, $wallet_id)
    {
        try {
            return $this->walletService->getWalletTransactions($from_date, $to_date, $user_id, $wallet_id);
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
