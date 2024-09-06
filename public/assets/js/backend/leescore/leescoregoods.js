define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

	var Controller = {
		index: function () {
			// 初始化表格参数配置
			Table.api.init({
				extend: {
					index_url: 'leescore/leescoregoods/index',
					add_url: 'leescore/leescoregoods/add',
					edit_url: 'leescore/leescoregoods/edit',
					del_url: 'leescore/leescoregoods/del',
					multi_url: 'leescore/leescoregoods/multi',
					table: 'leescore/leescore_goods',
				}
			});

			var table = $("#table");
			var json = $.getJSON('leescore/leescoregoods/getOptions');
			// 初始化表格
			table.bootstrapTable({
				url: $.fn.bootstrapTable.defaults.extend.index_url,
				pk: 'id',
				sortName: 'weigh',
				columns: [
					[
						{checkbox: true},
						{field: 'category_id', title: __('Category name'), operate: '=', searchList: json,visible: false},
						{field: 'get_leescore_goods.scName', title: __('Category name'),visible: true, operate:false},
						{field: 'paytype', title: __('Paytype'), operate: '=', searchList: {"0":__('score mode'),"1":__('money mode'),"2":__('hunhe mode')},visible: false},
						//{field: 'get_score_goods.scName', title: __('Category name'), searchList: options_val},
						{field: 'thumb', title: __('Thumb'), operate: false, addclass: 'img-responsive', formatter: Table.api.formatter.image},
						{field: 'name', title: __('Name')},
						{field: 'status', title: __('Status'), visible:false, searchList: {"1":__('Status 1'),"2":__('Status 2')}},
						{field: 'status_text', title: __('Status'), operate:false},
						{field: 'stock', title: __('Stock'), operate: 'BETWEEN'},
						{field: 'scoreprice', title: __('Scoreprice'), operate: 'BETWEEN'},
						{field: 'money', title: __('Money'), operate:'BETWEEN'},
						{field: 'usenum', title: __('Usenum'), operate: false},
						{field: 'flag', title: __('Flag'), formatter: Table.api.formatter.flag, searchList: {"index":__('Flag 0'),"hot":__('Flag 1'),"recommend":__('Flag 2'),"new":__('Flag 3')}},
						{field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
					]
				],
				commonSearch: true,
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
                $(document).on("change", "#c-category_id", function () {
                    $("#c-category_id option").removeClass("hide");
                });
                Form.api.bindevent($("form[role=form]"));
            }
		}
	};
	return Controller;
});