$(document).ready(function(){
    $('.js-ceilings').slick({
        prevArrow: $('.js-slider-prev'),
        nextArrow: $('.js-slider-next'),
    });

    $('.js-wallpaper').slick({
        prevArrow: $('.js-slider-prev'),
        nextArrow: $('.js-slider-next'),
    });

    $('.js-stopper').slick({
        prevArrow: $('.js-slider-prev'),
        nextArrow: $('.js-slider-next'),
        dots: true,
        appendDots: $('.slider__dots'),
    });
});
