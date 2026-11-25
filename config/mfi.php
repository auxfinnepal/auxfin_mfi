<?php

return [
    "api"=>env('MFI_API_URL',"https://mfi.umva.org"),
    // Upstream hosts sent to mfi_connect with each proxy request; mfi_connect allows only hosts it also lists.
    "upstream_allowed_hosts"=>array_values(array_filter(array_map(
        'strtolower',
        array_map('trim', explode(',', (string) env('UPSTREAM_ALLOWED_HOSTS', '')))
    ))),
    "client_id"=>env('MFI_CLIENT_ID',''),
    "client_secret"=>env('MFI_CLIENT_SECRET',''),
    "mfi_model"=>env('MFI_MODEL', \App\Models\Mfi::class),
    "sms_gateway"=>[
        "enabled"=>env('SMS_GATEWAY_ENABLED', true),
        "default_provider"=>env('SMS_GATEWAY_DEFAULT_PROVIDER', 'ugafode'),
    ],
];
