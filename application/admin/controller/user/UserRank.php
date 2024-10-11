<?php

namespace app\admin\controller\user;

use app\common\model\UserRank As userRankModel;
use app\common\controller\Backend;
use app\common\library\Auth;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class UserRank extends Backend
{

    protected $relationSearch = true;

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('userRank');
    }

}
