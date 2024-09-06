$(function () {

    var dateArray = days; // 已经签到的

    var $dateBox = $("#js-qiandao-list"),
            $currentDate = $(".current-date"),
            $qiandaoBnt = $("#js-just-qiandao"),
            _html = '',
            myDate = new Date();
    $currentDate.text($('#currDate').val());

    var monthFirst = new Date(myDate.getFullYear(), parseInt(myDate.getMonth()), 1).getDay();
    var d = new Date(myDate.getFullYear(), parseInt(myDate.getMonth() + 1), 0);
    var totalDay = d.getDate(); //获取当前月的天数
    var showLogin = function (ret) {
        layer.alert(ret.msg, {
            btn: ['去登录'],
            yes: function () {
                location.href = LOGIN_URL;
            }
        });
        return false;
    };

    for (var i = 0; i < 42; i++) {
        _html += ' <li><div class="qiandao-icon"></div></li>';
    }

    const myList = '';
    //生成日历网格
    $dateBox.html(_html);
    // console.log(MYLIST);
    //生成当月的日历且含已签到
    var $dateLi = $dateBox.find("li");
    
    // MYLIST = JSON.parse(MYLIST);
    // console.log(dateArray);
    var list_obj = document.getElementById('js-qiandao-list');
    
    for (var index = 0; index < totalDay; index++) {
        $dateLi.eq(index + monthFirst).addClass("date" + parseInt(index + 1));
        $dateLi.eq(index + monthFirst).find('div').html(index+1);
        // qiandao-icon
        dateArray.forEach( (v, i) => {
            if(--v == index) {
                // console.log($dateLi.eq(30));
                $dateLi.eq( index + monthFirst ).addClass("qiandao");
            }
        } );
    };

    // for (var i = 0; i < totalDay; i++) {
    //     $dateLi.eq(i + monthFirst).addClass("date" + parseInt(i + 1));
    //     if(MYLIST.length > 0){
    //         for(n=0; n < MYLIST.length; n++){
    //             if (parseInt(i + 1) == MYLIST[n]) {
    //                 $dateLi.eq(i + monthFirst).addClass("qiandao");
    //             }
    //         }
    //     }
    // }

    $(".date" + myDate.getDate() + ":not(.qiandao)").addClass('able-qiandao');

    //签到
    $(document).on('click', '.able-qiandao', function () {
        $(this).addClass('qiandao');
        $qiandaoBnt.trigger("click");
        return;
    });

    //点击签到按钮
    $qiandaoBnt.on("click", function () {
        $.ajax({
            url: SIGN_URL,
            type: 'post',
            dataType: 'json',
            success: function (ret) {
                if (ret.code == 1) {
                    $qiandaoBnt.addClass('actived');
                    $(".qiandao-jiangli-num").html(ret.data.reward);
                    $(".qiandao-active").fadeIn();
                } else if (ret.code == -1) {
                    showLogin(ret);
                } else {
                    layer.alert(ret.msg);
                }
            }, error: function () {
                layer.alert("操作失败请重试");
            }
        });
    }); //签到

    $("#js-qiandao-history").on("click", function () {
        $.ajax({
            url: SIGNINFO_URL,
            type: 'post',
            dataType: 'json',
            success: function (ret) {
                if (ret.code == 1) {
                    $(".count-info h4").eq(0).html(ret.data.lianxu);
                    $(".count-info h4").eq(1).html(ret.data.length);
                    $(".count-info h4").eq(2).html(ret.data.extra_total);

                    var data = ret.data.data;
                    var html = '';
                    if (jQuery.type(data) === 'array') {
                        for (var x = 0; x < data.length; x++)
                        {
                            html = html + '<tr><td>' + data[x]['sign_time'] + '</td><td>' + data[x]['sign_reward'] + '</td><td>' + data[x]['sign_extra_reward'] + '</td></tr>'
                        }

                    } else {
                        html = "<tr><td colspan='3'>" + ret.data.data + "</td></tr>";
                    }
                    $("#sign-list tbody").html(html);
                    $('.qiandao-history-layer').fadeIn();

                } else if (ret.code == -1) {
                    showLogin(ret);
                } else {
                    layer.alert(ret.msg);
                }
            }, error: function () {
                layer.alert("操作失败请重试");
            }
        });

    });

    $(document).on('click', 'a.close-qiandao-layer', function () {
        $('.qiandao-history-layer').fadeOut();
        return;
    });
    $(document).on('click', 'a.tips-close', function () {
        $('.qiandao-active').fadeOut();
        return;
    });

});
