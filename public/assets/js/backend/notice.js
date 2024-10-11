define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'notice/index' + location.search,
                    add_url: 'notice/add',
                    edit_url: 'notice/edit',
                    //del_url: 'notice/del',
                    import_url: 'notice/import',
                    dragsort_url:'',
                    table: 'notice',
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
                        {field: 'type', title: __('类型'), searchList: {"1":__('系统通知'),"2":__('消息')},formatter:function (value, row, index) {
                                if(value == '1'){
                                    return '系统通知';
                                }else if(value == 2){
                                    return '消息';
                                }
                            }
                        },
                        {field: 'receive_object', title: __('接收对象'), searchList: {"1":__('全部'),"2":__('指定对象')}, operate: false,formatter:function (value, row, index) {
                                if(value == '1'){
                                    return '全部';
                                }else if(value == 2){
                                    return '指定对象';
                                }
                            }
                        },
                        {field: 'title', title: __('公告标题'), operate: 'like'},
                        {field: 'subtitle', title: __('公告副标题'), operate: 'like'},
                        {field: 'status', title: __('状态'), searchList: {"1":__('正常'),"2":__('撤销')}, operate: 'like',formatter:function (value, row, index) {
                            if(value == '1'){
                                return '正常';
                            }else if(value == 2){
                                return '撤销';
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
            $(function(){
                $('.receive_users').css('display','none');
            });
            $('#receive_object').on( 'change', function(){
                var receive_object = $('#receive_object').val();
                if(receive_object == 2){
                    $('.receive_users').css('display','block');
                }else{
                    $('.receive_users').css('display','none');
                }
            });
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
