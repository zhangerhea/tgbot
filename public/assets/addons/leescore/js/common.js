$(function () {
    $('.carousel').carousel({
        interval: 5000 //changes the speed
    });
    $(".btn-experience").on("click", function () {
        location.href = "/addons/epay/index/experience?amount=" + $("input[name=amount]").val() + "&type=" + $(this).data("type") + "&method=" + $("#method").val();
    });

});