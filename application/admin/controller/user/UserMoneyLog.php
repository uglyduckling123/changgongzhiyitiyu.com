<?php

namespace app\admin\controller\user;

use app\common\model\MoneyLog;
use app\common\controller\Backend;
use app\common\library\Auth;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class UserMoneyLog extends Backend
{

    protected $relationSearch = true;
    //protected $searchFields = 'id,username,nickname,unine_key,city,area,community';

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('MoneyLog');
    }

}
