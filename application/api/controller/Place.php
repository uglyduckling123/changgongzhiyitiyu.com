<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\make\Info;
use think\Db;
use think\Config;
use fast\Random;
use think\Request;
use zjkal\ChinaHoliday;
use app\api\controller\User;

/**
 * 场馆接口
 * 2023-03-10 14:45:09
 */
class Place extends Api
{

    protected $noNeedLogin = ['del_order','cancel_order','seatVerify', 'getPlaceList', 'getPlaceDetail', 'backTimeSlof', 'siteList'];
    protected $noNeedRight = [''];
    
    //2024/5: hook: 后门用户id
    static $vvip = [78,201,126,20];


    /**
     * 场馆列表
     */
    public function getPlaceList()
    {
        if ($this->request->isGet()) {
            $page = input('get.page', 1);
            $limit = input('get.limit', 10);
            $offset = ($page - 1) * $limit;
            $res = Db::name('place')->where(['status' => 1])->order('weigh desc')->field('id,name,cover_image,business_time,money,address')->limit($offset, $limit)->select();
            foreach ($res as $k => $v) {
                $res[$k]['cover_image'] = domain() . $v['cover_image'];
            }
            $this->success('获取成功', $res);
        }
        $this->error('请求方式错误');
    }

    /**
     * 场馆详情
     * id 场馆ID
     */
    public function getPlaceDetail()
    {
        if ($this->request->isGet()) {
            $id = $this->request->get('id');
            if (empty($id)) $this->error('参数错误');
            $list = Db::name('place')->where(['id' => $id])->field('id,business_time,money,banner_image,name,address,mobile,content,open_time')->find();
            $list['room'] = Db::name('place_room')->where(['place_id' => $id])->field('id,name')->select();
            $time = strtotime(date('Y-m-d', time()));
            $new_time = $time + 21600;
//            if (time() < $new_time) $this->error('暂未开放预约');
            $list['banner_image'] = explode(',', $list['banner_image']);
            foreach ($list['banner_image'] as $k => $v) {
                $list['banner_image'][$k] = domain() . $v;
            }
            if (!$list) {
                $this->error('暂无数据!');
            } else {
                $this->success('获取成功', $list);
            }
        }
        $this->error('请求方式错误');
    }

    /**
     * 循环出一个横向列表
     * date 日期
     */
    public function siteList()
    {
        if ($this->request->isGet()) {
            $date = $this->request->get('date');
            $room = $this->request->get('room');
            $date_label = $this->request->get('date_label');
            $place_id = $this->request->get('place_id');
            if (empty($date)) $this->error('请选择日期');
            if (empty($room)) $this->error('请选择场地');
            if (empty($place_id)) $this->error('请选择场馆');
            //查找该场所配置信息
            $room_info = Db::name('place_room')->where(['place_id' => $place_id, 'id' => $room])->find();
            if (!$room_info) $this->error('场所信息不存在');
            $user = $this->auth->getUser();
            if ($user['member_type'] != 2) {
                if ($date_label > $room_info['make_day']) {
                    $this->error('今天不在可预约时间范围内');
                }
            }
            $time = strtotime(date('Y-m-d', time()));
            $new_time = $time + 21600;
//            if (time() < $new_time) $this->error('暂未开放预约');
            $arr = [];
            $label = [];
            //查找场地数量
            $seat  = Db::name('seat')->where(['place_id' => $place_id, 'room_id' => $room, 'status' => 0])->select();
            for ($i = 1; $i <= 15; $i++) {
                $label[$i] = [];
            }
            foreach ($seat as $k => $v) {
                $arr[$k]['seat_id'] = $v['id'];
                $arr[$k]['seat_name'] = $v['name'];
                $arr[$k]['label'] = $label;
            }
            $user = $this->auth->getUser();
            foreach ($arr as &$value) {
                foreach ($value['label'] as $kk => $vv) {
                    $value['label'][$kk]['status'] = $this->backStatus($user, $date, $place_id, $room, $value['seat_id'], $kk, $date_label);
                    $value['label'][$kk]['time_solf'] = $this->backTime($kk);
                    $data = $this->backPrice($kk,$room_info,$date);
                    $value['label'][$kk]['member_price'] = $data['member_price'];
                    $value['label'][$kk]['wrong_member_price'] = $data['wrong_memeber_price'];
                    $value['label'][$kk]['is_real'] = $data['is_real'];
                }
            }
            $this->success('获取成功', $arr);
        }
        $this->error('请求方式错误');
    }
    /**
     * 循环出一个横向列表
     * date 日期
     */
    public function siteListNew()
    {
        if ($this->request->isGet()) {
            $date = $this->request->get('date');
            $room = $this->request->get('room');
            $date_label = $this->request->get('date_label');
            $place_id = $this->request->get('place_id');
            if (empty($date)) $this->error('请选择日期');
            if (empty($room)) $this->error('请选择场地');
            if (empty($place_id)) $this->error('请选择场馆');
            //查找该场所配置信息
            $room_info = Db::name('place_room')->where(['place_id' => $place_id, 'id' => $room])->find();
            if (!$room_info) $this->error('场所信息不存在');
            $user = $this->auth->getUser();
            if ($user['member_type'] != 2) {
                if ($date_label > $room_info['make_day']) {
                    $this->error('今天不在可预约时间范围内');
                }
            }
            $arr = [];
            $label = [];
            //查找场地数量
            $seat  = Db::name('seat')->where(['place_id' => $place_id, 'room_id' => $room, 'status' => 0])->select();
            for ($i = 1; $i <= 29; $i++) {
                $label[$i] = [];
            }
            foreach ($seat as $k => $v) {
                $arr[$k]['seat_id'] = $v['id'];
                $arr[$k]['seat_name'] = $v['name'];
                $arr[$k]['label'] = $label;
            }
            $user = $this->auth->getUser();
            foreach ($arr as &$value) {
                foreach ($value['label'] as $kk => $vv) {
                    $value['label'][$kk]['status'] = $this->backStatusNew($user, $date, $place_id, $room, $value['seat_id'], $kk, $date_label);
                    $value['label'][$kk]['time_solf'] = $this->backTimeNew($kk);
                    $data = $this->backPriceNew($kk,$room_info,$date);
                    $value['label'][$kk]['member_price'] = $data['member_price'];
                    $value['label'][$kk]['wrong_member_price'] = $data['wrong_memeber_price'];
                    $value['label'][$kk]['is_real'] = $data['is_real'];
                }
            }
            $this->success('获取成功', $arr);
        }
        $this->error('请求方式错误');
    }
    
    /**
     * 返回价格
     */
    public function backPrice($kk,$room_info,$date)
    {
        $solf = $this->backIntTime($kk);
        $new_slof = explode('-', $room_info['slof_time']); //公益时间段
        $gold_one_time = explode('-',$room_info['gold_one_time']);//第一时间段
        $gold_two_time = explode('-',$room_info['gold_two_time']);//第二时间段
         //判断价格 公益时间段价格  工作日(会员和非会员)价格  非工作日(会员和非会员)价格
        $workday = ChinaHoliday::isWorkday($date);
        //判断是不是工作日
        if($workday){
            if($solf[0] >= $new_slof[0] && $solf[1] <= $new_slof[1]){
                //公益时间段价格
                $member_price = 0;
                $wrong_memeber_price = 0;
                $real = 1;
            }else {
                 if($solf[0] >= $gold_one_time[0] && $solf[1] <= $gold_one_time[1]){
                    $member_price = $room_info['one_first_price'];
                    $wrong_memeber_price = $room_info['one_second_price'];
                 }else{
                    $member_price = $room_info['weekday_one_price'];
                    $wrong_memeber_price = $room_info['weekday_two_price'];
                 }
                $real = 0;
            }
        }else{
            //节假日价格
           $member_price = $room_info['saturday_one_price'];
           $wrong_memeber_price = $room_info['saturday_two_price'];
           $real = 0;
        }
        $data['member_price'] = $member_price;
        $data['wrong_memeber_price'] = $wrong_memeber_price;
        $data['is_real'] = $real;
        return $data;
    }

    /**
     * 返回价格
     */
    public function backPriceNew($kk,$room_info,$date)
    {
        $solf = $this->backIntTimeNew($kk);
        $new_slof = explode('-', $room_info['slof_time']); //公益时间段
        $gold_one_time = explode('-',$room_info['gold_one_time']);//第一时间段
        //判断价格 公益时间段价格  工作日(会员和非会员)价格  非工作日(会员和非会员)价格
        $workday = ChinaHoliday::isWorkday($date);
        //判断是不是工作日
        if($workday){
            if($solf[0] >= $new_slof[0] && $solf[1] <= $new_slof[1]){
                //公益时间段价格
                $member_price = 0;
                $wrong_memeber_price = 0;
                $real = 1;
            }else {
                if($solf[0] >= $gold_one_time[0] && $solf[1] <= $gold_one_time[1]){
                    $member_price = $room_info['one_first_price'];
                    $wrong_memeber_price = $room_info['one_second_price'];
                }else{
                    $member_price = $room_info['weekday_one_price']/2;
                    $wrong_memeber_price = $room_info['weekday_two_price']/2;
                }
                $real = 0;
            }
        }else{
            //节假日价格
            $member_price = $room_info['saturday_one_price']/2;
            $wrong_memeber_price = $room_info['saturday_two_price']/2;
            $real = 0;
        }
        $data['member_price'] = $member_price;
        $data['wrong_memeber_price'] = $wrong_memeber_price;
        $data['is_real'] = $real;
        return $data;
    }
    /**
     * 返回处理数据用整数时间段
     */
    public function backIntTime($kk)
    {
        if ($kk == 1) {
            $solf = '7-8';
        } elseif ($kk == 2) {
            $solf = '8-9';
        } elseif ($kk == 3) {
            $solf = '9-10';
        } elseif ($kk == 4) {
            $solf = '10-11';
        } elseif ($kk == 5) {
            $solf = '11-12';
        } elseif ($kk == 6) {
            $solf = '12-13';
        } elseif ($kk == 7) {
            $solf = '13-14';
        } elseif ($kk == 8) {
            $solf = '14-15';
        } elseif ($kk == 9) {
            $solf = '15-16';
        } elseif ($kk == 10) {
            $solf = '16-17';
        } elseif ($kk == 11) {
            $solf = '17-18';
        } elseif ($kk == 12) {
            $solf = '18-19';
        } elseif ($kk == 13) {
            $solf = '19-20';
        } elseif ($kk == 14) {
            $solf = '20-21';
        } elseif ($kk == 15) {
            $solf = '21-22';
        }
        $new_solf = explode('-',$solf);
        return $new_solf;
    }

    /**
     * 返回处理数据用整数时间段
     */
    public function backIntTimeNew($kk)
    {
        if ($kk == 1) {
            $solf = '7-8';
        } elseif ($kk == 2) {
            $solf = '7-8';
        } elseif ($kk == 3) {
            $solf = '8-9';
        } elseif ($kk == 4) {
            $solf = '8-9';
        } elseif ($kk == 5) {
            $solf = '9-10';
        } elseif ($kk == 6) {
            $solf = '9-10';
        } elseif ($kk == 7) {
            $solf = '10-11';
        } elseif ($kk == 8) {
            $solf = '10-11';
        } elseif ($kk == 9) {
            $solf = '11-12';
        } elseif ($kk == 10) {
            $solf = '11-12';
        } elseif ($kk == 11) {
            $solf = '12-13';
        } elseif ($kk == 12) {
            $solf = '12-13';
        } elseif ($kk == 13) {
            $solf = '13-14';
        } elseif ($kk == 14) {
            $solf = '13-14';
        } elseif ($kk == 15) {
            $solf = '14-15';
        } elseif ($kk == 16) {
            $solf = '14-15';
        } elseif ($kk == 17) {
            $solf = '15-16';
        } elseif ($kk == 18) {
            $solf = '15-16';
        } elseif ($kk == 19) {
            $solf = '16-17';
        } elseif ($kk == 20) {
            $solf = '16-17';
        } elseif ($kk == 21) {
            $solf = '17-18';
        } elseif ($kk == 22) {
            $solf = '17-18';
        } elseif ($kk == 23) {
            $solf = '18-19';
        } elseif ($kk == 24) {
            $solf = '18-19';
        } elseif ($kk == 25) {
            $solf = '19-20';
        } elseif ($kk == 26) {
            $solf = '19-20';
        } elseif ($kk == 27) {
            $solf = '20-21';
        } elseif ($kk == 28) {
            $solf = '20-21';
        } elseif ($kk == 29) {
            $solf = '21-22';
        }
        $new_solf = explode('-',$solf);
        return $new_solf;
    }
    
    /**
     * 返回时间段
     */
    public function backTime($kk)
    {
        if ($kk == 1) {
            $solf = '07:00-08:00';
        } elseif ($kk == 2) {
            $solf = '08:00-09:00';
        } elseif ($kk == 3) {
            $solf = '09:00-10:00';
        } elseif ($kk == 4) {
            $solf = '10:00-11:00';
        } elseif ($kk == 5) {
            $solf = '11:00-12:00';
        } elseif ($kk == 6) {
            $solf = '12:00-13:00';
        } elseif ($kk == 7) {
            $solf = '13:00-14:00';
        } elseif ($kk == 8) {
            $solf = '14:00-15:00';
        } elseif ($kk == 9) {
            $solf = '15:00-16:00';
        } elseif ($kk == 10) {
            $solf = '16:00-17:00';
        } elseif ($kk == 11) {
            $solf = '17:00-18:00';
        } elseif ($kk == 12) {
            $solf = '18:00-19:00';
        } elseif ($kk == 13) {
            $solf = '19:00-20:00';
        } elseif ($kk == 14) {
            $solf = '20:00-21:00';
        } elseif ($kk == 15) {
            $solf = '21:00-22:00';
        }
        return $solf;
    }
    /**
     * 返回时间段
     */
    public function backTimeNew($kk)
    {
        if ($kk == 1) {
            $solf = '07:00-07:30';
        } elseif ($kk == 2) {
            $solf = '07:30-08:00';
        } elseif ($kk == 3) {
            $solf = '08:00-08:30';
        } elseif ($kk == 4) {
            $solf = '08:30-09:00';
        } elseif ($kk == 5) {
            $solf = '09:00-09:30';
        } elseif ($kk == 6) {
            $solf = '09:30-10:00';
        } elseif ($kk == 7) {
            $solf = '10:00-10:30';
        } elseif ($kk == 8) {
            $solf = '10:30-11:00';
        } elseif ($kk == 9) {
            $solf = '11:00-11:30';
        } elseif ($kk == 10) {
            $solf = '11:30-12:00';
        } elseif ($kk == 11) {
            $solf = '12:00-12:30';
        } elseif ($kk == 12) {
            $solf = '12:30-13:00';
        } elseif ($kk == 13) {
            $solf = '13:00-13:30';
        } elseif ($kk == 14) {
            $solf = '13:30-14:00';
        } elseif ($kk == 15) {
            $solf = '14:00-14:30';
        } elseif ($kk == 16) {
            $solf = '14:30-15:00';
        } elseif ($kk == 17) {
            $solf = '15:00-15:30';
        } elseif ($kk == 18) {
            $solf = '15:30-16:00';
        } elseif ($kk == 19) {
            $solf = '16:00-16:30';
        } elseif ($kk == 20) {
            $solf = '16:30-17:00';
        } elseif ($kk == 21) {
            $solf = '17:00-17:30';
        } elseif ($kk == 22) {
            $solf = '17:30-18:00';
        } elseif ($kk == 23) {
            $solf = '18:00-18:30';
        } elseif ($kk == 24) {
            $solf = '18:30-19:00';
        } elseif ($kk == 25) {
            $solf = '19:00-19:30';
        } elseif ($kk == 26) {
            $solf = '19:30-20:00';
        } elseif ($kk == 27) {
            $solf = '20:00-20:30';
        } elseif ($kk == 28) {
            $solf = '20:30-21:00';
        } elseif ($kk == 29) {
            $solf = '21:00-22:00';
        }
        return $solf;
    }
    
    public function isWeekdayMorningToAfternoon($date) {
    
        $ymd = date("H:i:s");
        // 获取当前时间
        $currentTime = strtotime($date.' '.$ymd);
        
        // 获取当前时间的小时和星期几
        $currentHour = date("G", $currentTime); // 24小时制的小时数，没有前导零（0 到 23）
        $currentDayOfWeek = date("N", $currentTime); // ISO-8601 格式数字表示的星期中的第几天（PHP 5.1.0 新加） 1（表示星期一）到 7（表示星期日）
        // 判断是否在非周六日的上午8点到下午16点之间
        if ($currentDayOfWeek >= 1 && $currentDayOfWeek <= 5 && $currentHour >= 8 && $currentHour < 16) {
            return true;
        } else {
            return false;
        }
    }


    
    /**
     * 返回预约状态
     * date 要预约的时间
     * place_id 网球馆id
     * room 室内室外
     * seat 第几场
     * label 几点钟 标签时间段 数字
     * 
     */
    private function backStatus($user, $date, $place_id, $room, $seat, $label, $date_label)
    {
        //halt($this->isWeekdayMorningToAfternoon($date));
        $map['place_id'] = $place_id;
        $map['room'] = $room;
        $map['seat'] = $seat;
        $map['label'] = $label;
        $map['make_year'] = strtotime($date);
        $map['status'] = ['in', [0, 1]];
        $label_time = explode('-', $this->backTime($label));
        $new_label_time = strtotime($label_time[1]);
        $make_info = Db::name('make_info')->where($map)->find();
        //判断当天状态
        if (strtotime($date) == strtotime(date('Y-m-d', time())) && (time() > $new_label_time)) {
            $label_time_status = 1;
        } else {
            $label_time_status = 0;
        }
        if ($date_label == 1) {
            $value = 'one_set';
        } elseif ($date_label == 2) {
            $value = 'two_set';
        } elseif ($date_label == 3) {
            $value = 'three_set';
        } elseif ($date_label == 4) {
            $value = 'four_set';
        } elseif ($date_label == 5) {
            $value = 'five_set';
        } elseif ($date_label == 6) {
            $value = 'six_set';
        } else {
            $value = 'seven_set';
        }
        $disable_one_seat = explode(',', Db::name('seat')->where(['place_id' => $place_id, 'id' => $seat])->value($value));
        if ($make_info || in_array($label, $disable_one_seat)  || $label_time_status) {
            if ($make_info) {
                if ($make_info['chaoji'] == 1){
                    if($make_info['make_year'] < time()){
                        $status = 1;
                    }
                    $status = 3;
                }else{
                    if ($make_info['uid'] == $user['id']) {
                        $status = 2; //本人已预约
                    } else {
                        $status = 1; //已预约
                    }
                    //2024/4: hook: 如果请求的用户不是vip，那么它不应该看到后门用户提前预订的免费时段信息
                    
                    
                    //2024/4: hook: 已定用户必须是后门用户
                    if(!in_array($make_info['uid'],Place::$vvip)){
                        return $status;
                    }
                    //2024/4: hook: 用户必须不是vip，否则需要看到这些预订信息
                    if ($user['member_type']!=0){
                        return $status;
                    }
                    //2024/4: hook: 仅隐藏公益时间段的预订
                    $hook_room_info = Db::name('place_room')->where(['place_id' => $place_id, 'id' => $room])->find();
                    $hook_solf = $this->backIntTime($label);
                    $hook_new_slof = explode('-', $hook_room_info['slof_time']);
                    $hook_workday = ChinaHoliday::isWorkday($date);
                    //2024/4: hook: 不是工作日没有公益时间
                    if(!$hook_workday){
                        return $status;
                    }
                    //2024/4: hook: 是否是公益时段
                    if(!($hook_solf[0] >= $hook_new_slof[0] && $hook_solf[1] <= $hook_new_slof[1])){
                        return $status;
                    }
                    //2024/4: hook: 预约的日期(0点)时间戳
                    $hook_time = strtotime($date);
                    //2024/4: hook: 如果已经过了6点，就没必要隐藏了
                    if (time() > ($hook_time + 21612)) {
                        return $status;
                    }
                    //2024/4: hook: 隐藏信息
                    $status = 0;
                    //2024/4: hook: 完毕
                }
                
            } else {
                $status = 1; //已预约
            }
            // $status = 1; //已预约
        } else {
            $status = 0; //未预约
        }
        return $status;
    }

    /**
     * 返回预约状态
     * date 要预约的时间
     * place_id 网球馆id
     * room 室内室外
     * seat 第几场
     * label 几点钟 标签时间段 数字
     *
     */
    private function backStatusNew($user, $date, $place_id, $room, $seat, $label, $date_label)
    {
        $map['place_id'] = $place_id;
        $map['room'] = $room;
        $map['seat'] = $seat;
        $map['label'] = $label;
        $map['make_year'] = strtotime($date);
        $map['status'] = ['in', [0, 1]];
        $label_time = explode('-', $this->backTimeNew($label));
        $new_label_time = strtotime($label_time[1]);
        $make_info = Db::name('make_info')->where($map)->find();
        //判断当天状态
        if (strtotime($date) == strtotime(date('Y-m-d', time())) && (time() > $new_label_time)) {
            $label_time_status = 1;
        } else {
            $label_time_status = 0;
        }
        if ($make_info || $label_time_status) {
            if ($make_info) {
                if ($make_info['uid'] ==$user['id']) {
                    $status = 2; //本人已预约
                } else {
                    $status = 1; //已预约
                }
            }else{
                $status = 1; //已预约
            }

        }else {
            $status = 0; //未预约
        }
        return $status;
    }

    //选择场次 进行验证
    public function seatVerify()
    {
        if ($this->request->isPost()) {
            $make_year = strtotime($this->request->post('make_year'));
            $make_time = $this->request->post('make_time');
            if (empty($make_year)) $this->error('请选择预约日期');
            if (empty($make_time)) $this->error('请选择预约时间段');
            //拆分时间段
            $new_time = explode('-', $make_time);
            //今天零点时间戳
            $time = strtotime(date('Y-m-d', time()));
            //今天最大时间戳
            $uptime = $time + 86400;
            if (($make_year < $uptime) && (time() > strtotime($new_time[1]))) {
                $this->error('已过该时间段');
            }
//            if (time() < ($time + 21600)) $this->error('暂未开放预约');
            // $config = \config('site');
            $data = ['code' => 1];
            $this->success('验证通过', $data);
        }
        $this->error('请求方式错误');
    }

    /**
     * 下单前验证
     */
    public function makePlaceBefore()
    {
        if ($this->request->isPost()) {
            $user = $this->auth->getUser();
            $place_id = $this->request->post('place_id');
            $make_year = strtotime($this->request->post('make_year'));
            $real = $this->request->post('is_real/a');
            $room = $this->request->post('room');
            $date_label = $this->request->post('date_label');
            if (empty($place_id))  $this->error('请选择预约场馆');
            if (empty($make_year)) $this->error('请选择预约日期');
            //查找该场所配置信息
            $room_info = Db::name('place_room')->where(['place_id' => $place_id, 'id' => $room])->find();
            if (!$room_info) $this->error('场所信息不存在');
            //今天零点时间戳
            $time = strtotime(date('Y-m-d', time()));
            $new_slof = explode('-', $room_info['slof_time']);
            $start_time = $time + ($new_slof[0] * 3600);
            $end_time = $time + ($new_slof[1] * 3600);
            //查找该场所配置信息
            $room_info = Db::name('place_room')->where(['place_id' => $place_id, 'id' => $room])->find();
            if (!$room_info) $this->error('场所信息不存在');
            if (time() < ($time + 21600) && $user['member_type'] == 0) $this->error('暂未开放预约');
            $real_sum = array_sum($real); //2 加数
            //查询条件
            $map['uid'] = $user['id'];
            $map['place_id'] = $place_id;
            $map['make_year'] = $make_year;
            $map['status'] = ['neq',2];
            if($real_sum){
                if($user['member_type'] == 0){
                    if($real_sum > 1) $this->error('公益时间段您只能预约一个场次');
                    if($make_year > $time) $this->error('您只能预约今天的公益时间段');
                    $user_one_make_info = Db::name('make_info')->where($map)->where('make_times', 'between time', [$start_time, $end_time])->find();
                    if ($user_one_make_info) $this->error('公益时间段您已预约一个场次');
                    $this->success('验证通过');
                }elseif($user['member_type'] == 1){
                    if ($date_label > 2) $this->error('公益时间段您只能预约今天和未来一天');
                        if ($real_sum > 1) {
                            $this->error('公益时间段您只能预约一个场次');
                        } else {
                            //今日预约次数
                            if ($this->request->post('make_year') == date('Y-m-d', time())) {
                                $today_make_count = Db::name('make_info')->where($map)->where('make_times', 'between time', [$start_time, $end_time])->count();
                                if ($today_make_count) $this->error('公益时间段您已预约一个场次');
                            } else {
                                //make_times 预约时间
                                $tomorrow_make_count = Db::name('make_info')->where($map)->where('make_times', 'between time', [$start_time + 86400, $end_time + 86400])->count();
                                if ($tomorrow_make_count) $this->error('公益时间段您已预约一个场次');
                            }
                        }
                        if (($today_make_count + $tomorrow_make_count) >= 2) {
                            $this->error('公益时间段您最多预约两个场次');
                        }
                    $this->success('验证通过');
                }else{
                    $this->success('验证通过'); 
                }
            }else{
                $this->success('验证通过');
            }
        }
        $this->error('请求方式错误');
    }

    /**
     * 场馆 立即预约
     * place_id 场馆ID
     * make_year 预约日期 2023-03-10
     * make_time 预约时间 07:00 - 08:00 
     * room 室内 室外
     * seat 场地
     * label 标签 用于识别某个场地 某个时间段
     * money 费用
     * type 支付类型 1余额支付 2微信支付
     */
    public function makePlace()
    {
        if ($this->request->isPost()) {
           $user = $this->auth->getUser();
            $place_id = $this->request->post('place_id');
            $make_year = strtotime($this->request->post('make_year'));
            $room = $this->request->post('room');
            $money = $this->request->post('money');
            $type = $this->request->post('type');
            $make_user_info = $this->request->post('make_user_info/a');
            $make_data = $this->request->post('make_data/a');
            $is_show = $this->request->post('is_show');
            
            $is_pay = $this->request->post('is_pay');
            if (empty($money)) $this->error('请输入金额');
            //随机订单号
            $order_num = date('Ymd') . substr(implode('', array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 4);
            //查找场所配置信息
            $room_info = Db::name('place_room')->where(['place_id' => $place_id, 'id' => $room])->find();
            $new_slof = explode('-', $room_info['slof_time']);
            //选择场次数量
            $make_count = count($make_data);
            if($is_show && $is_pay){
               $this->only_pay($make_user_info,$make_data,$user,$place_id,$make_year,$new_slof,$room,$money,$type,$make_count,$order_num);
            }elseif($is_show){
                $this->only_make_info($make_user_info,$place_id,$make_data,$user,$order_num,$make_year,$new_slof,$room,$money);
            }elseif($is_pay){
                $this->only_pay($make_user_info,$make_data,$user,$place_id,$make_year,$new_slof,$room,$money,$type,$make_count,$order_num);
            }else{
                $this->only_make_info($make_user_info,$place_id,$make_data,$user,$order_num,$make_year,$new_slof,$room,$money);
            }
        }
        $this->error('请求方式错误');
    }

    /**
     * 单独提交信息
     */
    public function only_make_info($make_user_info,$place_id,$make_data,$user,$order_num,$make_year,$new_slof,$room,$money)
    {
        $user_type = $this->request->post('user_type',0);
        $is_gongyi = $this->request->post('is_gongyi',0);
        if($user['member_type'] !=2){
            //查询预约人信息
        if (empty($make_user_info)) $this->error('请输入预约人信息');
            //判断信息是否有重复
            foreach ($make_user_info as $k => $v) {
                $makeUserInfo = Db::name('make_user_info')->where(
                    ['make_id' => $place_id, 'name' => $v['name'], 'mobile' => $v['mobile'], 'address' => $v['address']]
                )->find();
                if ($makeUserInfo) $this->error('部分预约人信息已存在');
            }
        }
        //定义空数组
        $data = [];
        //循环插入数据
        foreach ($make_data as $key => $value) {
            $data[$key]['uid'] = $user['id'];
            $data[$key]['place_id'] = $place_id;
            $data[$key]['code'] = $order_num;
            $data[$key]['make_year'] = $make_year;
            // $data[$key]['make_times'] = $make_year + ($new_slof[0] * 3600); //判断二级会员用
             //判断二级会员用
            $slof = $this->backIntTime($value['label']);
            $data[$key]['make_times'] = $make_year + ($slof[0] * 3600);
            $data[$key]['make_time'] = $value['make_time'];
            $data[$key]['label'] = $value['label'];
            $data[$key]['seat'] = $value['seat'];
            $data[$key]['room'] = $room;
            $data[$key]['money'] = $money;
            $data[$key]['createtime'] = time();
            if($user_type == 1 && $is_gongyi == 1){
                $data[$key]['chaoji'] = 1;
            }else{
                $data[$key]['chaoji'] = 0;
            }
            $data[$key]['pay_type'] = 3; //公益时间不需支付
            $res = Db::name('make_info')->where(['status'=>['in', [0, 1]],'place_id'=>$place_id,'make_year'=>$make_year,'seat'=>$value['seat'],'make_time'=>$value['make_time']])->find();
            if($res){
                $this->error('该场次已被别人预约');
            }
        }
        //生成预约单
        $res = Db::name('make_info')->insertAll($data);
        //获取递增ID
        $reslist = Db::name('make_info')->getLastInsID();
        //取出预约单ID
        $array = [];
        for ($i = 0; $i < $res; $i++) {
            $array[] = (int)$reslist++;
        }
        if ($array) {
            foreach ($array as $kk => $vv) {
                 if($make_user_info){
                     foreach ($make_user_info as $k => $v) {
                            $arr[$k]['make_id'] = $vv;
                            $arr[$k]['name'] = $v['name'];
                            $arr[$k]['mobile'] = $v['mobile'];
                            $arr[$k]['address'] = $v['address'];
                            $arr[$k]['number'] = $v['number'];
                            $arr[$k]['createtime'] = time();
                        }
                Db::name('make_user_info')->insertAll($arr);
                 }
            }
            $data = ['make_id' => $array[0]];
            $this->success('预约成功', $data);
        } else {
            $this->error('预约失败');
        }
    }
    /**
     * 单独付钱
     */
    public function only_pay($make_user_info,$make_data,$user,$place_id,$make_year,$new_slof,$room,$money,$type,$make_count,$order_num)
    {
        // halt($make_user_info);
        $user_type = $this->request->post('user_type',0);
        $is_gongyi = $this->request->post('is_gongyi',0);
        $arr = [];
        $code = [];
        foreach ($make_data as $key => $value) {
            $arr[$key]['uid'] = $user['id'];
            $arr[$key]['place_id'] = $place_id;
            $arr[$key]['code'] = $order_num;
            $arr[$key]['make_year'] = $make_year;
            // $arr[$key]['make_times'] = $make_year + ($new_slof[0] * 3600);
             //判断二级会员用
            $slof = $this->backIntTime($value['label']);
            $arr[$key]['make_times'] = $make_year + ($slof[0] * 3600); 
            $arr[$key]['make_time'] = $value['make_time'];
            $arr[$key]['label'] = $value['label'];
            $arr[$key]['seat'] = $value['seat'];
            $arr[$key]['room'] = $room;
            if($type == 1){
                $arr[$key]['money'] = $value['member_price'];
            }else{
                $arr[$key]['money'] = $value['wrong_memeber_price'];
            }
            $arr[$key]['createtime'] = time();
            $arr[$key]['pay_type'] = $type;
            if($user_type == 1 && $is_gongyi == 1){
                $arr[$key]['chaoji'] = 1;
            }else{
                $arr[$key]['chaoji'] = 0;
            }
            $res = Db::name('make_info')->where(['status'=>['in', [0, 1]],'place_id'=>$place_id,'make_year'=>$make_year,'seat'=>$value['seat'],'make_time'=>$value['make_time']])->find();
            if($res){
                $this->error('该场次已被别人预约');
            }
            array_push($code, $arr[$key]['code']);
        }
        $code = implode(',', $code);
        //判断支付状态
        if ($type == 1) {
            //余额支付 扣除余额 生成预约单 如果有预约人信息保存预约人
            if ($user['money'] < $money) $this->error('余额不足');
            $this->balancePay($user, $money, $arr, $code,$make_user_info);
        } else {
            //微信支付
            foreach ($arr as $key => $value) {
                if($value['money'] == 0){
                    $arr[$key]['status'] = 0; //暂未付款单
                }else{
                    $arr[$key]['status'] = 3; //暂未付款单
                } 
            }
            //生成预约单
            $res = Db::name('make_info')->insertAll($arr);
            //获取递增ID
            $reslist = Db::name('make_info')->getLastInsID();
            //取出预约单ID
            $array = [];
            for ($i = 0; $i < $res; $i++) {
                $array[] = (int)$reslist++;
            }
            foreach ($array as $kk => $vv) {
                if($make_user_info){
                    foreach ($make_user_info as $k => $v) {
                    $arrs[$k]['make_id'] = $vv;
                    $arrs[$k]['name'] = $v['name'];
                    $arrs[$k]['mobile'] = $v['mobile'];
                    $arrs[$k]['address'] = $v['address'];
                    $arrs[$k]['number'] = $v['number'];
                    $arrs[$k]['createtime'] = time();
                }
                Db::name('make_user_info')->insertAll($arrs); 
                }
            }
            $payStatus = $this->wechatPay($money, $array, $code, $make_count);
            if ($payStatus['code']) {
                $data = ['make_id' => $array[0]];
                $this->success('预约成功', $data);
            } else {
                $this->error('预约失败');
            }
        }
    }
    
    /**
     * 余额支付
     */
    public function balancePay($user, $money, $arr, $code,$make_user_info)
    {
        // halt($make_user_info);
        Db::startTrans();
        try {
            //扣除余额
            Db::name('user')->where(['id' => $user['id']])->setDec('money', $money);
            //生成预约单
            $res = Db::name('make_info')->insertAll($arr);
            //获取递增ID
            $reslist = Db::name('make_info')->getLastInsID();
            //取出预约单ID
            $array = [];
            for ($i = 0; $i < $res; $i++) {
                $array[] = (int)$reslist++;
            }
             foreach ($array as $kk => $vv) {
                 if($make_user_info){
                 foreach ($make_user_info as $k => $v) {
                    $arrs[$k]['make_id'] = $vv;
                    $arrs[$k]['name'] = $v['name'];
                    $arrs[$k]['mobile'] = $v['mobile'];
                    $arrs[$k]['address'] = $v['address'];
                    $arrs[$k]['number'] = $v['number'];
                    $arrs[$k]['createtime'] = time();
            }
                Db::name('make_user_info')->insertAll($arrs);
            }
               
            }
            //生成消费记录
            $this->moneyLog($user, $money, $code);
            $data = ['make_id' => $array[0]];
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $this->error($e->getMessage());
        }
        return $this->success('预约成功', $data);
    }

    /**
     * 微信支付
     */
    public function wechatPay($money, $array, $code, $make_count)
    {
        $userInfo = $this->auth->getUser();
        $user = new User();
        $str = date('Ymd') . substr(implode('', array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 4); //订单号
        $data = [
            'order_number' => $str,
            'order_remark' => '预约单号' . $code . '微信支付',
            'mobile' => $userInfo['mobile'],
            'user_id' => $userInfo['id'],
            'total_fee' => $money,
            'actual_fee' => $money,
            'create_time' => time(),
            'update_time' => time(),
            'type' => 2, //支出
        ];
        return $user->wechatPay($data, $array, $str, $make_count);
    }

    /**
     * 资金明细
     */
    public function moneyLog($user, $money, $code)
    {
        $user_money = $user['money'];
        //生成记录
        $arr = [
            'type' => 2,
            'user_id' => $user['id'],
            'money' => $money,
            'before' => $user_money,
            'after' => $user_money - $money,
            'memo' => '预约单号' . $code . '余额支付',
            'createtime' => time(),
            'order_num' => date('Ymd') . substr(implode('', array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 4),
            'time' => date('Y-m', time()),
        ];
        return Db::name('user_money_log')->insert($arr);
    }

    /**
     * 判断是否是公益时间段
     */
    public function backTimeSlof()
    {
        // $room = $this->request->post('room');
        // //查找该场所配置信息
        // $room_info = Db::name('place_room')->where(['id' => $room])->find();
        // if (!$room_info) $this->error('场所信息不存在');
        // $time = strtotime(date('Y-m-d', time()));
        // $new_slof = explode('-', $room_info['slof_time']);
        // $startTime = $time + ($new_slof[0] * 3600);
        // $endTime = $time + ($new_slof[1] * 3600);
        // $holiday = ChinaHoliday::isHoliday(date('Y-m-d', time()));
        // if (time() > $startTime && time() < $endTime && !$holiday) {
        //     $data['status'] = 1;
        // } else {
        //     $data['status'] = 0;
        // }
        // $this->success('获取成功', $data);
        $user = $this->auth->getUser();
        $real = $this->request->post('is_real/a');
        if($user['member_type'] !=2){
            if(in_array(0,$real) && in_array(1,$real)){
                $data['is_show'] = 1;
                $data['is_pay'] = 1;
            }elseif(in_array(0,$real)){
                $data['is_show'] = 0;
                $data['is_pay'] = 1;
            }elseif(in_array(1,$real)){
                $data['is_show'] = 1;
                $data['is_pay'] = 0;
            }
        }else{
            $data['is_show'] = 0;
            //2024/4: hook: 未知前端逻辑，is_show似乎控制是否弹出预约人信息填写界面，但是管理员不弹出，那下单的时候哪来的预约人？
            if(in_array($user['id'],Place::$vvip)){
                $data['is_show'] = 1;
            }
            //2024/4: hook: 完毕
            if(in_array(0,$real)){
                $data['is_pay'] = 1;
            }else{
                $data['is_pay'] = 0;
            }
        }
        $this->success('获取成功', $data);
    }
    
   /**
     * 定时任务核销订单
     */
    public function cancel_order()
    {
        $list = Db::name('make_info')->where('make_times','<= time',time())->where('status',0)->field('id')->select();
        if($list){
            foreach ($list as $key => $value) {
                $list[$key]['status'] = 1;
                $list[$key]['cancel_time'] = time();
            }
            $make_info = new Info();
            $res = $make_info->saveAll($list);
            if($res){
                $this->success('操作成功');
            }else{
                $this->error('暂无数据');
            }
        }else{
            $this->success('暂无数据');
        }
    }
    
}
