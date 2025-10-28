<?php

namespace Auxfin\Mfi\Traits;

use Auxfin\Mfi\MfiService;
use Auxfin\Mfi\OtpService;

trait useOtp
{

    private OtpService $otpService;


    public function __construct()
    {

        $this->otpService = new OtpService();
    }

    public function generateOtp(int $mfi_id,int $user_id,string $purpose){
        return $this->otpService->generateOtp($mfi_id, $user_id, $purpose);
    }
    public function verifyOtp(int $mfi_id,int $user_id,string $purpose,string $otp)
    {
        return $this->otpService->verifyOtp($mfi_id, $user_id, $otp, $purpose);
    }


}
