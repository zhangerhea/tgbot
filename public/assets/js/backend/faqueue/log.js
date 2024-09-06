define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'faqueue/log/index',
                    del_url: 'faqueue/log/del',
                    table: 'faqueue_log',
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
                        {field: 'queue', title: __('Queue')},
                        {field: 'job', title: __('Job'),operate:'LIKE %...%'},
                        {field: 'data', title: __('Data'),formatter:function(value,row){
                            if(row.job.indexOf('EmailJob') > -1 || row.job.indexOf('SmsJob') > -1){
                                var div = document.createElement('div');
                                div.innerHTML = value;
                                value = div.innerText || div.textContent;
                                var data = JSON.parse(value);
                                var html = [];
                                for(k in data){
                                    html.push(k+'：'+data[k].substring(0,20)+'<br>');
                                }
                                return html.join(' ');
                            }else{
                                return value;
                            }
                        },operate:'LIKE %...%'},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});