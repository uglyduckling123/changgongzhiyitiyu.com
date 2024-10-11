<?php

namespace app\service;

use app\common\model\Order;
use app\common\model\User;
use app\common\model\UserAgent;
use app\common\model\PayLog;
use app\common\model\Config  as configModel;
use app\common\model\MoneyLog;
use app\common\model\RechargeOrder;
use Yansongda\Pay\Pay;
use think\Log;
use think\Config;
use think\Db;

class PayService
{
    
    /**
     * 微信支付
     */
    public function wechatPay($order_number)
    {
        $order = Db::name('recharge_order')->where('order_number','=',$order_number)->find();
        if (!$order || $order->status != 0) {
            return false;
        }
        $user = User::where('id','=',$order['user_id'])->find();
        Log::info('微信支付授权用户:'.json_encode($user));
        if(empty($user) || empty($user->openid)){
            Log::info('该用户未授权');
            return false;
        }
        $data = [
            'out_trade_no' => $order['order_number'],
            'body' => '订单'.$order['order_number'].'支付',
            'total_fee' => $order['total_fee']*100,
            'openid' => $user->openid,
        ];
        try{
            $config = Config::get('pay.wechat');//获取配置参数
            // $config['miniapp_id'] = \app\common\model\Config::get(array('id' => 24))['value'];

            $wechat = Pay::wechat($config);
            $pay = $wechat->miniapp($data);
            Log::info('微信支付授权:'.json_encode($pay));
            if(!isset($pay['paySign']) || empty($pay['paySign'])){
                return false;
            }
        }catch(\Exception $e){
            Log::error('微信支付授权:'.$e->getMessage());
            return false;
        }
        return $pay;
    }
    
    /**
     * 退款
     */
    public function refund($order_number,$sid)
    {
        $order = RechargeOrder::where('order_number', '=', $order_number)->find();
        if (!$order || $order->status != 1) {
            return false;
        }
        $user = User::where('id', '=', $order->user_id)->find();
        if (empty($user) || empty($user->openid)) {
            Log::info('该用户未授权');
            return false;
        }
        $total_fee = Db::name('recharge_son_order')->where('id',$sid)->value('total_fee');
         $data = [
            'out_trade_no' => $order->order_number,
            'out_refund_no' => time(),
            'total_fee' => $order->total_fee *100,
            'refund_fee' => $total_fee *100,
            'refund_desc' => '支付单号' . $order['order_number'] . '退款',
            'type' => 'miniapp',
        ];
        // halt($data);
        $config = Config::get('pay.wechat'); //获取配置参数
        $wechat = Pay::wechat($config);
        $pay = $wechat->refund($data);
        return $pay;
    }
    

    /**
     * 支付回调
     */
    public function notifyCallback()
    {
            Log::info('-----------------支付回调---------------------------------------------------');
            $config = Config::get('pay.wechat');//获取配置参数
            $wechat = Pay::wechat($config);
        try{
            $data = $wechat->verify();
             Log::info('微信支付回调：'.json_encode($data));
             if(isset($data['return_code']) && $data['return_code'] == "SUCCESS" && $data['result_code'] == "SUCCESS"){
                $out_trade_no = $data['out_trade_no'];
                $order_info = RechargeOrder::where(['order_number'=>$data['out_trade_no'],'status'=> 0])->field('id,user_id,status,update_time,total_fee,actual_fee,order_remark,type')->find();
                if($order_info){
                    if($data['mch_id'] == $config['mch_id'] && $data['total_fee']/100 == $order_info['actual_fee']){
                       
                        $order_info->status = 1;
                        $order_info->update_time = time();
                        $order_res = $order_info->save();
                        if(!$order_res){
                            Log::error('微信支付回调：订单号'.$out_trade_no.'支付成功，更新状态失败');
                            return $wechat->success()->send();
                        }
                        //更新子订单
                        Db::name('recharge_son_order')->where('pid',$order_info['id'])->update(['status' => 1]);
                        $user_res = User::where('id',$order_info->user_id)->find();
                        if(!$user_res){
                            Log::error('微信支付回调：订单号'.$out_trade_no.'支付成功，查询用户：'.$order_info->user_id.'失败');
                            return $wechat->success()->send();
                        }
                        //更新支付日志
                        //充值
                        if($order_info['type'] == 1){
                            //更新用户账户余额
                            $arr['money'] = $user_res['money'] + $order_info['total_fee'];
                            $arr['updatetime'] = time();
                            Db::name('user')->where('id',$order_info->user_id)->update($arr);
                            //生成记录
                            $arr = [
                                'type' => 1,
                                'user_id'=>$user_res['id'],
                                'money'=> $order_info['total_fee'],
                                'before'=>$user_res['money'],
                                'after' =>$user_res['money'] + $order_info['total_fee'],
                                'memo' => $order_info['order_remark'],
                                'createtime'=>time(),
                                'order_num'=>$data['out_trade_no'],
                                'time'=> date('Y-m',time()),
                            ];
                        }else{
                            $make_info = Db::name('recharge_son_order')->where('pid',$order_info['id'])->field('make_id')->select();
                            $make_ids = [];
                            foreach($make_info as &$v){
                                array_push($make_ids,$v['make_id']);
                            }
                            //批量更新预约单
                            $map['id'] = ['in',$make_ids];
                            Db::name('make_info')->where($map)->update(['status' => 0]);
                            //生成记录
                            $arr = [
                                'type' => 2,
                                'user_id' => $user_res['id'],
                                'money' => $order_info['total_fee'],
                                'before' => $user_res['money'],
                                'after' => $user_res['money'],
                                'memo' => $order_info['order_remark'],
                                'createtime' => time(),
                                'order_num' => $data['out_trade_no'],
                                'time' => date('Y-m', time()),
                            ];
                        }
                        Db::name('user_money_log')->insert($arr);
           //-----------------------------------------------------------------------------------------------
                        
                    }else{
                        Log::error('微信支付回调：订单号'.$out_trade_no.'支付成功，更新状态失败');
                        return $wechat->success()->send();
                    }
                }else{
                  Log::error('微信支付回调：订单支付成功，订单号'.$out_trade_no.'不存在');
                  return $wechat->success()->send();
                }
               
            }else{
                $order_info =  Order::where([['order_number','=',$data['out_trade_no']],['status','=',0]])->field('user_id,mobile,status,update_time')->find();
                if($order_info && $data['mch_id'] == $config['mch_id'] && $data['total_fee']/100 == $order_info['actual_fee']){
                    $order_info->status = 2;
                    $order_info->update_time = time();
                    $order_res = $order_info->save();
                }
               Log::error('微信支付回调：订单支付失败，订单号'.$data['out_trade_no']);
            }
         } catch (\Exception $e) {
            //  halt($e->getMessage());
             Log::error('微信支付回调：'.$e->getMessage());
         }
         
         return $wechat->success()->send();
    }



}
