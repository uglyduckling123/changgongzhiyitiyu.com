define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/order/index' + location.search,
                    add_url: '',
                    edit_url: 'order/order/edit',
                    del_url: '',
                    import_url: '',
                    dragsort_url:'',
                    table: 'order',
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
                        {field: 'order_number', title: __('订单编号'), operate: 'LIKE'},
                        {field: 'business_type', title: __('业务类型'), searchList: {"1":__('信用卡'),"2":__('贷款'),"3":__('拉新')},formatter:function (value, row, index) {
                                if(value == '1'){
                                    return '信用卡';
                                }else if(value == 2){
                                    return '贷款';
                                }else if(value == 3){
                                    return '拉新';
                                }
                            }
                        },
                        {field: 'business_name', title: __('业务名称'), operate: 'LIKE'},
                        {field: 'reward', title: __('奖励'), operate: false},
                        {field: 'mobile', title: __('手机号'), operate: 'LIKE'},
                        {field: 'realname', title: __('真实名称'), operate: 'LIKE'},
                        {field: 'id_card', title: __('身份证号'), operate: 'LIKE'},
                        {field: 'car_card', title: __('车牌号'), operate: 'LIKE'},
                        /* {field: 'introduce', title: __('要求'), operate: 'LIKE'}, */
                        {field: 'status', title: __('状态'), searchList: {"0":__('申请中'),"1":__('已完成'),"2":__('已驳回')},formatter:function (value, row, index) {
                            if(value == 0){
                                return '申请中';
                            }else if(value == 1){
                                return '已完成';
                            }else if(value == 2){
                                return '已驳回';
                            }
                        }},
                        {field: 'create_time', title: __('申请时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
