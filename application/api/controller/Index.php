<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Config;
use think\Db;
use fast\Random;
use think\Request;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['backTips','getBannerData'];
    protected $noNeedRight = [''];

    /**
     * 轮播图
     */
    public function getBannerData()
    {
        if ($this->request->isGet()) {
            $res = Db::name('banner')->where(['status' => 'normal'])->order('weigh desc')->field('id,path_file')->select();
            foreach ($res as $key => $value) {
                $res[$key]['path_file'] =  domain() . $value['path_file'];
            }
            $this->success('获取成功', $res);
        }
        $this->error('请求方式错误');
    }
    
   /**
     * 返回充值金额 和客服
     */
    public function getSysInfo()
    {
        $config = \config('site');
        $data = [
            'recharge_num' => explode(',',$config['recharge_num']),
            'balance_use_explain' =>$config['balance_use_explain'],
            'customer_weixin' => $config['customer_weixin']
        ];
        $this->success('获取成功',$data);
    }
    

    /**
     * 系统信息
     * user_agreement 用户协议
     * balance_use_explain 余额使用说明
     */
    public function getSiteContent()
    {
        $type = $this->request->get('type');
        $configs = Config::where('group', 'example')->select();
        $res = array_column($configs, 'value', 'name');
        if ($type == 1) {
            $data = $res['user_agreement']; 
        } else if ($type == 2) {
            $data = $res['balance_use_explain'];
        }
        $this->success('获取成功', ['content' => $data]);
    }
    /**
     * 我的预约 1 核销 0 预约 
     */
    public function myMakeInfo()
    {
        if ($this->request->isGet()) {
            $user_info = $this->auth->getUser();
            $status = $this->request->get('status') ? 1 : 0;
            $page = $this->request->get('page');
            $limit = $this->request->get('limit');
            $offset = ($page - 1) * $limit;
            $res = Db::table('yy_make_info')
                ->alias(['yy_make_info' => 'info', 'yy_place' => 'place'])->join('yy_place', 'info.place_id= place.id')
                ->where(['info.uid'=>$user_info->id,'info.status'=>$status])
                ->order('id desc')->limit($offset, $limit)->field('room,seat,place.name,info.place_id,info.money,place.cover_image,place.business_time,place.address,info.id,code,make_year,make_time')->select();
            if (!$res) {
                $this->success('暂无数据',[]);
            } else {
                foreach ($res as $key => $value) {
                    //场地  场次 人数 金额
                    $res[$key]['make_year'] = date('Y-m-d', $value['make_year']);
                    $res[$key]['room'] = Db::name('place_room')->where(['id'=>$value['room'],'place_id'=>$value['place_id']])->value('name');
                    $res[$key]['cover_image'] = domain() .$value['cover_image'];
                    $res[$key]['seat'] = Db::name('seat')->where(['id'=>$value['seat'],'place_id'=>$value['place_id']])->value('name');
                    $res[$key]['people_num'] = Db::name('make_user_info')->where('make_id',$value['id'])->value('number')?Db::name('make_user_info')->where('make_id',$value['id'])->value('number'):0;
                }
                $this->success('获取成功', $res);
            }
        }
        $this->error('请求方式错误');
    }

    /**
     * 预约详情
     */

    public function makeInfoDetail()
    {
        if ($this->request->isGet()) {
            $make_id = $this->request->get('make_id');
            if (empty($make_id)) $this->error('参数错误');
            $res = Db::table('yy_make_info')
                ->where('info.id',$make_id) 
                ->alias(['yy_make_info' => 'info', 'yy_place' => 'place'])->join('yy_place', 'info.place_id= place.id')
                ->field('place_id,place.name,place.cover_image,place.business_time,place.address,place.address,
                            info.id,code,info.status,make_year,make_time,info.createtime,info.money,info.room,info.seat,info.pay_type,info.cancel_time')
                ->find();
            if (!$res) {
                $this->error('暂无数据');
            } else {
                $res['make_year'] = date('Y-m-d', $res['make_year']);
                $res['createtime'] = date('Y-m-d H:i:s', $res['createtime']);
                $res['cancel_time'] = $res['cancel_time']?date('Y-m-d H:i:s', $res['cancel_time']):0;
                $res['make_user_info'] = $this->backUserMakeInfo($res['id']);
                $res['room'] = Db::name('place_room')->where(['id'=>$res['room'],'place_id'=>$res['place_id']])->value('name');
                $res['cover_image'] = domain() .$res['cover_image'];
                $res['seat'] = Db::name('seat')->where(['id'=>$res['seat'],'place_id'=>$res['place_id']])->value('name');
                $res['people_num'] = Db::name('make_user_info')->where('make_id',$make_id)->value('number')?Db::name('make_user_info')->where('make_id',$make_id)->value('number'):0;
                $this->success('获取成功', $res);
            }
        }
        $this->error('请求方式错误');
    }

    /**
     * 返回预约用户信息
     */
    public function backUserMakeInfo($make_id)
    {
        return Db::table('yy_make_user_info')->where('make_id', $make_id)->field('name,mobile,address')->select();
    }
        /**
     * 返回提示消息
     */
    public function backTips()
    {
        $room = $this->request->post('room');
        //查找该场所配置信息
        $room_info = Db::name('place_room')->where(['id' => $room])->find();
        $config =  \config('site');
        $new_slof = explode('-',$room_info['slof_time']);
        $before_time = date('H',time());
        if ($before_time >= $new_slof[0] && $before_time <= $new_slof[1]) {
            $tips = $config['welfare_tips'];
        } else {
            $tips = $config['no_welfare_tips'];
        }
        $data = ['tips' => $tips];
        $this->success('请求成功',$data);
    }
}
