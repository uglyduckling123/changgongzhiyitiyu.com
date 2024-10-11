define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'venue/place_room/index' + location.search,
                    add_url: 'venue/place_room/add',
                    edit_url: 'venue/place_room/edit',
                    del_url: 'venue/place_room/del',
                    multi_url: 'venue/place_room/multi',
                    import_url: 'venue/place_room/import',
                    table: 'place_room',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'place.name', title: __('Place.name'), operate: 'LIKE'},
                        {field: 'slof_time', title: __('公益时间段'), operate: 'LIKE'},
                        {field: 'gold_one_time', title: __('第一时间段'), operate: 'LIKE'},
                        {field: 'gold_two_time', title: __('第二时间段'), operate: 'LIKE'},
                        {field: 'one_first_price', title: __('第一时间段会员价格'), operate: 'LIKE'},
                        {field: 'one_second_price', title: __('第一时间段非会员价格'), operate: 'LIKE'},
                        {field: 'weekday_one_price', title: __('第二时间段会员价格'), operate: 'LIKE'},
                        {field: 'weekday_two_price', title: __('第二时间段非会员价格'), operate: 'LIKE'},
                        {field: 'saturday_one_price', title: __('周六节假日会员价格'), operate: 'LIKE'},
                        {field: 'saturday_two_price', title: __('周六节假日非会员价格'), operate: 'LIKE'},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
