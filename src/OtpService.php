<?php

namespace Auxfin\Mfi;

use App\Models\SsoToken;
use Carbon\Carbon;
use GuzzleHttp\Client;

class OtpService
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

    public function generateOtp(int $mfi_id,int $user_id,string $purpose)
    {
        try {
            $token = $this->mfiService->getMfiToken();

            $response = $this->client->post(
                $this->apiUrl . '/api/otp/generate',
                [
                    'form_params' => [
                        "mfi_id" => $mfi_id,
                        "purpose" => $purpose,
                        "user_id" => $user_id
                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );
            return json_decode($response->getBody()->getContents());
        }catch (\Exception $e){
            throw $e;
        }
    }

    public function verifyOtp(int $mfi_id, int $user_id,string $otp,string $purpose)
    {
        try {
            $token = $this->mfiService->getMfiToken();

            $response = $this->client->post(
                $this->apiUrl . '/api/otp/verify',
                [
                    'form_params' => [
                        "mfi_id" => $mfi_id,
                        "user_id" => $user_id,
                        "otp" => $otp,
                        "purpose" => $purpose
                    ],
                    "headers" => [
                        "Authorization" => "Bearer $token"
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents());
        }catch (\Exception $e){
            throw $e;
        }
    }


}
