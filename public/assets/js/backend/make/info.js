define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'make/info/index' + location.search,
                    add_url: 'make/info/add',
                    multi_url: 'make/info/multi',
                    import_url: 'make/info/import',
                    table: 'make_info',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                // fixedColumns: true,
                // fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'user.nickname', title: __('用户昵称'), operate: 'LIKE'},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'place.name', title: __('Place.name'), operate: 'LIKE'},
                        {field: 'make_year', title: __('Make_year'),operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime,datetimeFormat: "YYYY-MM-DD"},
                        {field: 'make_time', title: __('Make_time'), operate: 'LIKE'},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('已退款|订单取消'),"3":__('未付款')}, formatter: Table.api.formatter.status},
                        {field: 'label', title: __('预约场次')},
                        {field: 'seat.name', title: __('预约场地')},
                        {field: 'room.name', title: __('室内室外'), searchList: {"1":__('Room 1'),"2":__('Room 2')}, formatter: Table.api.formatter.normal},
                        {field: 'pay_type', title: __('Pay_type'), searchList: {"1":__('Pay_type 1'),"2":__('Pay_type 2'),"3":__('无需付款')}, formatter: Table.api.formatter.normal},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'cancel_time', title: __('Cancel_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate},
                        {
                            field: 'buttons',
                            width: "120px",
                            title: __('预约人信息'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('预约信息'),
                                    title: __('预约信息'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-money',
                                    url: function(row){
                                        return 'make/Info/make?id='+row.id;
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
                            field: 'status',
                            width: "120px",
                            title: __('操作'),
                            table: table,
                            operate:false,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'success',
                                    text: __('核销'),
                                    title: __('核销'),
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-check',
                                    url:function (row) {
                                        return 'make/Info/audit?id='+row.id
                                    },
                                    confirm: '确认通过吗?',
                                    success: function (data, ret) {
                                        table.bootstrapTable('refresh');
                                    },
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        if(row.status == 0){
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'success',
                                    text: __('退款'),
                                    title: __('退款'),
                                    classname: 'btn btn-xs btn-error btn-ajax',
                                    icon: 'fa fa-warning',
                                    url:function (row) {
                                        return 'make/Info/applyRefund?make_id='+row.id
                                    },
                                    confirm: '确认退款吗?',
                                    success: function (data, ret) {
                                        table.bootstrapTable('refresh');
                                    },
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        if(row.status == 0 && row.pay_type !=3){
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'success',
                                    text: __('取消'),
                                    title: __('取消订单'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    icon: 'fa fa-warning',
                                    url:function (row) {
                                        return 'make/Info/cancleOrder?make_id='+row.id
                                    },
                                    confirm: '确认取消订单吗?',
                                    success: function (data, ret) {
                                        table.bootstrapTable('refresh');
                                    },
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        if(row.status == 0){
                                            return true;
                                        }
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.buttons
                        }
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
