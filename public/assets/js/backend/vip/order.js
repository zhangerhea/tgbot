define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vip/order/index' + location.search,
                    add_url: 'vip/order/add',
                    edit_url: 'vip/order/edit',
                    del_url: 'vip/order/del',
                    multi_url: 'vip/order/multi',
                    import_url: 'vip/order/import',
                    table: 'vip_order',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 2,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'orderid', title: __('Orderid'), operate: 'LIKE'},
                        {field: 'user_id', title: __('User_id'), formatter: Table.api.formatter.search},
                        {field: 'user.nickname', title: __('Nickname'), formatter: Table.api.formatter.search},
                        {field: 'vip_id', title: __('Vip_id'), formatter: Table.api.formatter.search},
                        {field: 'vip.level', title: __('Vip_level'), formatter: Table.api.formatter.search},
                        {field: 'vip.name', title: __('Vip_name'), formatter: Table.api.formatter.label, custom: Config.customColor},
                        {field: 'record_id', title: __('Record_id')},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'amount', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'payamount', title: __('Payamount'), operate: 'BETWEEN'},
                        {field: 'paytype', title: __('Paytype'), operate: 'LIKE', formatter: Table.api.formatter.search},
                        {field: 'paytime', title: __('Paytime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'method', title: __('Method'), operate: 'LIKE'},
                        {field: 'ip', title: __('Ip'), operate: 'LIKE', visible: false, formatter: Table.api.formatter.search},
                        {field: 'memo', title: __('Memo'), operate: 'LIKE', visible: false},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"created": __('Status created'), "paid": __('Status paid'), "expired": __('Status expired')}, formatter: Table.api.formatter.status},
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
