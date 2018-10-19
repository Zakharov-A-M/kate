$(document).ready(function () {
    var $headScroll = $('.header_scroll');
    $headScroll.removeClass('header_mini');
    $headScroll.parent().removeAttr('id');
    $("#goup .header_mini").hide().removeAttr("href");
    var $headMinGoUp = $("#goup .header_mini");
    var headMin = 'header_mini';
    var wid = $(window).width();

    if (wid > 768) {

        if ($(window).scrollTop() >= "44") {
            $headMinGoUp.fadeIn(10);
            $headScroll.addClass(headMin);
            $headScroll.parent().attr('id', 'goup');
        }
    }
    else {
        $headScroll.removeClass(headMin);
        $headScroll.parent().removeAttr('id');
        $headMinGoUp.fadeOut(10);
    }

    $(window).scroll(function () {
        if (wid > 768) {
            if ($(window).scrollTop() <= "44") {
                $headScroll.removeClass(headMin);
                $headScroll.parent().removeAttr('id');
                $headMinGoUp.fadeOut(10);
            } else {
                $headMinGoUp.fadeIn(10);
                $headScroll.addClass(headMin);
                $headScroll.parent().attr('id', 'goup');
            }
        }
        else {
            $headScroll.removeClass(headMin);
            $headScroll.parent().removeAttr('id');
            $headMinGoUp.fadeOut(10);
        }
    });
    return false;
});