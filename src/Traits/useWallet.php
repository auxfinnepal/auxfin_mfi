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

    public function transaction($user_id, $wallet_id, $mfi_id, $amount, $transaction_type, $to_user_id, $account, $remark)
    {
        try {

            return $this->walletService->transaction($user_id, $wallet_id, $mfi_id, $amount, $transaction_type, $to_user_id, $account, $remark);
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
