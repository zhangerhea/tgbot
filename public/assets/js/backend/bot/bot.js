define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bot/bot/index' + location.search,
                    add_url: 'bot/bot/add',
                    edit_url: 'bot/bot/edit',
                    del_url: 'bot/bot/del',
                    multi_url: 'bot/bot/multi',
                    import_url: 'bot/bot/import',
                    table: 'bot',
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
                        {
                            field: 'bot_name',
                            title: __('Bot_name'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'token',
                            title: __('Token'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',

                        },

                        {
                            field: 'status',
                            title: __('status'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Controller.api.formatter.status
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
                            field: 'buttons',
                            title: __('WebHook'),
                            operate: false,
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.buttons,
                            buttons: [
                                {
                                    name: 'ajax',
                                    title: __('更新'),
                                    text: __('更新'),
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    icon: 'fa fa-magic',

                                    url: 'bot/bot/getMe',
                                    success: function (data, ret) {
                                        Layer.alert(ret.msg + ",返回数据：" + JSON.stringify(data));
                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: __('测试消息'),
                                    text: '测试消息',
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-leaf',
                                    url: function (row) {
                                        return 'https://api.telegram.org/bot' + row.token + '/getUpdates';
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: __('删除WebHook '),
                                    text: '删除WebHook ',
                                    classname: 'btn btn-xs btn-danger btn-dialog',
                                    icon: 'fa fa-trash',
                                    confirm: '确认删除WebHook？',
                                    url: function (row) {
                                        return 'https://api.telegram.org/bot' + row.token + '/deleteWebhook';
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: __('WebHookInfo'),
                                    text: 'WebHookInfo',
                                    classname: 'btn btn-xs btn-info btn-magic btn-dialog',
                                    icon: 'fa fa-info',
                                    url: function (row) {
                                        let _url = encodeURIComponent(Config.api_url + '/api/hook/do?id=' + row.id);
                                        return 'https://api.telegram.org/bot' + row.token + '/getWebhookInfo?url=' + _url;
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: __('点击前请先打开VPN,确保域名为Https开头（有SLL证书）'),
                                    text: '注册webhook',
                                    classname: 'btn btn-xs btn-success btn-magic btn-dialog',
                                    icon: 'fa fa-plus',
                                    url: function (row) {
                                        let _url = encodeURIComponent(Config.api_url + '/api/hook/do?id=' + row.id);
                                        return 'https://api.telegram.org/bot' + row.token + '/setWebhook?url=' + _url;
                                    }
                                }
                            ]
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    title: __('消息按钮'),
                                    text: function (row){

                                        if(row.buttons){
                                            return '按钮 ✔'
                                        }else{
                                            return '按钮'
                                        }
                                    },
                                    classname: 'btn  btn-xs btn-primary   btn-dialog',
                                    icon: 'fa fa-share-square-o',
                                    extend: 'data-area=\'["500px", "480px"]\'',
                                    url: 'bot/bot/buttons'
                                },
                                {
                                    name: 'detail',
                                    title: __('游戏'),
                                    text: '游戏',
                                    classname: 'btn  btn-xs btn-primary   btn-dialog',
                                    icon: 'fa fa-gamepad',
                                    extend: 'data-area=\'["600px", "400px"]\'',
                                    url: 'bot/bot/games'
                                }

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

        buttons: function () {

            let row = 0;
            let buttons_json = $('#buttons_json').val();
            if (buttons_json) {
                buttons_json = $.parseJSON(buttons_json);
            }else{
                buttons_json = [];
            }
            buttons_json = {list: buttons_json};

            let html = Template('edit_tmpl', buttons_json);
            $('#button_body').append(html);
            //添加按钮
            $(document).on("click", ".btn-add", function () {
                let btn_div = $(this).parent().parent().find('div');
                let len = btn_div.find('button').length
                if (len >= 4) {
                    layer.alert('不能超过四个');
                    return;
                }
                btn_div.append(Template('button_tmpl', {row: row, index: len}));

            });
            //删除行
            $(document).on("click", ".btn-remove", function () {
                $(this).parent().parent().remove();
            });
            //添加行
            $(document).on("click", ".btn-append", function () {
                row--;
                let len = $('#button_body').children('tr').length+1;
                if(len >= 10) {
                    layer.alert('不能超过10行');
                }
                $('#button_body').append(Template('row_tmpl', {row: row, index: 0}));

            });

            //按钮设置
            $(document).on("click", ".btn-setting", function () {
                let that = $(this);
                let text = that.parent().children('input').eq(0);
                let url = that.parent().children('input').eq(1);
                let callback_data = that.parent().children('input').eq(2);
                let web_app = that.parent().children('input').eq(3);

                //默认值
                $('#btn_text').val(text.val());
                $('#btn_url').val(url.val());
                $('#btn_callback_data').val(callback_data.val());
                $('#btn_web_app').val(web_app.val());
                layer.open({
                    type: 1,
                    title: '按钮设置',
                    skin: 'layui-layer-fast',
                    area: ['400px', '360px'], //设置宽高
                    content: $("#setting_tmpl"),
                    btn: ['确定', '取消'],
                    btn1: function (index, layero) {
                        // 按钮1的逻辑
                        let btn = {};
                        btn.text = $('#btn_text').val();
                        btn.url = $('#btn_url').val();
                        btn.callback_data = $('#btn_callback_data').val();
                        btn.web_app = $('#btn_web_app').val();

                        //回填
                        that.text(btn.text);
                        text.val(btn.text)
                        url.val(btn.url)
                        callback_data.val(btn.callback_data)
                        web_app.val(btn.web_app)

                        layer.close(index);
                    },
                    btn2: function (index, layero) {
                        // 按钮2的逻辑
                    },
                    cancel: function () {
                        // 右上角关闭事件的逻辑
                    }
                });

            })

            Controller.api.bindevent();
        },
        games: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                status: function (value, row, index) {
                    let txt = '开启';
                    if (row.status == 'normal') {
                        txt = '关闭';
                    }
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" href="javascript:;" data-url="bot/Bot/changeStatus" data-confirm="确认' + txt + '状态？" data-params="" data-id="' + row.id + '"><i class="fa ' + (row.status != 'normal' ? 'fa-toggle-on fa-flip-horizontal text-gray' : 'fa-toggle-on') + ' fa-2x"></i></a>';

                }
            }

        }
    };
    return Controller;
});
