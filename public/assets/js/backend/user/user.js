define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    add_url: 'user/user/add',
                    // edit_url: 'user/user/edit',
                    // del_url: 'user/user/del',
                    withdrawal_url: 'user/user/withdrawal',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'user.id',
                columns: [
                    [
                        // {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true,width: '50'},
                        {field: 'nickname', title: __('昵称'), operate: 'LIKE',width:'50'},
                        {field: 'avatar', title: __('头像'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'mobile', title: __('手机号'), operate: 'LIKE'},
                        {field: 'money', title: __('账户余额'), operate: 'LIKE'},
                        {field: 'member_type', title: __('会员类型'),formatter:function (value){
                            var str = ''
                            switch (value) {
                                case 0:str="<strong style='color: dimgray'>普通会员</strong>";break;
                                case 1:str="<strong style='color: red'>二级会员</strong>";break;
                                case 2:str="<strong style='color: orange'>超级会员</strong>";break;
                            }
                                return str;
                            }},
                        {
                            field: 'buttons',
                            width: "120px",
                            title: __('资金'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('资金流水'),
                                    title: __('资金流水'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-list-ol',
                                    url: function(row){
                                        return 'user/user/balance_money?id='+row.id;
                                    },
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    },
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        return true;
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.buttons
                        },
                        {
                            field: 'buttons', title: __('充值操作'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'recharge',
                                    title: __('虚拟充值'),
                                    text: '充值金额',
                                    classname: 'btn btn-success btn-primary btn-dialog',
                                    icon: 'fa fa-money',
                                    url: 'user/user/recharge',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "充值成功"});
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.buttons
                        },
                        {
                            field: 'buttons', title: __('会员权限设置'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'member',
                                    text: '设为超级会员',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    // icon: 'fa fa-group',
                                    url: function(row){
                                        return 'user/user/setmember?type=1&id='+row.id;
                                    },
                                    confirm: '确认设定为超级会员吗?',
                                    success: function (data, ret) {
                                        table.bootstrapTable('refresh');
                                    },
                                    visible: function (row) {
                                        if(row.member_type == 0){
                                            //返回true时按钮显示,返回false隐藏
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }
                                },
                                {
                                    name: 'member',
                                    text: '设为二级会员',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    // icon: 'fa fa-group',
                                    url: function(row){
                                        return 'user/user/setmember?type=2&id='+row.id;
                                    },
                                    confirm: '确认设定为二级会员吗?',
                                    success: function () {
                                        table.bootstrapTable('refresh');
                                    },
                                    visible: function (row) {
                                        if(row.member_type == 0){
                                            //返回true时按钮显示,返回false隐藏
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }
                                },
                                {
                                    name: 'member',
                                    text: '取消超级会员',
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    // icon: 'fa fa-group',
                                    url: function(row){
                                        return 'user/user/cancelmember?id='+row.id;
                                    },
                                    confirm: '确认取消超级会员权限吗?',
                                    success: function () {
                                        table.bootstrapTable('refresh');
                                    },
                                    visible: function (row) {
                                        if(row.member_type == 2){
                                            //返回true时按钮显示,返回false隐藏
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }
                                },
                                {
                                    name: 'member',
                                    text: '取消二级会员',
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    // icon: 'fa fa-user',
                                    url: function(row){
                                        return 'user/user/cancelmember?id='+row.id;
                                    },
                                    confirm: '确认取消二级会员权限吗?',
                                    success: function (data, ret) {
                                        table.bootstrapTable('refresh');
                                    },
                                    visible: function (row) {
                                        if(row.member_type == 1){
                                            //返回true时按钮显示,返回false隐藏
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.buttons
                        },
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        recharge: function () {
            Controller.api.bindevent();
        },
        member: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});