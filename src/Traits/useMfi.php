<?php

namespace Auxfin\Mfi\Traits;

use Auxfin\Mfi\MfiService;
use Auxfin\Mfi\OtpService;

trait useMfi
{
    use useWallet,useOtp;
    private MfiService $mfiService;



    public function __construct()
    {
        $this->mfiService = new MfiService();
        $this->otpService = new OtpService();
    }

    public function listMfi(string $country, bool $status)
    {
        return $this->mfiService->listMfi($country, $status);
    }

    public function getConnected(string $mfi_id, string $user_id)
    {
        return $this->mfiService->getConnected($mfi_id, $user_id);
    }

    public function checkConnection(string $mfi_id, string $method)
    {
        return $this->mfiService->checkConnection($mfi_id, $method);
    }

    public function accountSearch(string $group_name, string $mfi_id)
    {
        return $this->mfiService->accountSearch($group_name, $mfi_id);
    }


    private function getMfiToken()
    {
        return $this->mfiService->getMfiToken();
    }

    public function linkMfi(string $mfi_id, string $user_id, string $account_number, string $account_name, string $phone_number)
    {
        return $this->mfiService->linkMfi($mfi_id, $user_id, $account_number, $account_name, $phone_number);
    }

    /**
     * @throws \Exception
     */
    public function getBalance($account_number, $mfi_id)
    {

        return $this->mfiService->getAccountBalance($account_number, $mfi_id);
    }
    public function getMiniStatement(string $account_number, string $from_date, string $to_date, string $mfi_id)
    {
        return $this->mfiService->getMiniStatement($account_number, $from_date, $to_date, $mfi_id);
    }
    public function getLoanHistory(string $account_number, string $from_date, string $to_date, string $mfi_id)
    {
        return $this->mfiService->getLoanHistory($account_number, $from_date, $to_date, $mfi_id);
    }

    public function applyLoan($mfi_id, $user_id, $purpose, $amount, $repayment_period, $requested_by, $account_number)
    {
        return $this->mfiService->applyLoan($mfi_id, $user_id, $purpose, $amount, $repayment_period, $requested_by, $account_number);
    }

    /**
     * @throws \Exception
     */
    public function withDrawMfiAmount($account_number, $mfi_id, $amount)
    {

        return $this->mfiService->withdrawAmount($account_number, $mfi_id, $amount);
    }
    public function depositMfiAmount($account_number, $mfi_id, $amount)
    {

        return $this->mfiService->depositAmount($account_number, $mfi_id, $amount);
    }
}
