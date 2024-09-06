define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'leescore/linkcategory/index' + location.search,
                    add_url: 'leescore/linkcategory/add',
                    edit_url: 'leescore/linkcategory/edit',
                    del_url: 'leescore/linkcategory/del',
                    multi_url: 'leescore/linkcategory/multi',
                    import_url: 'leescore/linkcategory/import',
                    table: 'leescore_link_category',
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
                        {field: 'switch', title: __('Switch'), operate: 'LIKE', table: table, formatter: Table.api.formatter.toggle},
                        {field: 'type', title: __('Type'), searchList: {"1":__('内置文章'),"2":__('友情链接'), "3": "其它碎片" }, formatter: Table.api.formatter.normal},
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