$(document).ready(function() {
	// 加入购物车
	$(document).on('click','a.add-cart' , function() {
		var id = $(this).attr('data-paramid');
		var uri = $(this).attr('data-url');
		var num = $("input.buynum").val();
		if(num == undefined)
		{
			num = 1;
		}
		$.ajax({
			url: uri,
			type: 'post',
			dataType: 'json',
			data: {id: id, num: num},
			success: function(res)
			{
				if(res.status == 1)
				{
					layer.msg(res.msg, { icon: 1});
					return true;
				}else{
					layer.msg(res.msg, { icon: 5});
					return false;
				}
			}
		});
	});

	// 清空购物车
	$(document).on("click", 'a.clearCart-btn', function(){
		var uri = $(this).attr('data-url');
		$.ajax({
			url: uri,
			type: 'post',
			dataType: 'json',
			success: function(res)
			{
				if(res.status == 1)
				{
					layer.msg(res.msg, { icon: 1});
					$(".cart-ps-list ul").html("<li style='color:#666;'>购物车空空如也~<div class='clearfix'></div></li>");
					return true;
				}else{
					layer.msg(res.msg, { icon: 5});
					return false;
				}
			}
		});
	});

	// 空白处收起购物车
	$(document).on("click", function(event){
		var open = $(".cart-ps-icon").attr('data-on');
		if(open == 'on')
		{
			var cliX = event.clientX;
			var cliY = event.clientY;

			var objX = Number($(".shopping-cart-ps").css("left").replace("px",""))-60;
			var objY = Number($(".shopping-cart-ps").css("top").replace("px",""));
			var objWidth = $(".shopping-cart-ps").width();
			var objHeight = $(".shopping-cart-ps").height();
			var objRight = objX + objWidth;
			var objLeft = objY + objHeight;

			// 点击坐标未达容器所在位置
			if(cliX >= objX && (cliY >= objY && cliY <= objLeft)) {

			}else{
				$(".cart-ps-icon").click();
			}
		}
	});
});