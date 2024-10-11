define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'integral/integral_order/index' + location.search,
                    add_url: '',
                    edit_url: 'integral/integral_order/edit',
                    del_url: '',
                    import_url: '',
                    dragsort_url:'',
                    table: 'integral_order',
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
                        {field: 'integral_goods_name', title: __('积分商品名称'), operate: 'LIKE'},
                        {field: 'since_unit', title: __('起兑单位'), operate: false},
                        {field: 'integral_value', title: __('积分价值(元/万'), operate: false},
                        {field: 'nickname', title: __('用户昵称'), operate: 'LIKE'},
                        {field: 'mobile', title: __('用户手机号'), operate: 'LIKE'},
                        {field: 'status', title: __('状态'), searchList: {"0":__('待处理'),"1":__('申请中'),"2":__('已完成'),"3":__('已驳回')},formatter:function (value, row, index) {
                            if(value == 0){
                                return '待处理';
                            }else if(value == 1){
                                return '申请中';
                            }else if(value == 2){
                                return '已完成';
                            }else if(value == 3){
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
