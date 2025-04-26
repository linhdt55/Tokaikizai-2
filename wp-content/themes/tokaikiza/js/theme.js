jQuery(document).ready(function($) {

    trigger_calendar();
    banner_slider();
    $('.home .section_breadcrumb').remove();
    $('body').on('click', '.wp-megamenu b', function() {
        $(this).parent().parent().toggleClass('parentactive')
        $(this).parent().parent().parent().toggleClass('ulparentactive')
    })
    $('body').on('click', '.wpmm-item-title', function() {
        $(this).closest('.wpmm-col').toggleClass('active')
        $(this).parent().toggleClass('active')
        $(this).parent().parent().parent().parent().toggleClass('ulparentactive2')
    })
    $('body').on('click', '.wpmm_mobile_menu_btn', function() {
        $('body').toggleClass('openmenu');
    })
    $('.slider_brand .elementor-widget-wrap').addClass('owl-carousel owl-theme')
    $('.slider_brand .elementor-widget-wrap').owlCarousel({
        loop: true,
        margin: 16,
        responsiveClass: true,
        items: 2,
        stagePadding: 20,
        responsive: {
            768: {
                items: 3,
                margin: 24,
                stagePadding: 0
            },
            1360: {
                items: 5,
                loop: false,
                margin: 24,
                stagePadding: 0
            }
        }
    })

    function banner_slider() {
        var sync1 = $(".banner_slider");
        var sync2 = $(".navigation-thumbs");
        var slidesPerPage = 6;
        var syncedSecondary = true;

        sync1.owlCarousel({
            center: true,
            items: 1,
            margin: 16,
            autoWidth: false,
            video: true,
            loop: true,
            autoplay: false,
            autoplayTimeout: 6000,
            autoplayHoverPause: false,
            stagePadding: 0,
            dots: true,
            responsive: {
                1200: {
                    stagePadding: 50,
                    autoWidth: true,
                }
            }
        }).on('changed.owl.carousel', syncPosition);

        sync2
            .on('initialized.owl.carousel', function() {
                sync2.find(".owl-item").eq(0).addClass("synced");
            })
            .owlCarousel({
                items: slidesPerPage,
                dots: true,
                nav: true,
                margin: 16,
                smartSpeed: 200,
                slideSpeed: 500,
                slideBy: slidesPerPage, //alternatively you can slide by 1, this way the active slide will stick to the first item in the second carousel
                responsiveRefreshRate: 100
            }).on('changed.owl.carousel', syncPosition2);

        function syncPosition(el) {
            //if you set loop to false, you have to restore this next line
            //var current = el.item.index;

            //if you disable loop you have to comment this block
            var count = el.item.count - 1;
            var current = Math.round(el.item.index - (el.item.count / 2) - .5);

            if (current < 0) {
                current = count;
            }
            if (current > count) {
                current = 0;
            }

            //end block

            sync2
                .find(".owl-item")
                .removeClass("synced")
                .eq(current)
                .addClass("synced");
            var onscreen = sync2.find('.owl-item.active').length - 1;
            var start = sync2.find('.owl-item.active').first().index();
            var end = sync2.find('.owl-item.active').last().index();

            if (current > end) {
                sync2.data('owl.carousel').to(current, 100, true);
            }
            if (current < start) {
                sync2.data('owl.carousel').to(current - onscreen, 100, true);
            }
        }

        function syncPosition2(el) {
            if (syncedSecondary) {
                var number = el.item.index;
                sync1.data('owl.carousel').to(number, 100, true);
            }
        }

        sync2.on("click", ".owl-item", function(e) {
            e.preventDefault();
            var number = $(this).index();
            sync1.data('owl.carousel').to(number, 300, true);
        });
    }

    function trigger_calendar() {
        if ($('.next_month').length > 0) {
            var next_month = $('#next_month_calendar').val();
            $('.next_month select option').prop('selected', false);
            $('.next_month option[value="' + next_month + '"]').prop('selected', true);
            setTimeout(function() { $('.next_month select').trigger('change'); }, 0);
        }
    }
    $('body').on('click', '.view-layout a', function() {
        var _class = $(this).data('layout');
        $('.view-layout a').removeClass('active');
        $(this).addClass('active');
        if (_class == 'list') {
            $(this).closest('.col-content').removeClass('layout-grid');
        } else {
            $(this).closest('.col-content').removeClass('layout-list');
        }
        $(this).closest('.col-content').addClass('layout-' + _class);
    })
    var icon_cart = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_1132_37865)"><path d="M0.833374 0.833252H4.16671L6.40004 11.9916C6.47624 12.3752 6.68496 12.7199 6.98966 12.9652C7.29436 13.2104 7.67562 13.3407 8.06671 13.3333H16.1667C16.5578 13.3407 16.9391 13.2104 17.2438 12.9652C17.5484 12.7199 17.7572 12.3752 17.8334 11.9916L19.1667 4.99992H5.00004M8.33337 17.4999C8.33337 17.9602 7.96028 18.3333 7.50004 18.3333C7.0398 18.3333 6.66671 17.9602 6.66671 17.4999C6.66671 17.0397 7.0398 16.6666 7.50004 16.6666C7.96028 16.6666 8.33337 17.0397 8.33337 17.4999ZM17.5 17.4999C17.5 17.9602 17.1269 18.3333 16.6667 18.3333C16.2065 18.3333 15.8334 17.9602 15.8334 17.4999C15.8334 17.0397 16.2065 16.6666 16.6667 16.6666C17.1269 16.6666 17.5 17.0397 17.5 17.4999Z" stroke="#2F2F39" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></g><defs><clipPath id="clip0_1132_37865"><rect width="20" height="20" fill="white"/></clipPath></defs></svg>';
    $('.add_to_cart_button').html(icon_cart);
    $('.single_add_to_cart_button ').prepend(icon_cart);
    $('[icon="cart"] .elementor-icon-list-text').text($('#cartcount').val())
    if ($('.wishlist_products_counter_number').length > 0) {
        $('[icon="wishlist"] .elementor-icon-list-text').text($('.wishlist_counter .wishlist_products_counter_number').text())
    }

    function textinstock() {
        $('.product-search-filter-price-heading').addClass('wpf_item_name');
        $('.product-search-filter-terms-heading').addClass('wpf_item_name');
        $('.product-search-filter-extras-heading').addClass('wpf_item_name');
        if ($('.wpf_instock_wrapp').length > 0 && $('#title_istock').length > 0) {
            var _for = $('.wpf_instock_wrapp').find('input').attr('id');
            $('.wpf_instock_wrapp').append('<label for="' +
                _for + '">' + $('#title_istock').val() + '</label>')
        }
    }
    textinstock();
    $('.wpf_item_name').after('<span class="click-toggle"></span>');
    $(".grouped_form").on("submit", function() {
        var haveQty = false;
        $(".cart.grouped_form .colum-quantity").each(function() {
            var qty = $(this).find("select").val();
            if(qty>0){
                haveQty = true;
                return false;
            }
        });
        if(haveQty){
            $('body').append('<div id="temp_load" class="loading"><span class="loading__anim"></span></div>');
        };
    })
    $(window).scroll(function() {
        var height = $('#masthead').height() - 30;
        var sticky = $('#masthead'),
            scroll = $(window).scrollTop();
        if (scroll > 0) {
            sticky.addClass('scrolled');
        } else {
            sticky.removeClass('scrolled');
        }
        if (scroll >= height) {
            sticky.addClass('fixed');
            if ($('section.menu_main').hasClass("open")) {
                $('section.menu_main').removeClass("open").addClass("closed");
            } 
        } else {
            sticky.removeClass('fixed');
            if ($('section.menu_main').hasClass("closed")) {
                $('section.menu_main').removeClass("closed").addClass("open");
            } 
        }
        $('section.menu_main').removeClass("closed");
        var _window = $(window).width();
        header_fixed(_window)
    });
    $('body').on('click', '#menu-button.elementor-element .elementor-icon', function(e) {
        e.preventDefault();
        if ($('section.menu_main').hasClass("open")) {
            $('section.menu_main').removeClass("open").addClass("closed");
        } else {
            // if other menus are open remove open class and add closed
            $('section.menu_main').removeClass("open").addClass("closed");
            $('section.menu_main').removeClass("closed").addClass("open");
        }
    })
    $('body').on('click', '.click-toggle', function() {
        $(this).next().slideToggle();
        $(this).parent().toggleClass('active');
    })
    $('.wpf_links.wpf_hierachy').find('.wpf_submenu').before('<span class="click-toggle"><svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.5 11L6.5 6L1.5 1" stroke="#2F2F39" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></svg></span>')
    $('.widget_product_categories .children').before('<span class="click-toggle"><svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.5 11L6.5 6L1.5 1" stroke="#2F2F39" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></svg></span>')
    if ($('.box_search .hfe-search-submit').length > 0 && $('#textsearch').length > 0) {
        $('.box_search .hfe-search-submit').text($('#textsearch').val());
    }
    $('body').on('click', '.opento', function() {
        var toggle = $(this).attr('data-toggle');
        $(toggle).toggle();
        $(this).toggleClass('active');
    })
    $('body').on('click', '.opensidebar', function() {
        var toggle = $(this).attr('data-toggle');
        $(toggle).toggleClass('openlefftoright');
        $(this).toggleClass('active');
    });
    /* button plus and minius product */
    $('body').on('click', 'button.plus, button.minus', function() {
        // Get current quantity values
        var qty = $(this).closest('.quantity').find('.qty');
        if (qty.val() == '') {
            var val = 0;
        } else {
            var val = parseFloat(qty.val());
        }
        if (parseFloat(qty.attr('max')) == '') {
            var max = 9999;
        } else {
            var max = parseFloat(qty.attr('max'));
        }
        var min = parseFloat(qty.attr('min'));
        var step = parseFloat(qty.attr('step'));
        // Change the value if plus or minus
        if ($(this).is('.plus')) {
            if (max && (max <= val)) {
                qty.val(max);
            } else {
                qty.val(val + step);
            }
        } else {
            if (min && (min >= val)) {
                qty.val(min);
            } else if (val > 1) {
                qty.val(val - step);
            }
        }
        if (qty.val() > 1) {
            $(this).closest('.quantity').find('button.minus').addClass('active')
        } else {
            $(this).closest('.quantity').find('button.minus').removeClass('active')
        }
        if ($('.countqty').length > 0) {
            $('.countqty').text(qty.val())
        }
        qty.trigger('change');
        if ($('.update_cart').length > 0) {
            $('.update_cart').trigger('click');
        }
    });
    if ($('.grouped_form').find('.qty').val() == 0) {
        $('.qty').val('1');
    }
    $('body').on('change', '.quantity .qty', function() {
        var _val = parseInt($(this).val());
        if ($('.buy_now_button').length > 0) {
            if (_val < 1) {
                $(this).val('1');
            }
        }
        if (_val == 0) {
            $(this).closest('.quantity').addClass('disable')
        } else {
            $(this).closest('.quantity').removeClass('disable')
        }
        if (_val > 1) {
            $(this).closest('.quantity').find('button.minus').addClass('active')
        } else {
            $(this).closest('.quantity').find('button.minus').removeClass('active')
        }
        if ($('.update_cart').length > 0) {
            $('.update_cart').trigger('click');
        }
    })
    $('body').on('focus', '#coupon_code', function() {
            $(this).parent().find('[type="submit"]').removeAttr('disabled')
        })
        /* buy now button */
    $('body').on('click', '.buy_now_button', function(e) {
        e.preventDefault();
        var thisParent = $(this).parents('form.cart');
        if ($('.single_add_to_cart_button', thisParent).hasClass('disabled')) {
            $('.single_add_to_cart_button', thisParent).trigger('click');
            return false;
        }
        thisParent.addClass('toki-quickbuy');
        $('.is_buy_now', thisParent).val('1');
        $('.single_add_to_cart_button', thisParent).trigger('click');
        window.location.href = $('.chekout_url').val();
    });
    $('body').on('click', '.button-trigger', function() {
        var trigger = $(this).data('trigger');
        $(trigger).trigger('click');
    })
    if ($('.countitemcart').length > 0 && $('.entry-header').length > 0) {
        $(".countitemcart").appendTo(".entry-header");
    }

    if ($('.tinv-next').length > 0) {
        $('.tinv-next').prev('span').addClass('pagination-number');
    }

    if ($('.tinv-prev').length > 0) {
        $('.tinv-prev').next('span').addClass('pagination-number');
    }

    if ($('.custom-header-wishlist').length > 0 && $('.entry-header').length > 0) {
        $(".entry-header").addClass('custom-entry-wishlist');
    }
    if ($('.count_wishlist').length > 0 && $('.entry-header').length > 0) {
        const counter = $(".wishlist_products_counter_number").text();
        if (counter <= 1) {
            $(".wl-qty").addClass('is-show');
        } else {
            $(".wl-qtys").addClass('is-show')
        }
        $(".count_wishlist").appendTo(".entry-header");
    }

    /* modal */
    $('body').on('click', '.clickpopup', function(e) {
        e.preventDefault();
        var toggle = $(this).data('toggle');
        if ($(this).data('target') == undefined) {
            var target = $(this).find('a').attr('href');
        } else {
            var target = $(this).data('target');
        }
        $(target).show();
        $(target).addClass('show');
        $('body').append('<div class="modal-backdrop show"></div>');
    })
    $('body').on('click', '.close,.modal-backdrop', function() {
        $('.modal').hide();
        $('.modal').removeClass('show');
        $('.modal-backdrop').remove();
    })
    $('body').on('click', '.modal', function(event) {
        if ($(event.target).closest(".modal-dialog").length < 1) {
            $('.modal').fadeOut();
            $('.modal').removeClass('show');
            $('.modal-backdrop').remove();
        }
    });
    $('body').on('click', '.changebrand', function() {
        var brands = $(this).data('brands');
        $('#changebrands_oem').val(brands);
        $('.title-form').html($(this).text());
    })
    $('.hfe-search-button-wrapper').append('<input type="hidden"  name="post_type" value="product">')
    if ($('.hfe-search-button-wrapper').length > 0 && $('#exclude_cat').length > 0) {
        $('.hfe-search-button-wrapper').append('<input type="hidden"  name="exclude_type" value="' + $('#exclude_cat').val() + '">')
    }

    function header_fixed(_window) {
        var header = $('#masthead').height();
        if (_window < 1025) {
            $('#masthead + *').css('padding-top', header + 'px');
        }
        if (_window > 1025) {
            $('#masthead + *').removeAttr('style');
        }
    }
    var _window = $(window).width();
    header_fixed(_window);
    $(window).resize(function() {

        var _window = $(window).width();
        header_fixed(_window);
    });
    if ($('#link-brecumber-before').length > 0) {
        $('.woocommerce-breadcrumb .item:last-child').html('<a href="' + $('#link-brecumber-before').val() + '">' + $('.woocommerce-breadcrumb .item:last-child').text() + '</a>')
    }
    if ($('#text-brecumber').length > 0) {
        $('.woocommerce-breadcrumb').append('<span class="delimiter"> / </span><span class="item">' + $('#text-brecumber').val() + '</span>')
    }
    if ($('.text-brecumber-replace').length > 0) {
        $('.woocommerce-breadcrumb .item:last-child').text($('.active .text-brecumber-replace').val())
    }
    if ($('.queryproduct .product').length > 0 && $('.counterproduct').length > 0) {
        $('.counterproduct').text($('.queryproduct .product').length);
    }

    $(".heading-part-finder").click(function() {
        $(".content-part-finder").toggle();
        $(this).toggleClass("rotate");
    });

    function setHeightCredits() {
        $(".wc_payment_method label").click(function() {
            setTimeout(() => {
                if ($(".payment_box.payment_method_authnet").is(':hidden')) {
                    $(".woocommerce-checkout-form").ready(function() {
                        $("body.woocommerce-checkout .woocommerce").css("min-height", $(this).find(".col-sidebar").outerHeight());
                    })
                } else {
                    $(".woocommerce-checkout-form").ready(function() {
                        $("body.woocommerce-checkout .woocommerce").css("min-height", $(this).find(".col-sidebar").outerHeight());
                    })
                }
            }, 1000)
        });
    };
    $(window).on("load resize", function() {
        // resize select date
        $(".wpsbc-select-container").each(function() {
            $(this).find("#width_tmp_option").html($(this).find('#resizing_select option:selected').text());
            $(this).find('#resizing_select').width($(this).find("#width_tmp_select").width());
        })

        $(document).ajaxComplete(function(event, xhr, settings) {
            $(".wpsbc-select-container").each(function() {
                $(this).find("#width_tmp_option").html($(this).find('#resizing_select option:selected').text());
                $(this).find('#resizing_select').width($(this).find("#width_tmp_select").width());
            })

            if ($(window).width() < 992) {
                $("h3.woocommerce-checkout-heading").click(function() {
                    $(this).next(".woocommerce-checkout-content").toggleClass("hidden-checkout");
                    $(this).toggleClass("rotate");
                });

                $(".woocommerce-checkout-form").ready(function() {
                    $("body.woocommerce-checkout .woocommerce").css("min-height", $(this).find(".col-sidebar").outerHeight());
                })
            } else {
                setHeightCredits();
            }
        })

        if ($(window).width() < 992) {
            $(".heading-part-finder").click(function() {
                $(".content-part-finder").toggleClass("hidden-checkout");
                $(this).toggleClass("rotate");
            });
            $(".woocommerce-billing-fields .woocommerce-form-coupon-toggle h5").click(function() {
                $(".woocommerce-billing-fields__field-wrapper").toggleClass("hidden-checkout");
                $(this).toggleClass("rotate");
            });
            $(".col-sidebar #order_review_heading").click(function() {
                $(".woocommerce-checkout-review-order-table").toggleClass("hidden-checkout");
                $(this).toggleClass("rotate");
            });
            $(".woocommerce-form-coupon-toggle .woocommerce-info").click(function() {
                $(".checkout_coupon.woocommerce-form-coupon").toggleClass("hidden-checkout");
                $(this).toggleClass("rotate");
                $(this).closest(".woocommerce-form-coupon-toggle").toggleClass("visible-toggle");
            });
            $("h3.woocommerce-checkout-heading").click(function() {
                $(this).next(".woocommerce-checkout-content").toggleClass("hidden-checkout");
                $(this).toggleClass("rotate");
            });
            $(".woocommerce-checkout-form").ready(function() {
                $("body.woocommerce-checkout .woocommerce").css("min-height", 0);
            })
        } else {
            $(".woocommerce-checkout-form").ready(function() {
                $("body.woocommerce-checkout .woocommerce").css("min-height", $(this).find(".col-sidebar").outerHeight());
            })
            $("body").find(".hidden-checkout").removeClass(".hidden-checkout");

            setHeightCredits();
        }
    });




    $(document).ajaxComplete(function(event, xhr, settings) {
        $('.add_to_cart_button').html(icon_cart);
        if ($('.grouped_addcart').hasClass('added')) {
            window.location.href = $('a.added_to_cart.wc-forward').attr('href');
        }
        if ($('.counterchange').length > 0) {
            $('.title-count .woocommerce-result-count').html($(".counterchange").html());
        }
        if ($('#primary .wpf-no-products-found').length > 0) {
            $('.title-count .woocommerce-result-count span').text('0');
        }

        if ($('.woocommerce-variation-price').length > 0) {
            var variation_price = $('.woocommerce-variation-price').html();
            $('.opendiv-price .price').html(variation_price);
        }
        if (('.ajax_add_to_cart').length > 0) {
            $('[icon="cart"] .elementor-icon-list-text').text($('#cartcount').val())
        }
        if ($('.wishlist_products_counter_number').length > 0) {
            $('[icon="wishlist"] .elementor-icon-list-text').text($('.wishlist_counter .wishlist_products_counter_number').text())
        }
        textinstock();
    });
    //calculate the time before calling the function in window.onload


    var beforeload = (new Date()).getTime();

    function getPageLoadTime() {
        var afterload = (new Date()).getTime();
        seconds = (afterload - beforeload);
        $("#load_time").text('Loaded in  ' + seconds + ' sec(s).');
        $('body').append('<input type="hidden" value = ' + seconds + ' id="load_time" />')
        setTimeout(function() {
            changepricevariable()
        }, seconds)
    }
    window.onload = getPageLoadTime;

    function changepricevariable() {
        var currencySymbol = $('#curency_symboy').val();
        if ($('.woocommerce-variation-price').length > 0) {
            var variation_price = $('.woocommerce-variation-price .price').html();
            $('.opendiv-price .price').html(variation_price);
            var regular = parseInt($('.woocommerce-variation-price del').text().replace(currencySymbol, ''));
            var sale = parseInt($('.woocommerce-variation-price ins').text().replace(currencySymbol, ''));
            var pricetk = regular - sale;
            percent = (pricetk / regular) * 100;
            $('.onlycounter').text(percent.toFixed(0));
        } else if ($('.onlycounter').length > 0) {
            var regular = parseInt($('.opendiv-price .price del').text().replace(currencySymbol, ''));
            var sale = parseInt($('.opendiv-price .price ins').text().replace(currencySymbol, ''));
            var pricetk = regular - sale;
            percent = (pricetk / regular) * 100;
            $('.onlycounter').text(percent.toFixed(0));
        }
    }
    $('body').on('change', '.variations select', function() {
        changepricevariable();
    })
    $('.list_icon_header a[show="mobile"]').click(function() {
        $('.wpmm_mobile_menu_btn').trigger('click')
    })
    $('.dropdown_layered_nav_year-normal').closest('.widget_layered_nav').hide()
    $('.dropdown_layered_nav_model-normal').closest('.widget_layered_nav').hide()


    if($(".variations").length){
        $(".variable-items-wrapper .variable-item").each(function() {
            $(this).removeAttr("title");
        });
    }
})


$(".ct-field").focusout(function() {
    $("input").removeClass("wpcf7-not-valid");
    $(".wpcf7-not-valid-tip").addClass("wpcf7-focusout");
})
$('.linkto a').click(function() {
    var href = $(this).attr('href');
    $('.customer_login .form').removeClass('active')
    $(href).addClass('active');
    $('.woocommerce-notices-wrapper').html('');
    if ($('.text-brecumber-replace').length > 0) {
        $('.woocommerce-breadcrumb .item:last-child').text($('.active .text-brecumber-replace').val())
    }
})
if (location.hash) {
    $('.customer_login .form').removeClass('active')
    $(location.hash).addClass('active');

    //active layout grid and list 
    if ($('.view-layout').length > 0) {
        var selector = $('.view-layout a[href="' + location.hash + '"]');
        var _class = selector.data('layout');
        $('.view-layout a').removeClass('active');
        $(selector).addClass('active');
        if (_class == 'list') {
            $(selector).closest('.col-content').removeClass('layout-grid');
            $(selector).closest('.col-content').addClass('layout-list');
        }
        if (_class == 'grid') {
            $(selector).closest('.col-content').removeClass('layout-list');
            $(selector).closest('.col-content').addClass('layout-grid');
        }
    }
}