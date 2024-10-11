<?php

namespace app\api\controller;


use app\common\controller\Api;
use app\common\model\User As userModel;
use app\service\PayService;
use fast\Random;
use think\Config;
use think\Db;
use think\Env;
use think\Log;
use Yansongda\Pay\Pay;
/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['applyRefund','rechargeMoney','changepwd','changeavter','login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third', 'weapp','getOpenidByCode','decryptPhone','callbackPay','getRank'];
    protected $noNeedRight = '*';
    protected $appid = '';
    protected $secret = '';
    public function _initialize()
    {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('用户中心已经关闭'));
        }
    }

    /**
     * 会员中心  个人中心
     */
    public function getUserInfo()
    {
        $user = $this->auth->getUser();
        $data = [
            'userInfo' =>$user
        ];
        $this->success('获取成功', $data);
    }


     /**
     * 获取密码加密后的字符串
     * @param string $password 密码
     * @param string $salt     密码盐
     * @return string
     */
    public function getEncryptPassword($password, $salt = '')
    {
        return md5(md5($password) . $salt);
    }


    /**
     * 根据code获取openid
     */
    public function getOpenidByCode()
    {
       //code值，从前端获取
        $code = $this->request->post("code");
        if (!$code) $this->error(__('参数错误'));
        $config = \config('weixin');
        //注册时获取
        $appid = $config['mp_appid'];
        $secret = $config['mp_accessKey'];

        $get_code_url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $appid . '&secret=' . $secret . '&js_code=' . $code . '&grant_type=authorization_code';
        $ret = file_get_contents($get_code_url);
        $ret = json_decode($ret);
        if(isset($ret->errcode)){
            $this->error('请重新授权');
        }
        $openid = $ret->{'openid'};
        $user = \app\common\model\User::where('mp_wx_openid', $openid)->find();
         if (!$user) {
            $this->success('请填写个人信息', ['openid' => $openid]);
        } else {
            $this->auth->direct($user->id);
            $userinfo = $this->auth->getUserinfo();
            $data = [
                'userinfo' => $userinfo
            ];
            $this->success(__('登录成功'), $data);
        }
    }

    /**
    *授权手机号
    */
    public function decryptPhone()
    {
        $code = input('post.code','');
        $openid = input('post.openid','');
        $config = \config('weixin');
        //注册时获取
        $appid = $config['mp_appid'];
        $secret = $config['mp_accessKey'];
        $get_token = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
        $ret = file_get_contents($get_token);
        $ret = json_decode($ret);
        $token = $ret->access_token;
        $url = "https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token=$token";
        $data['code'] = $code;
        $res = $this->http_request($url, json_encode($data), 'json');
        $res = json_decode($res, true);
        if(isset($res['errcode']) && $res['errcode'] == 0){
            $mobile = Db::name('user')->where('mobile',$res['phone_info']['phoneNumber'])->find();
            if($mobile) $this->error('该手机号已授权');
            $arr['mobile'] = $res['phone_info']['phoneNumber'];
            $arr['nickname'] = 'YYD'.Random::alnum(7);
            $arr['unine_key'] = Random::alnum(8);
            $arr['avatar'] = '/static/images/default_head.png';
            $user = Db::name('user')->where('mp_wx_openid', $openid)->find();
            if (!$user) {
                $arr['mp_wx_openid'] = $openid;
                $arr['openid'] = $openid;
                $id = Db::name('user')->insertGetId($arr);
                $this->auth->direct($id);    
            } else {
                $this->auth->direct($user->id);
            }
            $userinfo = $this->auth->getUserinfo();
            $data = [
                'userinfo' => $userinfo
            ];
            $this->success('登录成功', $data);
            }else{
                $this->error('获取失败');
            }
    }
    
    public function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            ));
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);

        return $output;
    }


    public function createQrcode()
    {
        try {
            $user_info = $this->auth->getUser();
            $str = str_pad($user_info['id'], 5,"0",STR_PAD_LEFT);
            /**如果路径不存在时附件不存在*/
            $filename = 'E'.$str.'.png';
            if(!file_exists(ROOT_PATH . DS . 'public' . DS . 'qrcode')){
                mkdir(ROOT_PATH . DS . 'public' . DS . 'qrcode',0777);
            }
            $path =   '/qrcode/'. $filename;
            $filepath = ROOT_PATH .DS . 'public' . $path;
            //$return_filepath = getDomain(). DS . 'qrcode'. DS . $filename;
            if(!file_exists($filepath)){
                 //获取密钥
                 $config = \config('weixin');
                //注册时获取
                $appid = $config['mp_appid'];
                $secret = $config['mp_accessKey'];

                $get_token = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
                $ret = file_get_contents($get_token);
                $ret = json_decode($ret);
                $token = $ret->access_token;
                //获取小程序二维码
                $data = [
                    'page'=>'pages/tabBar/my',
                    'scene'=> 'promoter='.$user_info['id'],
                    'check_path'=> Env::get('environment', 'release') == 'trial' ? false: true,
                    'env_version'=> Env::get('environment', 'release'),
                ];
                $result = $this->http_request('https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$token, json_encode($data));
                $rr = json_decode($result);
                if(isset($rr->errcode)){
                    $this->error('下载失败');
                }else{
                    file_put_contents($filepath, $result);
                }
            }
        } catch (\Exception $e) {
           $this->error('获取失败');
        }
        $this->success('获取成功',['url'=>$path]);
    }

    public function getTeamList()
    {
        $user_info = $this->auth->getUser();
        $page = $this->request->get('page');
        $limit = $this->request->get('limit');
        $offset = ($page - 1) * $limit;
        $res = userModel::where('promoter',$user_info->id)->order('id desc')->limit($offset, $limit)->field('id,nickname,mobile,avatar,id_card')->select();
        if(!empty($res)){
           $this->_dataTrees(userModel::field('id,promoter')->select(),$user_info->id);
           foreach($res as &$v){
              $v->is_certification = !empty($v->id_card) ? 1 : 2;
              unset($v->id_card);
           }
        }
        $result = [
            'promoter'=>$res,
            'promoter_num'=>$user_info->promoter_num,
            'promoter_all_num'=> count($this->tree),
        ];
        $this->success('获取成功', $result);
    }

    public  $tree = [];

     /**
     * 生成节点树
     *
     * @param $data
     * @param int $pid
     * @return array
     */
    public  function _dataTrees($data, $pid = 0)
    {
        foreach ($data as $v) {
            if ($v['promoter'] == $pid) {
                if(!in_array($v->id,array_column($this->tree, 'id'))){
                    $this->tree[] = [
                        'id' => $v['id'],
                        'promoter' => $v['promoter'],
                    ];
                }
                $this->_dataTrees($data, $v->id);
            }
        }
    }

    /**
     * 修改会员个人信息
     *
     * @ApiMethod (POST)
     * @param string $avatar 头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio 个人简介
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        // $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
        $nickname = $this->request->post('nickname');
        $user->nickname = $nickname;
        // $user->avatar = $avatar;
        $user->save();
        $this->success('编辑资料成功');
    }

    /**
     * 资金明细
     */
    public function getMoneyDetail()
    {
        if($this->request->isGet()){
            $user_info = $this->auth->getUser();
            $page = $this->request->get('page');
            $limit = $this->request->get('limit');
            $offset = ($page - 1) * $limit;
            $month  = $this->request->get('month');
            $map['user_id'] = $user_info->id;
            if($month){
                $map['time'] = $month;
            }
            $res = Db::name('user_money_log')->where($map)->order('id desc')->limit($offset, $limit)->field('memo,after,money,createtime,type')->select();
            if($res){
                foreach($res as $k=>$v){
                    if($v['type'] == 1 || $v['type'] == 3){
                        $res[$k]['money'] = '+'.$v['money'];
                    }else{
                        $res[$k]['money'] = '-'.$v['money'];
                    }
                    $res[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                }
                $this->success('获取成功',$res);
            }else{
                $this->success('暂无数据',[]);
            }
        }
        $this->error('请求方式错误');
    }

    /**
     * 资金充值
     */
    public function rechargeMoney()
    {
        if($this->request->isPost()){
            $user = $this->auth->getUser();
            $money = $this->request->post('money');
            if(empty($user['mobile'])) $this->error('请先绑定手机号');
            if(empty($money)) $this->error('请输入充值金额');
            $str = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 4);//订单号
            $data = [
                'order_number'=> $str,
                'order_remark'=> '线上充值',
                'mobile'=> $user['mobile'],
                'user_id'=> $user['id'],
                'total_fee'=> $money,//分
                'actual_fee'=> $money,//分
                'create_time'=> time(),
                'update_time'=> time(),
                'type' =>1,//收入
             ];
            $this->wechatPay($data,[],$str,1);
        }
        $this->error('请求方式错误');
    }

    /**
     * 微信充值
     */
    public function wechatPay($data,$array,$str,$make_count)
    {
        try{
            //生成总订单
            $rechargeId = Db::name('recharge_order')->insertGetId($data);
            $arr = [];
            foreach ($array as $key => $value) {
                $arr[$key]['pid'] = $rechargeId;
                $arr[$key]['make_id'] = $value;
                $arr[$key]['total_fee'] = $data['total_fee']/$make_count;
                $arr[$key]['create_time'] = time();
            }
            Db::name('recharge_son_order')->insertAll($arr);
            $res = (new PayService)->wechatPay($str);
            if(!$res){
                return $this->error('支付授权失败'); 
            }
        }catch(\Exception $e){
            Log::error('微信支付授权:'.$e->getMessage());
            return $this->error('支付授权失败'); 
        }
        return $this->success('支付授权成功',$res);
    }

    /**
     * 申请退款
     */
    public function applyRefund()
    {
        if($this->request->isGet()){
           $make_id = $this->request->get('make_id');
            if(empty($make_id)) $this->error('请传要退款订单ID');
            $makeInfo = Db::name('make_info')->where(['id'=>$make_id])->find();
            if(!$makeInfo) $this->error('预约信息不存在');
            $user = Db::name('user')->where('id',$makeInfo['uid'])->find();
            //拆分预约时间段
            $new_make_time = explode('-',$makeInfo['make_time']);
            $new_date_time = date('Y-m-d',$makeInfo['make_year']).' '.$new_make_time[0];
            $strtotime = strtotime($new_date_time); //预约时间戳
            if($user['member_type'] !=2 && ($makeInfo['money'] > 0)){
                if(($strtotime - time()) < 86400){
                    $this->error('24小时内订单不可申请退款');
                }
            }
            if($makeInfo['pay_type'] == 2){
                //根据预约ID找到总订单ID
                $sonOrder = Db::name('recharge_son_order')->where(['make_id' => $make_id])->field('id,pid')->find();
                $str = Db::name('recharge_order')->where(['id' => $sonOrder['pid'],'status' => 1])->value('order_number');
                $res = (new PayService)->refund($str,$sonOrder['id']);
                if ($res['return_code'] == 'SUCCESS') {
                   $this->refundCallback($user,$res['out_trade_no'],$make_id,$sonOrder['id']); 
                }
            }else{
                $this->balanceCallback($user,$makeInfo);
            }
        }
        $this->error('请求方式错误');
    }
    
    //零钱退款回调
    public function balanceCallback($user,$makeInfo)
    {
        //更新预约信息
        $res = Db::name('make_info')->where('id',$makeInfo['id'])->update(['status'=>2]);
        if($makeInfo['money'] > 0){
            //用户余额递增
        Db::name('user')->where('id',$user['id'])->setInc('money',$makeInfo['money']);
            //生成记录
            $arr = [
                'type' => 3,//退款
                'user_id'=>$user['id'],
                'money'=> $makeInfo['money'],
                'before'=>$user['money'],
                'after' =>$user['money']+$makeInfo['money'],
                'memo' => '预约单号'.$makeInfo['code'].'余额退款',
                'createtime'=>time(),
                'order_num'=>$makeInfo['code'],
                'time'=> date('Y-m',time()),
            ];
            $ret = Db::name('user_money_log')->insert($arr); 
        }else{
            $ret = 1;
        }
        if($res && $ret){
            return $this->success('退款申请成功');
        }else{
            return $this->error('退款申请失败');
        } 
    }
    
    //退款回调
    public function refundCallback($user,$out_trade_no,$make_id,$sid)
    {
        //订单信息
        $order_info = Db::name('recharge_order')->where(['order_number'=>$out_trade_no,'status'=> 1,'user_id'=>$user['id']])->find(); 
        if(!$order_info)$this->error('订单不存在');

        $code = Db::name('make_info')->where('id',$make_id)->value('code');
        // $res = Db::name('recharge_order')->where('id',$order_info['id'])->update(['status'=>3]);
        //更新预约信息
        $res = Db::name('make_info')->where('id',$make_id)->update(['status'=>2]);
        //更新子订单表退款状态
        $ref = Db::name('recharge_son_order')->where('id',$sid)->update(['status' =>3]);
        //生成记录
        $arr = [
            'type' => 3,//退款
            'user_id'=>$user['id'],
            'money'=> 0,
            'before'=>$user['money'],
            'after' =>$user['money'],
            'memo' => '预约单号'.$code.'微信退款',
            'createtime'=>time(),
            'order_num'=>$order_info['order_number'],
            'time'=> date('Y-m',time()),
        ];
        $ret = Db::name('user_money_log')->insert($arr); 
        if($res && $ret && $ref){
            return $this->success('退款申请成功');
        }else{
            return $this->error('退款申请失败');
        } 
    }
    /**
     * 支付回调
     */
    public function callbackPay()
    {
        (new PayService)->notifyCallback();
    }

}
