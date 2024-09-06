define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vip/record/index' + location.search,
                    add_url: 'vip/record/add',
                    edit_url: 'vip/record/edit',
                    del_url: 'vip/record/del',
                    multi_url: 'vip/record/multi',
                    import_url: 'vip/record/import',
                    table: 'vip_record',
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
                        {field: 'user_id', title: __('User_id'), formatter: Table.api.formatter.search},
                        {field: 'user.nickname', title: __('Nickname'), formatter: Table.api.formatter.search},
                        {field: 'vip_id', title: __('Vip_id'), formatter: Table.api.formatter.search},
                        {field: 'vip.level', title: __('Vip_level'), formatter: Table.api.formatter.search},
                        {field: 'vip.name', title: __('Vip_name'), formatter: Table.api.formatter.label, custom: Config.customColor},
                        {field: 'level', title: __('Level'), formatter: Table.api.formatter.search},
                        {field: 'days', title: __('Days'), formatter: Table.api.formatter.search},
                        {field: 'amount', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'expiretime', title: __('Expiretime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"created": __('Status created'), "active": __('Status active'), "expired": __('Status expired'), "finished": __('Status finished'), "locked": __('Status locked'), "canceled": __('Status canceled')}, formatter: Table.api.formatter.status},
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
