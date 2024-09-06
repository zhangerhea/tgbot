define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'leescore/leescoreorder/index',
                    add_url: 'leescore/leescoreorder/add',
                    // edit_url: 'leescoreorder/edit',
                    del_url: 'leescore/leescoreorder/del',
                    faild_url: 'leescore/leescoreorder/faild',
                    send_url: 'leescore/leescoreorder/send',
                    multi_url: 'leescore/leescoreorder/multi',
                    table: 'leescore/leescoreorder',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showToggle:true,
                detailView: true,//父子表
                columns: [
                    [
                        {checkbox: true},
                        {field: 'order_id', title: __('order id'), operate: 'LIKE'},
                        {field: 'uid', title: __('Uid'), visible: false, operate:false},
                        {field: 'user.username', title: __('username'), operate: false},
                        {field: 'money_total', title: __('Money total'), operate:'BETWEEN',
                            cellStyle: function (value,index,row){
                                return {
                                    css: {
                                        "color": "red",
                                    }
                                };
                            },
                        },
                        {field: 'score_total', title: __('Score total'), operate: 'BETWEEN',
                        cellStyle: function (value,index,row){
                            return {
                                css: {
                                    "color": "orange",
                                }
                            };
                        }},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"-2": __('已驳回'), "-1":__("已关闭"),"0":__('未付款'),"1":__('已付款'),"2":__('Status 2'),"3":__('Status 3'),"4":__('Status 4'),"5":__('Status 5')}},
                        {field: 'status_text', title: __('Status'), operate:false},
                        {field: 'paytime', title: __('Paytime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, 
                            buttons: [
                                    {name: 'send', text: __('view'), icon: 'fa fa-eye', classname: 'btn btn-xs btn-warning btn-dialog send-box', url: $.fn.bootstrapTable.defaults.extend.send_url},
                            ],  
                            events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                //注册加载子表的事件。
                onExpandRow: function(index, row, $detail) {
                    var html = "";
                    var tScore = (row.trade_score != null) ? row.trade_score : '-';
                    var tMoney = (row.trade_money != null) ? row.trade_money : '-';
                    var truename = (row.address_info != null) ? row.address_info.truename : '-';
                    var mobile = (row.address_info != null) ? row.address_info.mobile : '-';
                    var address = (row.address_info != null) ? row.address_info.address : '-';
                    var region = (row.address_info != null) ? row.address_info.region : '-';
                    html += "<div class=\"col-xs-8\"><ul style='list-style:none; padding:0px; margin:0px;'>";
                    $.ajax({
                        type: "post",
                        url: "leescore/leescoreorder/getOrderGoods", //子表请求的地址
                        data: {id:row.id}, //我这里是点击父表后，传递父表列id给后台查询子表数据
                        dataType: 'json',
                        //async: false, //同步请求
                        success: function(data) {
                            //遍历子表数据
                            $.each(data, function(n, value) {
                                var goodsURL = "/addons/leescore/goods/details?gid=" + value.goods_id;
                                html += '<li style="height:70px; padding:5px 0;"><div class="col-xs-2"><a target="_blank" href="'+ goodsURL +'"><img src="'+ value.goods_thumb +'" style="height:60px; width:80px;"/>'
                                     +'</a></div>'
                                     +'<div class="col-xs-4"><a target="_blank" href="'+ goodsURL +'">'
                                     +value.goods_name
                                     +'</a></div>'
                                     + '<div class="col-xs-2">'
                                     + __('Score') + ": "
                                     +value.score
                                     +'</div>'
                                     + '<div class="col-xs-2">'
                                     + __('Money') + ": "
                                     +value.money
                                     +'</div>'
                                     + '<div class="col-xs-2">*'
                                     +value.buy_num
                                     +'</div></li>';
                            });
                            html += '</ul></div>';
                            html += '<div class="col-xs-4">'
                                 +'<div class="col-xs-12">'
                                 + __('trade score') 
                                 +': '
                                 + tScore
                                 +'</div>'
                                 +'<div class="col-xs-12">'
                                 + __('trade money') 
                                 +': '
                                 + tMoney
                                 +'</div><br><br><br>'
                                 +'<div class="col-xs-12">'
                                 + __('addressee') 
                                 +': '
                                 + truename
                                 +'</div>'
                                 +'<div class="col-xs-12">'
                                 + __('mobile') 
                                 +': '
                                 + mobile
                                 +'</div><br/><br/><br/>'
                                 +'<div class="col-xs-12">'
                                 + region + address
                                 +'</div>'
                                 +'</div>';
                            $detail.html(html); // 关键地方
                        }
                    });
                },
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },

        send: function(){
            $("#faild").on('click', function() {
                layer.confirm(__('msg tip'), {
                    title: __('action'),
                    btn: [__("yes"),__("no")] //按钮
                }, function(){
                    $("#send-form").attr("action","leescore/leescoreorder/faild").submit();
                });
            }); 

            $("#send").on('click', function() {
                var sn = $("#c-virtual_sn").val();
                var name = $("#c-virtual_name").val();
                if(sn == '' || name == '')
                {
                    layer.msg(__('sn name tip'));
                    return false;
                }
                $("#send-form").attr("action","leescore/leescoreorder/send").submit();
            });
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