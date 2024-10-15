<?php

namespace app\admin\controller\make;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use app\service\PayService;
/**
 * 预约详情管理
 *
 * @icon fa fa-circle-o
 */
class Info extends Backend
{

    /**
     * Info模型对象
     * @var \app\admin\model\make\Info
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\make\Info;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("roomList", $this->model->getRoomList());
        $this->view->assign("payTypeList", $this->model->getPayTypeList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    ->with(['user','place','seat','room','makeuserinfo'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                $row->visible(['id','code','make_year','make_time','money','createtime','cancel_time','status','label','seat','room','pay_type']);
                $row->visible(['user']);
				$row->getRelation('user')->visible(['nickname']);
				$row->visible(['place']);
				$row->getRelation('place')->visible(['name']);
				$row->visible(['seat']);
				$row->getRelation('seat')->visible(['name']);
				$row->visible(['room']);
				$row->getRelation('room')->visible(['name']);
                $row->visible(['makeuserinfo']);
                $row->getRelation('makeuserinfo')->visible(['name','mobile','identification','address']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    //核销
    public function audit()
    {
        $gets = $this->request->get();
        if(!$gets){
            $this->error('参数错误');
        }
        $id = $gets['id'];//审核单id
        Db::startTrans();
        try {
           //查询信息
           $makeInfo = Db::name('make_info')->where('id',$id)->find();
           if(!$makeInfo){
               Db::rollback();
               $this->error('核销信息不存在');
           }
           
           //更新当前表状态
           $makeUpdate = Db::name('make_info')->where('id',$id)->update(['status'=>1,'cancel_time'=>time()]);
           if(!$makeUpdate){
               $this->error('核销失败');
           }
            Db::commit();
        }catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } 
        $this->success('操作成功');
    }
    //预约人信息
    public function make()
    {
        $id = $this->request->get('id');
        $list = Db::name('make_user_info')->where('make_id',$id)->paginate(5,false, [
            'query' => request()->param(),
        ]);
        $this->assign('list',$list);
        return $this->view->fetch();
    }

   //退款
    public function applyRefund()
    {
        $gets = $this->request->get();
        if(!$gets){
            $this->error('参数错误');
        }
        $make_id = $gets['make_id'];//审核单id
        if(empty($make_id)) $this->error('请传要退款订单ID');
        $makeInfo = Db::name('make_info')->where(['id'=>$make_id])->find();
        if(!$makeInfo) $this->error('预约信息不存在');
        $user = Db::name('user')->where('id',$makeInfo['uid'])->find();
        //拆分预约时间段
        $new_make_time = explode('-',$makeInfo['make_time']);
        $new_date_time = date('Y-m-d',$makeInfo['make_year']).' '.$new_make_time[0];
        $strtotime = strtotime($new_date_time); //预约时间戳
        // if(($strtotime - time()) < 86400){
        //     $this->error('超过24小时不可申请退款');
        // }
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
    //零钱退款回调
    public function balanceCallback($user,$makeInfo)
    {
        //用户余额递增
        Db::name('user')->where('id',$user['id'])->setInc('money',$makeInfo['money']);
        //更新预约信息
        $res = Db::name('make_info')->where('id',$makeInfo['id'])->update(['status'=>2]);
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
     * 取消订单
     */
    public function cancleOrder()
    {
        $gets = $this->request->get();
        if(!$gets){
            $this->error('参数错误');
        }
        $make_id = $gets['make_id'];//审核单id
        $makeInfo = Db::name('make_info')->where(['id'=>$make_id])->find();
        if(!$makeInfo) $this->error('预约信息不存在');
        $res = Db::name('make_info')->where('id',$makeInfo['id'])->update(['status' => 2,'updatetime' => time()]);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }
    
}
