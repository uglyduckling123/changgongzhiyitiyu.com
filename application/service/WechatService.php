<?php
namespace app\service;

use app\common\basics\Logic;
use app\common\model\wechat\Wechat;
use app\common\model\wechat\WechatReply;
use app\common\server\WeChatServer;
use EasyWeChat\Kernel\Exceptions\Exception;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Factory;
use think\Log;

class WechatService 
{

    /**
     * 获取微信配置
     * @param $url
     * @return array|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function jsConfig($url)
    {
        $config = [
            'app_id' => 'wxe9e17ab0ac0202de',
            'secret' => '8fa2ed876d1468268874dbe65413308e',
            'mch_id' => '',
            'key' => 'H0anvznuUh4qoUQhJQwx3UYZrTNOiWOiNlsGC9QXzaP',
            'token' => 'd6d427ee9aa7b083d1c4ea1fd4f9e286',
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => '../runtime/log/wechat.log'
            ],
        ];
        $app = Factory::officialAccount($config);
        $url = urldecode($url);
        $app->jssdk->setUrl($url);
        //$apis = ['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone', 'openLocation', 'getLocation', 'chooseWXPay', 'updateAppMessageShareData', 'updateTimelineShareData', 'openAddress'];
        $apis = ['updateTimelineShareData','updateAppMessageShareData'];
        try {
            return  $app->jssdk->getConfigArray($apis, $debug = false, $beta = false);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}