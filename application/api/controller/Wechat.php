<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\service\WechatService;
use think\Db;
use think\Log;

/**
 * 首页接口
 */
class Wechat extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function jsConfig()
    {
        $url = $this->request->get('url');
        $result = WechatService::jsConfig($url);
        Log::info(json_encode($result));
        if (!$result || empty($result['signature'])) {
            $this->error(__('获取失败'));
        }
        $this->success('', $result);
    }

    /**
     * 微信授权
    */

    /**
      *获取手机号 
    */

    

}
