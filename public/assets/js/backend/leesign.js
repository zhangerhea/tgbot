define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'leesign/index',
                    add_url: 'leesign/add',
                    edit_url: 'leesign/edit',
                    del_url: 'leesign/del',
                    multi_url: 'leesign/multi',
                    table: 'lee_sign',
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
                        {field: 'uid', title: __('Uid'), formatter: Table.api.formatter.search},
                        {field: 'sign_ip', title: __('Sign_ip'), formatter: Table.api.formatter.search},
                        {field: 'sign_time', title: __('Sign_time'), operate: 'RANGE', addclass: 'datetimerange'},
                        {field: 'sign_reward', sortable: true, title: __('Sign_reward')},
                        {field: 'sign_extra_reward', sortable: true, title: __('Sign_extra_reward')},
                        {field: 'max_sign', sortable: true, title: __('Max_sign')},
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