<?php

namespace app\admin\model\make;

use think\Model;


class Info extends Model
{

    

    

    // 表名
    protected $name = 'make_info';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'make_time_text',
        'cancel_time_text',
        'status_text',
        'room_text',
        'pay_type_text'
    ];
    

    
    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'),'3' => __('未付款订单')];
    }

    public function getRoomList()
    {
        return ['1' => __('Room 1'), '2' => __('Room 2')];
    }

    public function getPayTypeList()
    {
        return ['1' => __('Pay_type 1'), '2' => __('Pay_type 2'),'3' => __('公益无需付款')];
    }


    public function getMakeTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['make_time']) ? $data['make_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCancelTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['cancel_time']) ? $data['cancel_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getRoomTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['room']) ? $data['room'] : '');
        $list = $this->getRoomList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPayTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_type']) ? $data['pay_type'] : '');
        $list = $this->getPayTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setMakeTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCancelTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'uid', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function place()
    {
        return $this->belongsTo('app\admin\model\Place', 'place_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    
    public function seat()
    {
        return $this->belongsTo('app\admin\model\Seat', 'seat', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    
    public function room()
    {
        return $this->belongsTo('app\admin\model\Room', 'room', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function makeuserinfo()
    {
        return $this->belongsTo('app\admin\model\MakeUserInfo', 'id', 'make_id', [], 'LEFT')->setEagerlyType(0);
    }
    
}
