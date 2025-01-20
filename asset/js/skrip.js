$(document).ready(function() {
    $('.hover-card').click(function(event) {
        if (!$(event.target).hasClass('toggle-button')) {
            var popupId = '#popup-' + $(this).find('.toggle-button').data('target').replace('#detail-', '');
            $(popupId).show();
        }
    });

    $(document).on('click', '.close-btn', function() {
        $(this).closest('.popup').hide();
    });

    $(window).click(function(event) {
        if ($(event.target).hasClass('popup')) {
            $('.popup').hide();
        }
    });

    $('.toggle-button').click(function(event) {
        event.stopPropagation();
        var target = $(this).data('target');
        $(target).toggle();
    });
});
