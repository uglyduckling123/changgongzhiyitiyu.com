<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\model\CommonQuestion As commonQuestionModel;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

class CommonQuestion extends Backend
{


    protected $model = null;
    protected $commentsModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new commonQuestionModel;
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
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");
            $params['create_time'] = $params['update_time'] = time();
            Db::startTrans();
            try{
                $res = commonQuestionModel::create($params);
            }catch(\Exception $e){
                Db::rollback();
                $this->error($e->getMessage());
            }
        Db::commit();
            $this->success();
        }
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
