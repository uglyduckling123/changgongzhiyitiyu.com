<?php
use think\Env;

return [    
    'wechat' => [
        'appid' => '',
        'app_id' => '',
        'miniapp_id' => 'wx10d70e199f7e85bb', //小程序APPID
        'mch_id' => '1622521469',//商户号
        'key' => 'yx258d55ac66fcbeade7a2e14e757btq',//秘钥
        'notify_url' => 'https://www.changgongzhiyitiyu.com/callback',
        'cert_client' => APP_PATH .'service'.DS.'cert'.DS.'apiclient_cert.pem',
        'cert_key' => APP_PATH .'service'.DS.'cert'.DS.'apiclient_key.pem', 
        'log' => [ // optional
            'file' => './logs/wechat.log',
            'level' => 'info',
            'type' => 'single',
            'max_file' => 30,
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
        ],
        // 'mode' => 'dev',
    ],
 
];