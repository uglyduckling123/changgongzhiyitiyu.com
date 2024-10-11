define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'integral/bank/index' + location.search,
                    add_url: 'integral/bank/add',
                    edit_url: 'integral/bank/edit',
                    del_url: 'integral/bank/del',
                    import_url: 'integral/bank/import',
                    dragsort_url:'',
                    table: 'bank',
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
                        {field: 'name', title: __('银行名称'), operate: false},
                        {field: 'image', title: __('图片'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'status', title: __('状态'), operate: 'like',formatter:function (value, row, index) {
                            if(value == '1'){
                                return '正常';
                            }else if(value == 2){
                                return '禁用';
                            }
                        }},
                        {field: 'create_time', title: __('创建时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate},
                        {
                            field: 'buttons',
                            width: "120px",
                            title: __('积分商品'), 
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('积分商品'),
                                    title: function(row){
                                        if(row.name.length > 7){
                                            return '积分商品:'+row.name.substr(0,7)+'...'
                                        }else{
                                            return '积分商品:'+row.name
                                        }
                                    },
                                    classname: 'btn btn-xs btn-primary btn-addtabs',
                                    icon: 'fa fa-align-justify',
                                    url: 'integral/integral_goods/index',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    },
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        return true;
                                    }
                                },
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
