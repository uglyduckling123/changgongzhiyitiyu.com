<?php

namespace app\admin\model\venue;

use think\Model;


class Seat extends Model
{

    

    

    // 表名
    protected $name = 'seat';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function place()
    {
        return $this->belongsTo('app\admin\model\Place', 'place_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    
    public function room()
    {
        return $this->belongsTo('app\admin\model\Room', 'room_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
