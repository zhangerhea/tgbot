define(['jquery', 'bootstrap', 'frontend'], function ($, undefined, Frontend) {
    var Controller = {
        viplist: function () {
            $(document).on("click", ".price-list a", function () {
                var form = $(this).closest("form");
                if (!$(this).hasClass("active")) {
                    $(this).closest(".price-list").find("a").removeClass("active");
                    $(this).addClass("active");
                    $("input[name='days']", form).val($(this).data("days"));
                }
                return false;
            });

            $(document).on("click", ".row-paytype label", function () {
                var form = $(this).closest("form");
                $(".row-paytype label", form).removeClass("active");
                $(this).addClass("active");
                $("input[name=paytype]", form).val($(this).data("value"));
            });

            $(document).on("click", "a.price-item", function () {
                var form = $(this).closest("form");
                $(".row-pricelist a", form).removeClass("active");
                $(this).addClass("active");
                $("input[name=days]", form).val($(this).data("days"));
            });
        },
        viplog: function () {

        }
    };
    return Controller;
});
