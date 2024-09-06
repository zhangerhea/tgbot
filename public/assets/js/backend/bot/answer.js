define(['jquery', 'bootstrap', 'backend', 'table', 'form','template'], function ($, undefined, Backend, Table, Form,Template) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bot/answer/index' + location.search,
                    add_url: 'bot/answer/add',
                    edit_url: 'bot/answer/edit',
                    del_url: 'bot/answer/del',
                    multi_url: 'bot/answer/multi',
                    import_url: 'bot/answer/import',
                    table: 'bot_answer',
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

                        {field: 'keywords', title: __('Keywords'), operate: 'LIKE'},
                        {field: 'sendtype', title: __('Sendtype'), operate: 'LIKE'},
                        {field: 'reqeust_type', title: __('Reqeust_type'), searchList: {"common":__('Common'),"command":__('Command')}, formatter: Table.api.formatter.normal},
                        {field: 'text', title: __('Text'),class: 'autocontent',formatter: Table.api.formatter.content},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    title: __('按钮'),
                                    text:  function (row){

                                        if(row.buttons){
                                            return '按钮 ✔'
                                        }else{
                                            return '按钮'
                                        }
                                    },
                                    classname: 'btn  btn-xs btn-primary   btn-dialog',
                                    icon: 'fa fa-share-square-o',
                                    extend: 'data-area=\'["600px", "480px"]\'',
                                    url: 'bot/answer/buttons'
                                },
                                {
                                    name: 'detail',
                                    title: __('键盘'),
                                    text:  function (row){

                                        if(row.keyboard){
                                            return '键盘 ✔'
                                        }else{
                                            return '键盘'
                                        }
                                    },
                                    classname: 'btn  btn-xs btn-primary   btn-dialog',
                                    icon: 'fa fa-keyboard-o',
                                    extend: 'data-area=\'["600px", "500px"]\'',
                                    url: 'bot/answer/keyboard'
                                }

                            ]}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindtype();
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindtype();

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
        keyboard: function () {

            let row = 0;
            let keyboard_json = $('#keyboard_json').val();
            keyboard_json = $.parseJSON(keyboard_json);
            keyboard_json = {list: keyboard_json};

            let html = Template('edit_tmpl', keyboard_json);
            $('#keyboard_body').append(html);
            //添加按钮
            $(document).on("click", ".btn-add", function () {
                let btn_div = $(this).parent().parent().find('div');
                let len = btn_div.find('button').length
                if (len >= 4) {
                    layer.alert('不能超过四个');
                    return;
                }
                btn_div.append(Template('keyboard_tmpl', {row: row, index: len}));

            });
            //删除行
            $(document).on("click", ".btn-remove", function () {
                $(this).parent().parent().remove();
            });
            //添加行
            $(document).on("click", ".btn-append", function () {
                row--;
                $('#keyboard_body').append(Template('row_tmpl', {row: row, index: 0}));

            });

            //按钮设置
            $(document).on("click", ".btn-setting", function () {
                let that = $(this);
                let text = that.parent().children('input').eq(0);
                let web_app = that.parent().children('input').eq(1);


                //默认值
                $('#btn_text').val(text.val());
                $('#btn_web_app').val(web_app.val());

                layer.open({
                    type: 1,
                    title: '键盘设置',
                    skin: 'layui-layer-fast',
                    area: ['400px', '380px'], //设置宽高
                    content: $("#setting_tmpl"),
                    btn: ['确定', '取消'],
                    btn1: function (index, layero) {
                        // 按钮1的逻辑
                        let btn = {};
                        btn.text = $('#btn_text').val();
                        btn.web_app = $('#btn_web_app').val();


                        //回填
                        that.text(btn.text);
                        text.val(btn.text)
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
        api: {
            formatter: {//渲染的方法
                bot: function (value, row, index) {
                    return row.bot.bot_name;
                }
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            bindtype:function (){
                $('#c-reqeust_type').change(function (){
                    $('#demo_img').attr('src','/assets/img/'+this.value+'.jpg');
                });
            }

        }
    };
    return Controller;
});
