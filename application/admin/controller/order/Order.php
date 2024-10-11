<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use app\common\model\Order As orderModel;
use app\common\model\Config  as configModel;
use app\common\model\User As userModel;
use app\common\model\MoneyLog;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Exception;
use think\Log;

class Order extends Backend
{


    protected $model = null;
    protected $commentsModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new orderModel;
    }

    public function index()
    {
        //当前是否为关联查询
       $this->relationSearch = true;
       //设置过滤方法
       $this->request->filter(['strip_tags', 'trim']);
       if ($this->request->isAjax())
       {
           //如果发送的来源是Selectpage，则转发到Selectpage
           if ($this->request->request('keyField'))
           {
               return $this->selectpage();
           }
           list($where, $sort, $order, $offset, $limit) = $this->buildparams();
           $total = $this->model
                   ->where($where)
                   ->order($sort, $order)
                   ->count();

           $list = $this->model
                   ->where($where)
                   ->order('id desc')
                   ->order($sort, $order)
                   ->limit($offset, $limit)
                   //->field('id,receive_object,title,subtitle,status,create_time,type')
                   ->select();
           foreach ($list as $v) {
           }
           $list = collection($list)->toArray();
           $result = array("total" => $total, "rows" => $list);
           return json($result);
       }
       return $this->view->fetch();
    }


    /**
     * 编辑
     */
     public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if ($this->request->isPost()) {
            $this->token();
            if($row->status != 0){
                $this->error('该订单已处理过');
            }
            $data = $this->request->post("row/a");
            if($data['status'] == 2 && empty($data['remark'])){
                 $this->error('备注不能为空');
            }
            if(strpos($row->reward,'%') === false){
                $data['fashionable_money'] = $row->reward;
            }
           
            if($data['is_fashionable'] == 1  && (empty($data['fashionable_money']) || $data['fashionable_money']<=0) ){
                $this->error('分账金额错误');
            }

            Db::startTrans();
            try{
                //佣金
                $promote_commission_two_percent = ($res = configModel::where('name','promote_commission_two')->value('value'))?$res:0;
                $promote_commission_three_percent = ($res = configModel::where('name','promote_commission_three')->value('value'))?$res:0;
                $data['update_time'] = time();
                $data['promote_commission_two'] = $promote_commission_two_percent;
                $data['promote_commission_three'] = $promote_commission_three_percent;
                $res = $row->save($data);
                if($res){
                    if($data['status'] == 1 && $data['is_fashionable'] == 1){
                        //推广人
                        if(!empty($row['promoter'])){
                                $promote_commission_two = $promote_commission_three =  0;
                                $user_one_res = userModel::where('id',$row['promoter'])->find();
                                if($user_one_res){
                                    $time = time();
                                    $month = date('Y-m',time());
                                    //1信用卡2贷款3拉新
                                    switch($row->business_type){
                                        case 1:
                                            $emo = '信用卡推广奖励';
                                            break;
                                        case 2:
                                            $emo = '贷款业务奖励';
                                            break;  
                                        case 3:
                                            $emo = '拉新业务奖励';
                                            break;      
                                    }
                                    if(!empty($user_one_res->promoter)){
                                        $user_two_res = userModel::where('id',$user_one_res->promoter)->find();
                                        if($user_two_res){
                                            $promote_commission_two = round($data['fashionable_money']*$promote_commission_two_percent/100,2);
                                            if($promote_commission_two != 0){
                                                MoneyLog::insert(['type'=>1,'user_id'=>$user_one_res->promoter, 'money'=> $promote_commission_two, 'before'=> $user_two_res->money,'after'=>$user_two_res->money+$promote_commission_two,'memo'=>$emo,'order_num'=>$row->order_number, 'createtime'=>$time,'time'=>$month]); 
                                                $user_two_res->save(['money'=>$user_two_res->money+$promote_commission_two,'total_income'=>$user_two_res->total_income+$promote_commission_two,'updatetime'=>$time]);
                                            }
                                            if(!empty($user_two_res->promoter)){
                                                $user_three_res = userModel::where('id',$user_two_res->promoter)->find();
                                                if($user_three_res){
                                                    $promote_commission_three = round($data['fashionable_money']*$promote_commission_three_percent/100,2);
                                                    if($promote_commission_three != 0){
                                                        MoneyLog::insert(['type'=>1,'user_id'=>$user_two_res->promoter, 'money'=> $promote_commission_three, 'before'=> $user_three_res->money,'after'=>$user_three_res->money+$promote_commission_three,'memo'=>$emo,'order_num'=>$row->order_number, 'createtime'=>$time,'time'=>$month]); 
                                                        $user_three_res->save(['money'=>$user_three_res->money+$promote_commission_three,'total_income'=>$user_three_res->total_income+$promote_commission_three,'updatetime'=>$time]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $sum = $promote_commission_two + $promote_commission_three;
                                    $promote_commission_one = bcsub($data['fashionable_money'], $sum, 2);
                                    MoneyLog::insert(['type'=>1,'user_id'=>$row['promoter'], 'money'=> $promote_commission_one, 'before'=> $user_one_res->money,'after'=>$user_one_res->money+$promote_commission_one,'memo'=>$emo,'order_num'=>$row->order_number, 'createtime'=>$time,'time'=>$month]); 
                                    $user_one_res->save(['money'=>$user_one_res->money+$promote_commission_one,'total_income'=>$user_one_res->total_income+$promote_commission_one,'updatetime'=>$time]);
                                }
                        }
                    }
                }else{
                    Db::rollback();
                    $this->error('操作失败');
                }
            }catch(Exception $e){
                Log::error('业务订单分账'.$ids.'：'.$e->getMessage());
                Db::rollback();
                $this->error($e->getMessage());
            }
            Db::commit();
            $this->success();
        }else{
            if (!$row) {
                $this->error(__('参数错误'));
            }
            $row->is_percent = strpos($row->reward,'%') !== false ? 1 : 2;
            $row->fashionable_money = $row->is_percent == 1? '' : $row->reward;
            $this->assign('row',$row);
            return $this->view->fetch();
        }
       
    }


}
