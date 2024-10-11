<?php

namespace app\common\model;

use think\Model;


class IntegralOrder Extends Model
{
    // 表名
    protected $name = 'integral_order';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    // 追加属性
    protected $append = [
    ];
}
