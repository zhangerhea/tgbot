define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'leescore/leescoreads/index',
                    add_url: 'leescore/leescoreads/add',
                    edit_url: 'leescore/leescoreads/edit',
                    del_url: 'leescore/leescoreads/del',
                    multi_url: 'leescore/leescoreads/multi',
                    table: 'leescore/leescore_ads',
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
                        {field: 'name', title: __('Name')},
                        {field: 'thumb', title: __('Thumb'), operate:false, formatter: Table.api.formatter.image},
                        {field: 'open_mode', title: __('Open_mode'), visible:false, searchList: {"0":__('Open_mode 0'),"1":__('Open_mode 1')}},
                        {field: 'open_mode_text', title: __('Open_mode'), operate:false},
                        {field: 'path_url', title: __('Path_url'), formatter: Table.api.formatter.url},
                        {field: 'position', title: __('Position'), visible:false, searchList: {"slider":__('position slider'),"activicy":__('position activicy'),"other":__('position other')}},
                        {field: 'position_text', title: __('Position'), operate:false},
                        {field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"0":__('status off'),"1":__('status on')}},
                        {field: 'status_text', title: __('Status'), operate:false},
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