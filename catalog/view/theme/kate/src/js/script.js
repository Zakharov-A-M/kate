$( document ).ready(function() {
    var feedbackModal = $('.js-feedback-modal');

    $('.js-feedback-toggle').on('click', function () {
        feedbackModal.addClass('open');
    });

    $('.js-close-modal').on('click', function () {
        feedbackModal.removeClass('open');
    });

    function currentMenuItem() {
        let location = window.location.href;
        $('.menu .menu__list li.menu__item').each(function () {
            let link = $(this).find('a').attr('href');
            if (location.indexOf(link) !== -1) {
                $(this).find('a').addClass('current');
                $(this).find('a').css('color', '#eb5933');
            }
        });
    }
});
