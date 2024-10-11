<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Bank;
use app\common\model\IntegralConfig;
use app\common\model\IntegralGoods;
use app\common\model\IntegralOrder;
use app\common\model\ScoreLog;
use think\Db;


/**
 * 首页接口
 */
class Integral extends Api
{
    protected $noNeedLogin = ['getBank','priceList'];
    protected $noNeedRight = ['*'];

    /**
     * 银行
     *
     */
    public function getBank()
    {
        $banks = Bank::where('status',1)->order('weigh desc')->field('id,name,image')->select();
        $this->success('获取成功', $banks);
        
    }

    public function priceList()
    {
        $res = IntegralConfig::where('name','price_list')->find();
        $this->success('获取成功', ['content'=>$res->value]);
    }

    public function integralGoods()
    {
        $bank_id = $this->request->get('bank_id');
        $page = $this->request->get('page');
        $limit = $this->request->get('limit');
        $offset = ($page - 1) * $limit;
        $res = IntegralGoods::where(['bank_id'=>$bank_id,'status'=>1])->order('id desc')->limit($offset, $limit)->field('id,name,advantage,since_unit,change_number,Integral_value')->select();
        foreach($res as &$v){
            $v['since_unit'] = substr($v['since_unit']/10000, 0, 5);
        }
        $restult = [
           'integral_query' => Bank::where('id',$bank_id)->value('integral_query'),
           'goods'=>$res
        ];
        $this->success('获取成功', $restult);
    }
 
    public function integralGoodsDetail()
    {
        $goods_id = $this->request->get('goods_id');
        $res = IntegralGoods::where('id',$goods_id)->field('id,name,advantage,since_unit,change_number,integral_value')->find();
        if(!empty($res)){
            $res->price = round($res->since_unit/10000*$res->integral_value, 2);
        }
        $this->success('获取成功', $res);
    }

     /**
     * 申请业务
     */
    public function applyBusiness()
    {
        $str = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $user = $this->auth->getUser();
        $time = time();
        $data = [
            'integral_goods_id'=>$this->request->post('integral_goods_id'),
            'user_id'=>$user['id'],
            'create_time'=>$time,
            'update_time'=>$time,
            'order_number'=>$str,
        ];
        if(empty($data['integral_goods_id']) ){
            $this->error('参数错误');
        }
        if(IntegralOrder::where('user_id',$user['id'])->whereIn('status',[1,2])->find()){
            $this->error('该业务已经申请过');
        }
        $busines_content = IntegralGoods::where(['id'=>$data['integral_goods_id'],'status'=> 1])->find();
        if(empty($busines_content)){
            $this->error('该业务已暂停');
        }
        $data['bank_id'] = $busines_content->bank_id;
        $data['integral_goods_name'] = $busines_content->name;
        $data['since_unit'] = $busines_content->since_unit;
        $data['integral_value'] = $busines_content->integral_value;
        $res = IntegralOrder::insert($data);
        if ($res) {
            $this->success('提交成功', []);
        } else {
            $this->error('提交失败');
        }
    }

    public function getList()
    {
        $status = $this->request->get('status');
        $page = $this->request->get('page');
        $limit = $this->request->get('limit');
        $offset = ($page - 1) * $limit;
        $result = IntegralOrder::where('status',$status)->order('id desc')->limit($offset, $limit)->field('id,order_number,integral_goods_name,create_time,since_unit,integral_value')->select();
        if(!empty($result)){
            foreach($result as &$v){
                $v->create_time = date('Y-m-d H:i:s',$v->create_time);
                $v->price = round($v->since_unit/10000*$v->integral_value, 2);
            }
        }
        $this->success('获取成功', $result);
    }

    public function getScoreList()
    {
        $page = $this->request->get('page');
        $limit = $this->request->get('limit');
        $offset = ($page - 1) * $limit;
        $user = $this->auth->getUser();
        $result = ScoreLog::where('user_id', $user['id'])->order('id desc')->limit($offset, $limit)->field('id,score,memo,createtime')->select();
        if(!empty($result)){
            foreach($result as &$v){
                $v->createtime = date('Y-m-d H:i:s',$v->createtime);
            }
        }
        $this->success('获取成功', $result);
    }

}
