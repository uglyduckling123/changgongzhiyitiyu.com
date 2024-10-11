define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {

            var bank_id = $('#bank_id').val();

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'integral/integral_goods/index' + location.search+'&bank_id='+bank_id,
                    add_url: 'integral/integral_goods/add?bank_id='+bank_id,
                    edit_url: 'integral/integral_goods/edit',
                    del_url: 'integral/integral_goods/del',
                    import_url: 'integral/integral_goods/import',
                    dragsort_url:'',
                    table: 'integral_goods',
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
                        {field: 'name', title: __('商品名称'), operate: false},
                        {field: 'advantage', title: __('优势'), operate: false},
                        {field: 'since_unit', title: __('起兑单位'), operate: false},
                        {field: 'change_number', title: __('兑换次数'), operate: false},
                        {field: 'integral_value', title: __('积分价值(元/万)'), operate: false},
                        {field: 'status', title: __('状态'), operate: 'like',formatter:function (value, row, index) {
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
