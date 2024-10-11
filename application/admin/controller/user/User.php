<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\library\Auth;
use app\common\model\Config  as configModel;
use think\Log;
use think\Exception;
use think\Db;
use think\Loader;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;
    protected $searchFields = '';

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('User');
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
                   ->order($sort, $order)
                   ->limit($offset, $limit)
                   ->select();
           foreach ($list as &$v) {
                    $v->avatar = $v->avatar ? cdnurl($v->avatar, true) : letter_avatar($v->nickname);
                    $v->hidden(['password', 'salt']);
           }
           $list = collection($list)->toArray();
           $result = array("total" => $total, "rows" => $list);

           return json($result);
       }
       return $this->view->fetch();
    }

    public function getData()
    {
        //当前是否为关联查询
       $this->relationSearch = true;
       //设置过滤方法
       $this->request->filter(['strip_tags', 'trim']);
       if ($this->request->isAjax())
       {
           $search = $this->request->param("name");
           $page = $this->request->param("pageNumber");
           $limit = $this->request->param("pageSize");
           $offset = ($page-1)*$limit;
           if(!empty($search)){
              $where = ['username'=>['like',"%$search%"]];
           }else{
             $where = [];
           }
           $total = $this->model->where($where)->count();
           $list = $this->model
                   ->where($where)
                   ->limit($offset, $limit)
                   ->field('id,username name')
                   ->select();
            $result = array("total" => $total, "list" => $list);
            return json($result);
       }
       return $this->view->fetch();
    }

    /**
     * 生成查询所需要的条件,排序方式
     * @param mixed   $searchfields   快速查询的字段
     * @param boolean $relationSearch 是否关联查询
     * @return array
     */
    // protected function buildparams($searchfields = null, $relationSearch = null)
    // {
    //     $searchfields = is_null($searchfields) ? $this->searchFields : $searchfields;
    //     $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
    //     $search = $this->request->get("search", '');
    //     $filter = $this->request->get("filter", '');
    //     $op = $this->request->get("op", '', 'trim');
    //     $sort = $this->request->get("sort", !empty($this->model) && $this->model->getPk() ? $this->model->getPk() : 'id');
    //     $order = $this->request->get("order", "DESC");
    //     $offset = $this->request->get("offset/d", 0);
    //     $limit = $this->request->get("limit/d", 999999);
    //     //新增自动计算页码
    //     $page = $limit ? intval($offset / $limit) + 1 : 1;
    //     if ($this->request->has("page")) {
    //         $page = $this->request->get("page/d", 1);
    //     }
    //     $this->request->get([config('paginate.var_page') => $page]);
    //     $filter = (array)json_decode($filter, true);
    //     $op = (array)json_decode($op, true);
    //     $filter = $filter ? $filter : [];
    //     $where = [];
    //     $alias = [];
    //     $bind = [];
    //     $name = '';
    //     $aliasName = '';
    //     if (!empty($this->model) && $this->relationSearch) {
    //         $name = $this->model->getTable();
    //         $alias[$name] = Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
    //         $aliasName = $alias[$name] . '.';
    //     }
    //     $sortArr = explode(',', $sort);
    //     foreach ($sortArr as $index => & $item) {
    //         $item = stripos($item, ".") === false ? $aliasName . trim($item) : $item;
    //     }
    //     unset($item);
    //     $sort = implode(',', $sortArr);
    //     $adminIds = $this->getDataLimitAdminIds();
    //     if (is_array($adminIds)) {
    //         $where[] = [$aliasName . $this->dataLimitField, 'in', $adminIds];
    //     }
    //     if ($search) {
    //         $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
    //         foreach ($searcharr as $k => &$v) {
    //             $v = stripos($v, ".") === false ? $aliasName . $v : $v;
    //         }
    //         unset($v);
    //         $where[] = [implode("|", $searcharr), "LIKE", "%{$search}%"];
    //     }
    //     $index = 0;
    //     foreach ($filter as $k => $v) {
    //         if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $k)) {
    //             continue;
    //         }
    //         $sym = isset($op[$k]) ? $op[$k] : '=';
    //         if (stripos($k, ".") === false) {
    //             $k = $aliasName . $k;
    //         }
    //         $v = !is_array($v) ? trim($v) : $v;
    //         $sym = strtoupper(isset($op[$k]) ? $op[$k] : $sym);
    //         //null和空字符串特殊处理
    //         if (!is_array($v)) {
    //             if (in_array(strtoupper($v), ['NULL', 'NOT NULL'])) {
    //                 $sym = strtoupper($v);
    //             }
    //             if (in_array($v, ['""', "''"])) {
    //                 $v = '';
    //                 $sym = '=';
    //             }
    //         }

    //         switch ($sym) {
    //             case '=':
    //             case '<>':
    //                 if(strpos($k,'name') !== false){
    //                 }else{
    //                     $where[] = [$k, $sym, (string)$v];
    //                 }  
    //                 break;
    //             case 'LIKE':
    //             case 'NOT LIKE':
    //             case 'LIKE %...%':
    //             case 'NOT LIKE %...%':
    //                 if(strpos($k,'name') !== false){
    //                 }else{
    //                     $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
    //                 }
                    
    //                 break;
    //             case '>':
    //             case '>=':
    //             case '<':
    //             case '<=':
    //                 $where[] = [$k, $sym, intval($v)];
    //                 break;
    //             case 'FINDIN':
    //             case 'FINDINSET':
    //             case 'FIND_IN_SET':
    //                 $v = is_array($v) ? $v : explode(',', str_replace(' ', ',', $v));
    //                 $findArr = array_values($v);
    //                 foreach ($findArr as $idx => $item) {
    //                     $bindName = "item_" . $index . "_" . $idx;
    //                     $bind[$bindName] = $item;
    //                     $where[] = "FIND_IN_SET(:{$bindName}, `" . str_replace('.', '`.`', $k) . "`)";
    //                 }
    //                 break;
    //             case 'IN':
    //             case 'IN(...)':
    //             case 'NOT IN':
    //             case 'NOT IN(...)':
    //                 $where[] = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
    //                 break;
    //             case 'BETWEEN':
    //             case 'NOT BETWEEN':
    //                 $arr = array_slice(explode(',', $v), 0, 2);
    //                 if (stripos($v, ',') === false || !array_filter($arr, function($v){
    //                     return $v != '' && $v !== false && $v !== null;
    //                 })) {
    //                     continue 2;
    //                 }
    //                 //当出现一边为空时改变操作符
    //                 if ($arr[0] === '') {
    //                     $sym = $sym == 'BETWEEN' ? '<=' : '>';
    //                     $arr = $arr[1];
    //                 } elseif ($arr[1] === '') {
    //                     $sym = $sym == 'BETWEEN' ? '>=' : '<';
    //                     $arr = $arr[0];
    //                 }
    //                 $where[] = [$k, $sym, $arr];
    //                 break;
    //             case 'RANGE':
    //             case 'NOT RANGE':
    //                 $v = str_replace(' - ', ',', $v);
    //                 $arr = array_slice(explode(',', $v), 0, 2);
    //                 if (stripos($v, ',') === false || !array_filter($arr)) {
    //                     continue 2;
    //                 }
    //                 //当出现一边为空时改变操作符
    //                 if ($arr[0] === '') {
    //                     $sym = $sym == 'RANGE' ? '<=' : '>';
    //                     $arr = $arr[1];
    //                 } elseif ($arr[1] === '') {
    //                     $sym = $sym == 'RANGE' ? '>=' : '<';
    //                     $arr = $arr[0];
    //                 }
    //                 $tableArr = explode('.', $k);
    //                 if (count($tableArr) > 1 && $tableArr[0] != $name && !in_array($tableArr[0], $alias) && !empty($this->model)) {
    //                     //修复关联模型下时间无法搜索的BUG
    //                     $relation = Loader::parseName($tableArr[0], 1, false);
    //                     $alias[$this->model->$relation()->getTable()] = $tableArr[0];
    //                 }
    //                 $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' TIME', $arr];
    //                 break;
    //             case 'NULL':
    //             case 'IS NULL':
    //             case 'NOT NULL':
    //             case 'IS NOT NULL':
    //                 $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
    //                 break;
    //             default:
    //                 break;
    //         }
    //         $index++;
    //     }
    //     if (!empty($this->model)) {
    //         $this->model->alias($alias);
    //     }
    //     $model = $this->model;
    //     $where = function ($query) use ($where, $alias, $bind, &$model) {
    //         if (!empty($model)) {
    //             $model->alias($alias);
    //             $model->bind($bind);
    //         }
    //         foreach ($where as $k => $v) {
    //             if (is_array($v)) {
    //                 call_user_func_array([$query, 'where'], $v);
    //             } else {
    //                 $query->where($v);
    //             }
    //         }
    //     };
    //     return [$where, $sort, $order, $offset, $limit, $page, $alias, $bind];
    // }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $this->token();
        }
        return parent::add();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("参数错误"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        Auth::instance()->delete($row['id']);
        $this->success();
    }

    /**
     * 资金流水
     */
    public function balance_money()
    {
        $id = $this->request->get('id');
        $list = Db::name('user_money_log')->where('user_id',$id)->paginate(5,false, [
            'query' => request()->param(),
        ]);
        $this->assign('list',$list);
        return $this->view->fetch();
    }

    /**
     * 充值金额
     */
    public function recharge($ids = null)
    {
        $row = $this->model->get($ids);
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $money = $params['money'];
                    $balance = Db::name('user')->where('id', $params['id'])->value('money');
                    $params['money'] = $money + $balance;
                    $result = $row->allowField(true)->save($params);
                    $data['uid'] = $params['id'];
                    $data['money'] = $money;
                    $data['createtime'] = time();
                    $data['admin_id'] = $this->auth->id;
                    $data['ip'] = request()->ip();
                    Db::name('admin_recharge')->insert($data);
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success('操作成功');
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $row['nickname'] = Db::name('user')->where('id', $row['id'])->value('nickname');
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 设置会员权限
     */
    public function setmember()
    {
        $gets = $this->request->get();
        if($gets){
            if($gets['type'] == 1){
                $arr['member_type'] = 2;
            }else{
                $arr['member_type'] = 1;
            }
            $where['id'] = $gets['id'];
            $res = $this->model->isUpdate(true)->allowField(true)->save($arr,$where);
            if(!$res){
                $this->error('没有可更新的行数');
            }
            $this->success('操作成功');
        }
    }
    /**
     * 取消会员权限
     */
    public function cancelmember()
    {
        $gets = $this->request->get();
        if($gets){
            $arr['member_type'] = 0;
            $arr['updatetime'] = time();
            $where['id'] = $gets['id'];
            $res = $this->model->isUpdate(true)->allowField(true)->save($arr,$where);
            if(!$res){
                $this->error('没有可更新的行数');
            }
            $this->success('操作成功');
        }
    }
}
