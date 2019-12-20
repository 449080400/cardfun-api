<?php

return [
    'alipay' => [
        'app_id'         => env('ALIPAY_APP_ID'),
        'ali_public_key' => env('ALIPAY_PUBLIC_KEY'),
        'private_key'    => env('ALIPAY_PRIVATE_KEY'),
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => env('WECHATPAY_APP_ID'),
        'miniapp_id'  => env('WECHATPAY_MINIAPP_ID'),
        'mch_id'      => env('WECHATPAY_MCH_ID'),
        'key'         => env('WECHATPAY_KEY'),
        'cert_client' => config_path('cert/wechat/apiclient_cert.pem'),
        'cert_key'    => config_path('cert/wechat/apiclient_key.pem'),
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];
