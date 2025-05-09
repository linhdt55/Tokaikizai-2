(function ($) {
    'use strict';

    $(function () {
        var wtier_cta_banner = {
            init: function () {
                this.moveBanner();
                this.initToggleFeatures();
                this.initDraggable();
            },

            moveBanner: function() {
                $('#wtier_product_import_export_pro').appendTo('#side-sortables').addClass('postbox');
            },

            initToggleFeatures: function() {
                const toggleBtn = $('.wtier-cta-toggle');
                const hiddenFeatures = $('.hidden-feature');
                
                // Set initial text
                toggleBtn.text(toggleBtn.data('show-text'));
                
                toggleBtn.on('click', function(e) {
                    e.preventDefault();
                    const $this = $(this);
                    
                    hiddenFeatures.slideToggle(100, function() {
                        // After animation completes, update the text based on visibility
                        if ($(this).is(':visible')) {
                            toggleBtn.text(toggleBtn.data('hide-text'));
                        } else {
                            toggleBtn.text(toggleBtn.data('show-text'));
                        }
                    });
                });
            },

            initDraggable: function() {
                const banner = $('.wtier-cta-banner');
                let originalPosition;
                
                banner.draggable({
                    handle: '.wtier-cta-header',
                    containment: 'window',
                    start: function(event, ui) {
                        originalPosition = ui.position;
                    },
                    stop: function(event, ui) {
                        $(this).animate(originalPosition, {
                            duration: 300,
                            easing: 'swing'
                        });
                    }
                });
            }
        };

        wtier_cta_banner.init();

        // Handle dismiss button for product banner
        $('#wtier_product_import_export_pro .wtier-cta-dismiss').on('click', function(e) {
            e.preventDefault();
            document.cookie = "hide_wtier_cta_banner=true; path=/; max-age=" + (10 * 365 * 24 * 60 * 60) + ";";
            $('#wtier_product_import_export_pro').hide();
        });

        // Handle dismiss button for coupon banner
        $('#wtier_coupon_import_export_pro .wtier-cta-dismiss').on('click', function(e) {
            e.preventDefault();
            document.cookie = "hide_wtier_coupon_cta_banner=true; path=/; max-age=" + (10 * 365 * 24 * 60 * 60) + ";";
            $('#wtier_coupon_import_export_pro').hide();
        });

        // Check if banners should be hidden on page load
        if (document.cookie.indexOf('hide_wtier_cta_banner=true') !== -1) {
            $('#wtier_product_import_export_pro').hide();
        }
        if (document.cookie.indexOf('hide_wtier_coupon_cta_banner=true') !== -1) {
            $('#wtier_coupon_import_export_pro').hide();
        }

        // Hide hidden features by default
        $('.hidden-feature').hide();
    });
})(jQuery);