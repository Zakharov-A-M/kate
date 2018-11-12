$( document ).ready(function() {
    var feedbackModal = $('.js-feedback-modal');

    $('.js-feedback-toggle').on('click', function () {
        feedbackModal.addClass('open');
    });

    $('.js-close-modal').on('click', function () {
        feedbackModal.removeClass('open');
    })
});
