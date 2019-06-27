/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

$(document).ready(function () {

    // MetsiMenu
    $('#sideBarLeft').metisMenu();

    $('.navbar-minimalize').click(function () {
        SmoothlyMenu();
    });

    $('.right-sidebar-toggle').click(function () {
        $('#right-sidebar').toggleClass('sidebar-open');
    });

    $(window).scroll(function () {
        if ($(window).scrollTop() > 0) {
            if ($(window).scrollTop() < 60) {
                $('#right-sidebar').css('top', 60 - $(window).scrollTop());
            } else {
                $('#right-sidebar').css('top', 0);
            }
        } else {
            $('#right-sidebar').css('top', '');
        }
    });

    $('.sidebar-container').slimScroll({
        height: '100%',
        railOpacity: 0.4,
        wheelStep: 10
    });

    $('#collapseCache').on('click', 'a', function(e) {
        e.preventDefault();
        var type = $(this).data('do');
        $.get("/admin/ajax/system/?action=clearCache&do=" + type)
            .success(function() { location.reload(); })
            .error(function() { alert("Ошибка выполнения"); })
    });

    $('#cronRun').on('click', 'a', function(){
        $.get("/admin/ajax/system/?action=cronRun")
            .success(function() { alert("Успешное выполнение"); })
            .error(function() { alert("Ошибка выполнения"); })
    });

    Pyshnov.formatNumber('.price-mask');

    if($("div").is(".datepicker")) {
        $('.datepicker').datetimepicker({
            locale: "ru",
            format: "YYYY-MM-DD HH:mm:ss",
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-angle-up",
                down: "fa fa-angle-down"
            }
        });
    }

    $('[data-toggle="tooltip"]').tooltip();

    $('.item-detail-show').on('click', function() {
        $(this).children('.fa').toggleClass('fa-rotate-90');
        $(this).parents('.item').find('.item-detail').slideToggle('slow');
    });

    $(".inputfile").change(function(){
        var filename = $(this).val().replace(/.*\\/, "");
        $(this).siblings('label').children('span').text(filename);
    });

});

function SmoothlyMenu() {
    var body = $("body");
    var width = $(window).width();

    if (width >= 992) {
        $('.metismenu li').hide();
        // For smoothly turn on menu
        setTimeout(
            function () {
                $('.metismenu li').fadeIn(400);
            }, 100);
        body.toggleClass("mini-navbar").removeClass("navbar-hidden");
    } else if (width < 769) {
        body.toggleClass("mini-navbar").removeClass("navbar-hidden");
    } else {
        body.toggleClass("navbar-hidden")
    }
}