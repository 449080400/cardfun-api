<?php

return [
    'wechat' => [
        'member_id'             => env('BAOFU_MEMBER_ID'), //商户号
        'terminal_id'           => env('BAOFU_TERMINAL_ID'), //终端号
        'private_key_password'  => env('BAOFU_PRIVATE_KEY_PASSWORD'), //商户私钥证书密码
        'page_url'              => env('BAOFU_PAGE_URL',''), //正式（true）/测试（false）
        'return_url'            => env('BAOFU_RETURN_URL', ''), //1-服务器和页面通知,0-仅服务器通知,3-不通知
        'version'               => env('BAOFU_VERSION', '4.0.0.2'), //版本号
        'txn_type'              => env('BAOFU_TXN_TYPE', '20199'), //支付交易类型
        'txn_sub_type'          => env('BAOFU_TXN_SUB_TYPE', '05'), //支付交易子类
        'query_txn_type'        => env('BAOFU_QUERY_TXN_TYPE', '20199'), //查询交易类型
        'query_txn_sub_type'    => env('BAOFU_QUERY_TXN_SUB_TYPE', '03'), //查询交易子类
        'data_type'             => env('BAOFU_DATA_TYPE', 'json'), //加密报文的数据类型（xml/json）
        'is_test'               => env('BAOFU_IS_TEST', true), //正式（true）/测试（false）
        'notice_type'           => env('BAOFU_NOTICE_TYPE', 1), //1-服务器和页面通知,0-仅服务器通知,3-不通知
        'cert_pri'              => config_path('cert/baofu/aoshen_pri.pfx'), //注意证书路径是否存在
        'cert_pub'              => config_path('cert/baofu/aoshen_pub.cer'), //注意证书路径是否存在
        'log'                   => [
            'path' => storage_path('logs/'), //日志目录
            'file' => 'baofu_wechat_pay.log', //日志
        ],
    ],
];
