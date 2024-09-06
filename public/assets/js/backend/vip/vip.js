define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vip/vip/index' + location.search,
                    add_url: 'vip/vip/add',
                    edit_url: 'vip/vip/edit',
                    del_url: 'vip/vip/del',
                    multi_url: 'vip/vip/multi',
                    import_url: 'vip/vip/import',
                    table: 'vip',
                },

            });
            Table.button.edit.extend = Table.button.edit.extend + ` data-area='["90%","90%"]'`

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'level',
                sortOrder: 'asc',
                columns: [
                    [{
                        checkbox: true
                    },
                        {
                            field: 'id',
                            title: __('Id')
                        },
                        {
                            field: 'level',
                            title: __('Level')
                        },
                        // {field: 'group_id', title: __('Group_id')},
                        {
                            field: 'name',
                            title: __('Name'),
                            operate: 'LIKE',
                            formatter: Table.api.formatter.label, custom: Config.customColor
                        },
                        {
                            field: 'label',
                            title: __('Label'),
                            operate: 'LIKE'
                        },
                        {
                            field: 'intro',
                            title: __('Intro'),
                            operate: 'LIKE',
                            formatter: Table.api.formatter.content,
                            table: table,
                            class: 'autocontent'
                        },
                        {
                            field: 'image',
                            title: __('Image'),
                            operate: false,
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        // {field: 'price', title: __('Price'), operate: 'BETWEEN'},
                        {
                            field: 'sales',
                            title: __('Sales')
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {
                                "normal": __('Normal'),
                                "hidden": __('Hidden'),
                                "pulloff": __('Pulloff')
                            },
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        diyFieldList(type) {
            var container = $("#pricetable");
            var refresh = function () {
                setTimeout(function () {
                    if ($("select option[value=1]:selected", container).length === 0) {
                        $("select option[value=1]:first", container).prop("selected", true);
                    }
                }, 1);
            };
            container.on('change', "select", function () {
                if ($(this).val() == 1) {
                    $(this).closest(".fieldlist").find("select").not(this).find("option[value=0]").prop("selected", true);
                    $("option[value=1]", this).prop("selected", true);
                } else {
                    refresh();
                }
            });
            container.on("click", ".btn-remove", function () {
                refresh();
            });
            container.on("fa.event.appendfieldlist", ".btn-append", function (e, obj) {
                refresh();
            });
            $("#righttable").on("fa.event.appendfieldlist", ".btn-append", function (e, obj) {
                Form.events.plupload(obj);
                Form.events.faselect(obj);
            });
        },
        add: function () {
            this.diyFieldList('add');
            Controller.api.bindevent();
        },
        edit: function () {
            this.diyFieldList('edit');
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
