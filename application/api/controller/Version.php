<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Config;
use think\Db;
use think\Log;



/**
 * 版本接口
 */
class Version extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function update()
    {
       $version = $this->request->param('version');
       Log::info('App版本号：'.$version);
       $configs = Config::where('group','basic')->select();
       $res = array_column($configs,'value','name');
       $result = [
           'status'=> 0,
           'url'=> ''
       ]; 
       if($res['version'] != $version){
            $result = [
                'status'=> 1,
                'url'=> getDomain().'/apk/huiyin.apk',
                'note'=> $res['app_note']
            ]; 
       }
       $this->success('获取成功', $result);
    }

   

}
