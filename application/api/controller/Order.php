<?php

namespace app\api\controller;


use app\common\controller\Api;
use app\common\model\Order As orderModel;
use app\common\model\Business;
use app\common\model\User As userModel;
use app\common\model\MoneyLog;
use app\common\model\UserRank As userRankModel;
use fast\Random;
use think\Config;
use think\Db;
use think\Validate;
use think\Log;
use think\Env;


/**
 * 会员接口
 */
class Order extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

  

    /**
     * 申请业务
     */
    public function applyBusiness()
    {
        $is_etc = $this->request->post('is_etc','');
        $parsms = $this->request->post();
        $str = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $time = time();
        $promoter = $this->request->post('promoter');
        if(empty($promoter)){
            $this->error('邀请人不能为空');
        }
        $promoter = userModel::where('unine_key',$promoter)->value('id');
        if(empty($promoter)){
            $this->error('邀请人不存在');
        }
        $data = [
            'business_id'=>$this->request->post('business_id'),
            'promoter'=> $promoter,
            'mobile'=>$this->request->post('mobile'),
            'realname'=>$this->request->post('realname'),
            'id_card'=>$this->request->post('id_card',''),
            'car_card'=>$this->request->post('car_card',''),
            'create_time'=>$time,
            'update_time'=>$time,
            'order_number'=>$str,
        ];
        if(empty($data['business_id']) ){
            $this->error('参数错误');
        }
        $busines_content = Business::where(['id'=>$data['business_id'],'status'=> 1])->find();
        if(empty($busines_content)){
            $this->error('该业务已暂停');
        }
        if(empty($data['realname'])){
            $this->error('姓名不能为空');
        }
        if(empty($data['mobile'])){
            $this->error('手机号不能为空');
        }
        if (!Validate::regex($data['mobile'], "^1\d{10}$")) {
            $this->error(__('手机号格式错误'));
        }
        if($busines_content->type == 1 && empty($data['id_card'])){
            $this->error(__('身份证号不能为空'));
        }
        if($is_etc == 1 && empty($data['car_card'])){
            $this->error(__('车牌号不能为空'));
        }
        if($is_etc == 1 && !isCarLicense($data['car_card']) ){
            $this->error(__('车牌号错误'));
        }
        if(!empty($data['id_card']) && !validateIDCard($data['id_card'])){
            $this->error('身份证号错误');
        }
        $data['business_name'] = $busines_content->name;
        $data['reward'] = $busines_content->reward;
        $data['business_type'] = $busines_content->type;
        $data['image'] = $busines_content->image;
        $res = orderModel::insert($data);
        if ($res) {
            $this->success('提交成功', []);
        } else {
            $this->error('提交失败');
        }
    }

    public function getBusinessInfo()
    {
        $business_id =  $this->request->get('business_id');
        if(empty($business_id)){
             $this->error('参数错误');
        }
        $info = Business::where(['id'=>$business_id,'status'=>1])->field('type,name,apply_content')->find();
        if(empty($info)){
            $this->error('业务已暂停');
        }
        $info->is_etc = strpos(strtolower($info->name),'etc') !== false?1:2;
        $this->success('获取成功',$info);
    }

    public function getList()
    {
        $type = $this->request->get('type');
        $status = $this->request->get('status');
        $page = $this->request->get('page');
        $limit = $this->request->get('limit');
        $offset = ($page - 1) * $limit;
        $user_info = $this->auth->getUser();
        $result = orderModel::where(['business_type'=>$type,'status'=>$status,'promoter'=>$user_info['id']])->order('id desc')->limit($offset, $limit)->field('id,order_number,image,business_name,reward,mobile,realname,car_card,create_time,promoter')->select();
        if(!empty($result)){
            $promoters = array_column($result,'promoter');
            $users = userModel::whereIn('id',$promoters)->column('id,nickname');
            foreach($result as &$v){
                $v->create_time = date('Y-m-d H:i:s',$v->create_time);
                $v->nickname = !empty($v->promoter) && isset($users[$v->promoter])?$users[$v->promoter]:'';
            }
        }
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

    public function getResult()
    {
        $time = $this->request->get('time');
        if(empty($time)){
            $this->error('月份不能为空');
        }
        $user_info = $this->auth->getUser();
        $this->_dataTrees(userModel::field('id,promoter')->select(), $user_info->id);
        
       
        $ids = $this->tree;
       
        $all_user_ids = $user_ids = array_column($ids,'id');
        
        $all_user_ids[] =  $user_info->id;
        $money_logs = MoneyLog::whereIn('user_id', $all_user_ids)->where(['type'=>1,'time'=>$time])->select();

        $begin_time = strtotime($time.'-01');
        $end_time = strtotime($time.'-31');
        $where['create_time'] = ['between',"$begin_time,$end_time"];
        $where['status'] = 1;
        $where['is_fashionable'] = 1;
        $total_result = orderModel::whereIn('promoter', $all_user_ids)->where($where)->value('sum(fashionable_money)');

        $person_income = $team_income = '0.00';
        if(!empty($money_logs)){
            foreach($money_logs as $v){
                if(in_array($v['user_id'], [$user_info->id])){
                    $person_income += $v['money'];
                }
                if(in_array($v['user_id'], $user_ids)){
                    $team_income += $v['money'];
                }
            }
        }
        $total_income = $person_income + $team_income;
        $rank_money =  userRankModel::where('money','<',$total_income)->order('id desc')->value('money');
        $rank_num = (int)substr($rank_money, 1);
        $rank = $rank_num*16.6;
        $rank_gap_money =  userRankModel::where('money','>',$total_income)->order('id asc')->value('money');
        if(empty($rank_gap_money)){
            $rank_gap = 0;
        }else{
            $rank_gap = $rank_gap_money - $total_income;
        }
        $results = [
            'total_income' =>  $total_income,
            'person_income' => $person_income,
            'team_income' => $team_income,
            'total_result' => !empty($total_result)?$total_result:'0',
            'rank' => $rank == 0 ? 2 : $rank,
            'rank_gap' => $rank_gap
        ];
        $this->success('获取成功', $results);

    }

}
