define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'business/business/index' + location.search,
                    add_url: 'business/business/add',
                    edit_url: 'business/business/edit',
                    del_url: 'business/business/del',
                    import_url: 'business/business/import',
                    dragsort_url:'',
                    table: 'business',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('ID')},
                        {field: 'type', title: __('业务类型'), searchList: {"1":__('信用卡'),"2":__('贷款'),"3":__('拉新')},formatter:function (value, row, index) {
                                if(value == '1'){
                                    return '信用卡';
                                }else if(value == 2){
                                    return '贷款';
                                }else if(value == 3){
                                    return '拉新';
                                }
                            }
                        },
                        {field: 'name', title: __('业务名称'), operate: false},
                        {field: 'reward', title: __('奖励'), operate: false},
                        {field: 'status', title: __('状态'), searchList: {"1":__('正常'),"2":__('禁用')},formatter:function (value, row, index) {
                            if(value == '1'){
                                return '正常';
                            }else if(value == 2){
                                return '禁用';
                            }
                        }},
                        {field: 'create_time', title: __('创建时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
