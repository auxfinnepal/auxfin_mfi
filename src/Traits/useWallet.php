<?php

namespace Auxfin\Mfi\Traits;

use Auxfin\Mfi\MfiService;
use Auxfin\Mfi\OtpService;
use Auxfin\Mfi\WalletService;
use mysql_xdevapi\Exception;

trait useWallet
{
    private WalletService $walletService;


    public function __construct()
    {
        $this->walletService = new WalletService();
    }

    public function getWallet(int $user_id)
    {
        try{
            return $this->walletService->getWallet($user_id);
        }catch (\Exception $e){
            throw $e;
        }

    }

    public function transaction($user_id, $wallet_id,$mfi_id, $amount,$transaction_type,$to_user_id){
        try {
            return $this->walletService->transaction($user_id, $wallet_id, $mfi_id, $amount, $transaction_type,$to_user_id);
        }
        catch (\Exception $e){
            throw $e;
        }
    }

}
