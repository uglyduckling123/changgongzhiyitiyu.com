<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 地区管理
 *
 * @icon fa fa-circle-o
 */
class Area extends Backend
{

    /**
     * Area模型对象
     * @var \app\admin\model\Area
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Area;
        $this->view->assign("levelList", $this->model->getLevelList());
        $this->view->assign("statusList", $this->model->getStatusList());

        $pid_list = \app\common\model\Area::getAllAreaList();
        $pid_list[0] = '顶级节点';
//        $pid_list = array_merge($pid_list,$list);
        $this->view->assign("pid_list",$pid_list);
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
