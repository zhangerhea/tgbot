define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'leescore/link/index' + location.search,
                    add_url: 'leescore/link/add',
                    edit_url: 'leescore/link/edit',
                    del_url: 'leescore/link/del',
                    multi_url: 'leescore/link/multi',
                    import_url: 'leescore/link/import',
                    table: 'leescore_link',
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
                        {field: 'label', title: __('链接名'), operate: 'LIKE'},
                        {field: 'leescorelinkcategory.name', title: __('分类名'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'), searchList: {"1":__('内置文章'),"2":__('友情链接'), "3": __('其它碎片')}, formatter: Table.api.formatter.normal},
                        {field: 'uri', title: __('URL'), operate: 'LIKE'},
                        {field: 'target', title: __('打开方式'), searchList: {"_dialog":__('弹窗'),"_self":__('原网页'),"_blank":__('新开窗口')}, formatter: Table.api.formatter.normal},
                        {field: 'leescorelinkcategory.id', title: __('分类ID')},
                        
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