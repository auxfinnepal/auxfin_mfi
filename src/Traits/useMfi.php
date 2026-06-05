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

    public function getConnected(string $mfi_id, string $user_id, string $type = null)
    {
        return $this->mfiService->getConnected($mfi_id, $user_id, $type);
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
    public function getBalance($mfi_id, $account_number = null,  $pin = null)
    {

        return $this->mfiService->getAccountBalance($mfi_id, $account_number,  $pin);
    }
    public function getMiniStatement($mfi_id, $account_number = null, $from_date = null, $to_date = null, $pin = null, $limit = null)
    {
        return $this->mfiService->getMiniStatement($mfi_id, $account_number, $from_date, $to_date, $pin, $limit);
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
    public function withDrawMfiAmount($mfi_id, $amount, $account_number = null, $pin = null,  $otp_code = null)
    {

        return $this->mfiService->withdrawAmount($mfi_id, $amount, $account_number,  $pin,  $otp_code);
    }
    public function depositMfiAmount($mfi_id, $amount, $account_number = null, $pin = null )
    {

        return $this->mfiService->depositAmount($mfi_id, $amount, $account_number,  $pin);
    }

    public function applyLoanByMfi($mfi_id, $user_id, $purpose, $amount, $repayment_period, $requested_by, $account_number)
    {
        return $this->mfiService->applyLoan($mfi_id, $user_id, $purpose, $amount, $repayment_period, $requested_by, $account_number);
    }
    public function getLoanApplications($mfi_id, $user_id)
    {
        return $this->mfiService->listLoanApplications($mfi_id, $user_id);
    }

    public function getLoans($mfi_id, $user_id)
    {
        return $this->mfiService->listLoans($mfi_id, $user_id);
    }
    public function validateLoan($mfi_id, $user_id,$amount,$application_id,$note,$repayment_period,$interest_rate,$account_number){
        return $this->mfiService->validateLoan($mfi_id, $user_id,$amount,$application_id,$note,$repayment_period,$interest_rate,$account_number);
    }
    public function rejectLoan($application_id,$note,$account_number){
        return $this->mfiService->rejectLoan($application_id,$note,$account_number);
    }

    public function login($mfi_id, $pin )
    {
        return $this->mfiService->login($mfi_id,  $pin);
    }

    public function refreshToken($mfi_id, $refresh_token)
    {
        return $this->mfiService->refreshToken($mfi_id, $refresh_token);
    }

    public function getTransactionStatus($mfi_id, $pin,  $transaction_id)
    {
        return $this->mfiService->getTransactionStatus($mfi_id, $pin, $transaction_id);
    }

}
