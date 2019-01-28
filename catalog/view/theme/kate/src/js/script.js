$( document ).ready(function() {
    let feedbackModal = $('.js-feedback-modal'),
        overlay = $('.overlay'),
        body = $('body');

    $('.js-feedback-toggle').on('click', function (e) {
        e.preventDefault();
        feedbackModal.addClass('open');
        overlay.addClass('open');
    });

    $('body').delegate('.js-close-modal', 'click', function(e) {
        e.preventDefault();
        feedbackModal.removeClass('open');
        overlay.removeClass('open');
        $('.js-send-message-ajax').remove();
    });


    body.keydown(function(e) {
        if (e.keyCode === 27) {
            $('.modal').removeClass('open');
            overlay.removeClass('open');
        }
    });

    overlay.on( 'click', function() {
        $(this).removeClass('open');
        feedbackModal.removeClass('open');
    });

    $('.js-discount-btn').on('click', function () {
        $('.js-discount').addClass('open');
    });

    $('.js-close-modal-discount').on('click', function () {
        $('.js-discount').removeClass('open');
    });

    $('#nav-icon3').click(function(){
        $(this).toggleClass('open');
        $('.js-mobile-menu').toggleClass('open');
    });

    $.mask.definitions['h'] = '[a-zA-ZА-Яа-я-]+';
    $('[name="firstname"]').mask('hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[a-zA-ZА-Яа-я-]+';
    $('[name="lastname"]').mask('hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[0-9()+-]+';
    $('[name="telephone"]').mask('hhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    /**
     * Validate email in form
     *
     * @param emailAddress
     * @returns {boolean}
     */
    function isValidEmailAddress(emailAddress)
    {
        var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
        return pattern.test(emailAddress);
    }

    let location = window.location.href;
    $('.menu li').each(function () {
        let link = $(this).find('a').attr('href');
        if (location.indexOf(link) !== -1) {
            $(this).addClass('current');
        }
    });
    if ($('.menu li.current').length) {
        let menu = $('.menu li.current');
        menu[0].classList.remove('current');
    }

});
