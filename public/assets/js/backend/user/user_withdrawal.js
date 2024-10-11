define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user_withdrawal/index',
                    add_url: '',
                    edit_url: 'user/user_withdrawal/edit',
                    del_url: '',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                columns: [
                    [
                        // {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true},
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'mobile', title: __('手机号'), operate: 'LIKE'},
                        {field: 'bank_card', title: __('提现银行卡'), operate: false},
                        {field: 'remain_money', title: __('账户金额'), operate: false},
                        {field: 'money', title: __('提现金额'), operate: false},
                        {field: 'create_time', title: __('申请时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('状态'), searchList: {"0":__('申请中'),"1":__('已提现'),"2":__('驳回')},formatter:function (value, row, index) {
                            if(value == '0'){
                                return '申请中';
                            }else if(value == '1'){
                                return '已提现';
                            }else if(value == 2){
                                return '驳回';
                            }
                        }},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate},
                    
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
        withdrawal: function () {
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