define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bot/push/index' + location.search,
                    add_url: 'bot/push/add',
                    edit_url: 'bot/push/edit',
                    del_url: 'bot/push/del',
                    multi_url: 'bot/push/multi',
                    import_url: 'bot/push/import',
                    table: 'bot_push',
                }
            });

            var table = $("#table");
            Table.button.edit.extend = 'data-toggle="tooltip" data-area=\'["902px", "600px"]\'';

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {
                            field: 'bot_id',
                            title: __('Bot_id'),
                            formatter: Controller.api.formatter.bot,
                            searchList: $.getJSON('bot/Bot/searchlist')
                        },
                        {
                            field: 'sendtype',
                            title: __('Sendtype'),

                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'text',
                            title: __('Text'),
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            table: table,
                            searchList: {"normal": __('Normal'), "sending": __('Sending'), "finished": __('Finished')},
                            custom: {normal: 'gray', finished: 'success', sending: 'red'},
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'push_count',
                            formatter: Controller.api.formatter.count,
                            title: __('推送次数/总数')
                        },
                        {
                            field: 'read_count',
                            title: __('阅读次数')
                        },
                        {
                            field: 'create_time',
                            title: __('Create_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'push_time',
                            title: __('Push_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,

                            buttons: [
                                {
                                    name: 'ajax',
                                    title: __('立即推送'),
                                    text: __('立即推送'),
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    icon: 'fa fa-send',
                                    confirm: '确认推送消息？',
                                    url: 'bot/Push/send',
                                    refresh: true, //操作完成后是否刷新列表
                                    success: function (data, ret) {
                                        $('.btn-refresh').trigger('click');
                                        //Layer.alert(ret.msg );
                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    },
                                    visible: function (data) {
                                        if (data.status == 'normal') {
                                            return true;
                                        } else {
                                            return false;
                                        }
                                    }
                                },
                            ]

                        }
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
            formatter: {//渲染的方法
                bot: function (value, row, index) {
                    return row.bot.bot_name;
                },
                count: function (value, row, index) {
                    return row.push_count + '/' + row.total_count;
                }

            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
