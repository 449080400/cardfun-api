<?php

return [
    'alipay_vod' => [
        'region_id' => 'cn-shanghai',
        'access_key' => env('ALIYUN_ACCESS_KEY'),
        'access_key_secret' => env('ALIYUN_ACCESS_KEY_SECRET'),
        // 推流域名
        'push_domain' => '',
        // 鉴权key
        'auth_key' => '',
        // 鉴权有效时间 单位秒
        'auth_timestamp' => '1800',
        // 鉴权rand 随机数，一般设成0
        'auth_rand' => '0',
        // 鉴权uid 暂未使用（设置成0即可)
        'auth_uid' => '0',
        // 播放域名
        'vhost' => '', //改成自己的播放域名
        // 播放类型 rtmp flv m3u8
        'play_type' => 'm3u8',
        ]
];
