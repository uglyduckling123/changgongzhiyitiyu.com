<?php

namespace app\admin\model\admin;

use think\Model;


class Recharge extends Model
{

    

    

    // 表名
    protected $name = 'admin_recharge';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'uid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
