define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bot/message/index' + location.search,
                    add_url: 'bot/message/add',
                    edit_url: 'bot/message/edit',
                    del_url: 'bot/message/del',
                    multi_url: 'bot/message/multi',
                    import_url: 'bot/message/import',
                    table: 'bot_message',
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
                        {field: 'bot_id', title:'机器人',formatter: Controller.api.formatter.bot,searchList:$.getJSON('bot/Bot/searchlist')},
                        {field: 'text', title: __('Text'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'chat_title', title: __('Chat_title'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'chat_id', title: __('Chat_id'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'send_time', title: __('Send_time'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.datetime},
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
            formatter: {//渲染的方法
                bot: function (value, row, index) {
                    return row.bot.bot_name;
                }
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
