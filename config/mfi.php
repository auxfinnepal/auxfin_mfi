<?php

return [
    "api"=>env('MFI_API_URL',"https://mfi.umva.org"),
    "client_id"=>env('MFI_CLIENT_ID',''),
    "client_secret"=>env('MFI_CLIENT_SECRET',''),
    "mfi_model"=>env('MFI_MODEL', \App\Models\Mfi::class),
    "sms_gateway"=>[
        "enabled"=>env('SMS_GATEWAY_ENABLED', true),
        "default_provider"=>env('SMS_GATEWAY_DEFAULT_PROVIDER', 'ugafode'),
    ],
];
