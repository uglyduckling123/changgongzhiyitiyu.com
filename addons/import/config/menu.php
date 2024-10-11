<?php
/**
 * 菜单配置文件
 */

return array (
  0 => 
  array (
    'type' => 'file',
    'name' => 'import',
    'title' => '批量导入日志',
    'icon' => 'fa fa-list',
    'url' => '',
    'condition' => '',
    'remark' => '',
    'ismenu' => 1,
    'menutype' => NULL,
    'extend' => '',
    'py' => 'pldrrz',
    'pinyin' => 'piliangdaorurizhi',
    'sublist' => 
    array (
      0 => 
      array (
        'type' => 'file',
        'name' => 'import/log',
        'title' => '数据导入记录',
        'icon' => 'fa fa-circle-o',
        'url' => '',
        'condition' => '',
        'remark' => '',
        'ismenu' => 1,
        'menutype' => NULL,
        'extend' => '',
        'py' => 'sjdrjl',
        'pinyin' => 'shujudaorujilu',
        'sublist' => 
        array (
          0 => 
          array (
            'type' => 'file',
            'name' => 'import/log/add',
            'title' => '添加',
            'icon' => 'fa fa-circle-o',
            'url' => '',
            'condition' => '',
            'remark' => '',
            'ismenu' => 0,
            'menutype' => NULL,
            'extend' => '',
            'py' => 'tj',
            'pinyin' => 'tianjia',
          ),
          1 => 
          array (
            'type' => 'file',
            'name' => 'import/log/edit',
            'title' => '编辑',
            'icon' => 'fa fa-circle-o',
            'url' => '',
            'condition' => '',
            'remark' => '',
            'ismenu' => 0,
            'menutype' => NULL,
            'extend' => '',
            'py' => 'bj',
            'pinyin' => 'bianji',
          ),
          2 => 
          array (
            'type' => 'file',
            'name' => 'import/log/preview',
            'title' => 'Preview',
            'icon' => 'fa fa-circle-o',
            'url' => '',
            'condition' => '',
            'remark' => '',
            'ismenu' => 0,
            'menutype' => NULL,
            'extend' => '',
            'py' => 'P',
            'pinyin' => 'Preview',
          ),
          3 => 
          array (
            'type' => 'file',
            'name' => 'import/log/index',
            'title' => '查看',
            'icon' => 'fa fa-circle-o',
            'url' => '',
            'condition' => '',
            'remark' => '',
            'ismenu' => 0,
            'menutype' => NULL,
            'extend' => '',
            'py' => 'zk',
            'pinyin' => 'zhakan',
          ),
          4 => 
          array (
            'type' => 'file',
            'name' => 'import/log/del',
            'title' => '删除',
            'icon' => 'fa fa-circle-o',
            'url' => '',
            'condition' => '',
            'remark' => '',
            'ismenu' => 0,
            'menutype' => NULL,
            'extend' => '',
            'py' => 'sc',
            'pinyin' => 'shanchu',
          ),
          5 => 
          array (
            'type' => 'file',
            'name' => 'import/log/multi',
            'title' => '批量更新',
            'icon' => 'fa fa-circle-o',
            'url' => '',
            'condition' => '',
            'remark' => '',
            'ismenu' => 0,
            'menutype' => NULL,
            'extend' => '',
            'py' => 'plgx',
            'pinyin' => 'pilianggengxin',
          ),
        ),
      ),
    ),
  ),
);