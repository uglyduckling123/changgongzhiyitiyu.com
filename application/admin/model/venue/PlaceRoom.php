<?php

namespace app\admin\model\venue;

use think\Model;


class PlaceRoom extends Model
{

    

    

    // 表名
    protected $name = 'place_room';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







    public function place()
    {
        return $this->belongsTo('app\admin\model\Place', 'place_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
