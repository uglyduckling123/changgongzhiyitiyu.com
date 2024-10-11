define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'venue/seat/index' + location.search,
                    add_url: 'venue/seat/add',
                    edit_url: 'venue/seat/edit',
                    del_url: 'venue/seat/del',
                    multi_url: 'venue/seat/multi',
                    import_url: 'venue/seat/import',
                    table: 'seat',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'place.name', title: __('Place.name'), operate: 'LIKE'},
                        {field: 'room.name', title: __('所属场所'), operate: 'LIKE'},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
                        {field: 'one_set', title: __('One_set'), operate: 'LIKE'},
                        {field: 'two_set', title: __('Two_set'), operate: 'LIKE'},
                        {field: 'three_set', title: __('Three_set'), operate: 'LIKE'},
                        {field: 'four_set', title: __('Four_set'), operate: 'LIKE'},
                        {field: 'five_set', title: __('Five_set'), operate: 'LIKE'},
                        {field: 'six_set', title: __('Six_set'), operate: 'LIKE'},
                        {field: 'seven_set', title: __('Seven_set'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
