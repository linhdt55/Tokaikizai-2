jQuery(document).ready(function($) {
    $(document).ajaxComplete(function(event, xhr, settings) {
        const counter = xhr.responseJSON ?.wishlists_data ?.counter;
        if (counter <= 1) {
            $(".wl-qty").addClass('is-show');
            $(".wl-qtys").removeClass('is-show');
        } else {
            $(".wl-qtys").addClass('is-show');
            $(".wl-qty").removeClass('is-show');
        }
        if (counter === 0) {
            $(".count_wishlist").addClass("hiddenCount");
        }

    });
});