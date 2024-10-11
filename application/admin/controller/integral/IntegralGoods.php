<?php

namespace app\admin\controller\integral;

use app\common\model\IntegralGoods As integralGoodsModel;
use app\common\controller\Backend;

use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;


class IntegralGoods extends Backend
{


    protected $model = null;
    protected $commentsModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new integralGoodsModel;
    }

    public function index()
    {
       $bank_id = $this->request->param("ids");
       //当前是否为关联查询
       $this->relationSearch = true;
       //设置过滤方法
       $this->request->filter(['strip_tags', 'trim']);
       if ($this->request->isAjax())
       {
           $bank_id = $this->request->param("bank_id");
           //如果发送的来源是Selectpage，则转发到Selectpage
           if ($this->request->request('keyField'))
           {
               return $this->selectpage();
           }
           list($where, $sort, $order, $offset, $limit) = $this->buildparams();
           $total = $this->model
                   ->where('bank_id',$bank_id)
                   ->where($where)
                   ->order($sort, $order)
                   ->count();

           $list = $this->model
                   ->where('bank_id',$bank_id)
                   ->where($where)
                   ->order('id desc')
                   ->order($sort, $order)
                   ->limit($offset, $limit)
                   //->field('id,type,name,status,reward,create_time')
                   ->select();
           foreach ($list as $v) {
           }
           $list = collection($list)->toArray();
           $result = array("total" => $total, "rows" => $list);

           return json($result);
       }
       $this->assign('bank_id',$bank_id);
       return $this->view->fetch();
    }

     /**
     * 添加
     */
    public function add()
    {
        $bank_id = $this->request->param("bank_id");
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");
            //$params['characteristics'] = str_replace('，',',',$params['characteristics']);
            $params['create_time'] = $params['update_time'] = time();
            $this->model->insert($params);
            $this->success();
        }
        $this->assign('bank_id',$bank_id);
        return parent::add();
    } 

    /**
     * 编辑
     */
     public function edit($ids = null)
    {
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");
            //$params['characteristics'] = str_replace('，',',',$params['characteristics']);
            $params['update_time'] = time();
            $this->model->update($params,['id'=>$ids]);
            $this->success();
        }else{
            $row = $this->model->get($ids);
            if (!$row) {
                $this->error(__('参数错误'));
            }
            $this->assign('row',$row);
            return $this->view->fetch();
        }
       
    }


}
