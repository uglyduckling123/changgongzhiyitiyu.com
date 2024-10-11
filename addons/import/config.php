<?php

return [
    [
        'name' => 'exclude',
        'title' => '禁用导入的数据表',
        'type' => 'text',
        'content' => [],
        'value' => 'fa_admin'."\r\n"
            .'fa_attachment'."\r\n"
            .'fa_auth_group'."\r\n"
            .'fa_auth_group_access'."\r\n"
            .'fa_auth_rule',
        'rule' => '',
        'msg' => '',
        'tip' => '多个分行填列',
        'ok' => '',
        'extend' => '',
    ],
];
