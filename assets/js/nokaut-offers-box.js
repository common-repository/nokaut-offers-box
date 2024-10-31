(function ($) {
    "use strict";
    $(document).ready(function () {
        $('[data-hook="nokaut-offers-box"]').each(function () {
            var loaded = $(this).attr('data-loaded');
            if (typeof loaded !== 'undefined' && loaded == "true") {
                return;
            }

            loadAjaxNokautOffersBox($(this));
        });

        // inline mode
        initProductMode();
        initProductsMode();
        checkWidth();

        initSelectLists();

        $(window).resize(function () {
            checkWidth();
        });
    });

    var loadAjaxNokautOffersBox = function (nokautOffersBox) {
        var postData = {
            action: 'get_nokaut_offers_box',
            url: nokautOffersBox.attr('data-url'),
            cid: nokautOffersBox.attr('data-cid'),
            template: nokautOffersBox.attr('data-template')
        };

        var limit = nokautOffersBox.attr('data-limit');
        if (typeof limit !== 'undefined') {
            postData['limit'] = limit;
        }

        var limitMin = nokautOffersBox.attr('data-limit-min');
        if (typeof limitMin !== 'undefined') {
            postData['limit_min'] = limitMin;
        }

        var classes = nokautOffersBox.attr('data-classes');
        if (typeof classes !== 'undefined') {
            postData['classes'] = classes;
        }

        jQuery.post(ajax_nokaut_offers_box_object.ajax_url, postData, function (response) {
            if (response.error) {
                nokautOffersBox.html('<!-- ' + response.error + ' -->');
            } else if (response.offers_box) {
                nokautOffersBox.html(response.offers_box);
                checkWrapperWidth(nokautOffersBox.find('.NOK-Wrapper'));
                nokautOffersBox.show();

                initProductMode(nokautOffersBox);
                initProductsMode(nokautOffersBox);
            }
        }).fail(function () {
            nokautOffersBox.html('<!-- Nokaut Offers Box Error: Internal Error -->');
        });
    };

    var initProductMode = function (nokautOffersBox) {
        var showMoreOffersButton = null;
        var hideMoreOffersButton = null;
        var showMoreDescButton = null;
        var hideMoreDescButton = null;

        if (typeof nokautOffersBox !== 'undefined') {
            showMoreOffersButton = nokautOffersBox.find('[data-hook="nokaut-show-more-offers"]');
            hideMoreOffersButton = nokautOffersBox.find('[data-hook="nokaut-hide-more-offers"]');
            showMoreDescButton = nokautOffersBox.find('[data-hook="nokaut-show-more-desc"]');
            hideMoreDescButton = nokautOffersBox.find('[data-hook="nokaut-hide-more-desc"]');
        } else {
            showMoreOffersButton = $('[data-hook="nokaut-show-more-offers"]');
            hideMoreOffersButton = $('[data-hook="nokaut-hide-more-offers"]');
            showMoreDescButton = $('[data-hook="nokaut-show-more-desc"]');
            hideMoreDescButton = $('[data-hook="nokaut-hide-more-desc"]');
        }

        if (showMoreOffersButton) {
            showMoreOffersButton.click(function () {
                showMoreOffersButton.hide();
                $(this).closest('[data-hook="nokaut-offers-box"]').find('[data-hook="nokaut-more-offers"]').show();
            });
        }

        if (hideMoreOffersButton) {
            hideMoreOffersButton.click(function () {
                $(this).closest('[data-hook="nokaut-offers-box"]').find('[data-hook="nokaut-more-offers"]').hide();
                showMoreOffersButton.show();
                scrollTo($(this).closest('[data-hook="nokaut-offers-box"]'));
            });
        }

        if (showMoreDescButton) {
            showMoreDescButton.click(function () {
                showMoreDescButton.hide();
                $(this).closest('[data-hook="nokaut-description-box"]').find('[data-hook="nokaut-description-text"]').height('auto');
            });
        }

        if (hideMoreDescButton) {
            hideMoreDescButton.click(function () {
                showMoreDescButton.show();
                $(this).closest('[data-hook="nokaut-description-box"]').find('[data-hook="nokaut-description-text"]').height(98);
            });
        }
    };

    var initProductsMode = function (nokautOffersBox) {
        var toggleSortsButton = null;
        var toggleFiltersButton = null;
        var showMoreProductsButton = null;
        var dataNokautUrls = null;
        var searchSubmit = null;

        if (typeof nokautOffersBox !== 'undefined') {
            toggleSortsButton = nokautOffersBox.find('[data-hook="nokaut-mall-sorts"]');
            toggleFiltersButton = nokautOffersBox.find('[data-hook="nokaut-mall-filters"]');
            showMoreProductsButton = nokautOffersBox.find('[data-hook="nokaut-mall-products-more"]');
            dataNokautUrls = nokautOffersBox.find('[data-nokaut-url]');
            searchSubmit = nokautOffersBox.find('[data-hook="nokaut-search-submit"]');
        } else {
            toggleSortsButton = $('[data-hook="nokaut-mall-sorts"]');
            toggleFiltersButton = $('[data-hook="nokaut-mall-filters"]');
            showMoreProductsButton = $('[data-hook="nokaut-mall-products-more"]');
            dataNokautUrls = $('[data-nokaut-url]')
            searchSubmit = $('[data-hook="nokaut-search-submit"]');
        }

        if (toggleSortsButton) {
            toggleSortsButton.click(function () {
                $(this).closest('[data-hook="nokaut-offers-box"]').find('[data-hook="nokaut-mall-filters-body"]:visible').toggle();
                $(this).closest('[data-hook="nokaut-offers-box"]').find('[data-hook="nokaut-mall-sorts-body"]').toggle();
            });
        }

        if (toggleFiltersButton) {
            toggleFiltersButton.click(function () {
                $(this).closest('[data-hook="nokaut-offers-box"]').find('[data-hook="nokaut-mall-sorts-body"]:visible').toggle();
                $(this).closest('[data-hook="nokaut-offers-box"]').find('[data-hook="nokaut-mall-filters-body"]').toggle();
            });
        }

        if (showMoreProductsButton) {
            var nextOffset = 4;

            showMoreProductsButton.click(function () {
                var nokautOffersBox = $(this).closest('[data-hook="nokaut-offers-box"]');
                var allCount = nokautOffersBox.find('[data-hook="nokaut-mall-product"]').length;
                var visible = nokautOffersBox.find('[data-hook="nokaut-mall-product"]:visible');
                var visibleCount = visible.length;

                nokautOffersBox.find('[data-hook="nokaut-mall-product"]').slice(visibleCount, visibleCount + nextOffset).show();
                scrollTo(visible.last());

                if (visibleCount + nextOffset >= allCount) {
                    showMoreProductsButton.hide();
                }
            });

            showMoreProductsButton.each(function () {
                var nokautOffersBox = $(this).closest('[data-hook="nokaut-offers-box"]');
                var allCount = nokautOffersBox.find('[data-hook="nokaut-mall-product"]').length;
                var visibleCount = nokautOffersBox.find('[data-hook="nokaut-mall-product"]:visible').length;

                if (visibleCount + nextOffset >= allCount) {
                    showMoreProductsButton.hide();
                }
            });
        }

        if (searchSubmit) {
            searchSubmit.on('click change', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var url = '';
                var searchSection = $(this).closest('[data-hook="nokaut-search"]');
                var searchInput = searchSection.find('[data-hook="nokaut-search-input"]');
                var searchInputValue = searchInput.val().trim();
                var searchInCategory = searchSection.find('[data-hook="nokaut-search-in-category"]:checked').val();

                if (!searchInputValue) {
                    return false;
                }

                if (searchInCategory == 1) {
                    url = searchInput.attr('data-search-category-url-template').replace(/%s/g, encodeURIComponent(searchInputValue));
                } else {
                    url = searchInput.attr('data-search-global-url-template').replace(/%s/g, encodeURIComponent(searchInputValue));
                }

                var nokautOffersBox = $(this).closest('[data-hook="nokaut-offers-box"]');
                loadAjaxProducts(nokautOffersBox, url);
            });
        }

        if (dataNokautUrls) {
            dataNokautUrls.click(function (e) {
                e.preventDefault();
                e.stopPropagation();

                var nokautOffersBox = $(this).closest('[data-hook="nokaut-offers-box"]');
                var url = $(this).attr('data-nokaut-url');
                loadAjaxProducts(nokautOffersBox, url);
            });
        }

        var loadAjaxProducts = function (nokautOffersBox, url) {
            if (!isValidUrl(url)) {
                alert('Operacja niemożliwa do wykonania');
                return false;
            }
            nokautOffersBox.attr('data-url', url);
            nokautOffersBox.find('[data-hook="nokaut-mall-loader"]').show();
            scrollTo(nokautOffersBox, -50);
            loadAjaxNokautOffersBox(nokautOffersBox);
        };

        var isValidUrl = function (url) {
            url = url.trim();
            return !(url == '' || url == '/' || url.search(/^\/--/) >= 0);
        };
    };

    var checkWrapperWidth = function (wrapper) {
        if (wrapper && typeof wrapper !== 'undefined') {
            var parentContainer = wrapper.parent(),
                width = wrapper.width() !== 0 ? wrapper.width() : parentContainer.parent().width(),
                name = parentContainer.attr('id');

            switch (name) {
                case 'NOK-Simple-Boxs':
                    if ((width < 584 && width >= 300)) {
                      wrapper.addClass("NOK-Mobile-Small-Text").removeClass("NOK-Mobile");
                    } else if (width < 300) {
                      wrapper.addClass("NOK-Mobile").removeClass("NOK-Mobile-Small-Text");
                    } else {
                      wrapper.removeClass("NOK-Mobile NOK-Mobile-Small-Text");
                    }
                    break;
                case 'NOK-Product-Default':
                    if ((width < 500 && width >= 450)) {
                      wrapper.addClass("NOK-Mobile-Small-Text").removeClass("NOK-Mobile");
                    } else if (width < 450) {
                      wrapper.addClass("NOK-Mobile").removeClass("NOK-Mobile-Small-Text");
                    } else {
                      wrapper.removeClass("NOK-Mobile NOK-Mobile-Small-Text");
                    }
                    break;
                case 'NOK-Mall-Default':
                    if (width < 400) {
                      wrapper.addClass("NOK-Mobile");
                    } else {
                      wrapper.removeClass("NOK-Mobile");
                    }
                    break;
                case 'NOK-Simple-Box':
                    if (width < 280) {
                      wrapper.addClass("NOK-Mobile");
                    } else {
                      wrapper.removeClass("NOK-Mobile");
                    }
                    break;
                case 'NOK-Full-Box':
                    if (width < 380) {
                      wrapper.addClass("NOK-Mobile");
                    } else {
                      wrapper.removeClass("NOK-Mobile");
                    }
                    break;
                case 'NOK-Simple-List':
                    if (width < 500 && width >= 300) {
                      wrapper.addClass("NOK-Mobile-Small-Text").removeClass("NOK-Mobile");
                    } else if (width < 300) {
                      wrapper.addClass("NOK-Mobile").removeClass("NOK-Mobile-Small-Text");
                    } else {
                      wrapper.removeClass("NOK-Mobile NOK-Mobile-Small-Text");
                    }
                    break;
            }
        }
    };

    var checkWidth = function () {
        $('.NOK-Wrapper').each(function () {
            checkWrapperWidth($(this));
        });
    };

    var initSelectLists = function () {
        $('body').on('click', '.NOK-select-list', function () {
            var that = this;

            if ($('.NOK-list li', this).length > 0) {
                if ($('.NOK-list', this).hasClass('NOK-hidden')) {
                    $('.NOK-select-list .NOK-list').hide().addClass('NOK-hidden');
                    $('.NOK-list', this).slideDown(300, function () {
                        $('.NOK-list', that).removeClass('NOK-hidden');
                    });
                } else {
                    $('.NOK-list', this).slideUp(300, function () {
                        $('.NOK-list', that).addClass('NOK-hidden');
                    });
                }
            }
        });
    };

    var scrollTo = function (selector, offset) {
        if (typeof offset == 'undefined') {
            offset = 0;
        }

        $('html, body').animate({
            scrollTop: selector.offset().top + offset + 'px'
        }, 'fast');
    };
}(jQuery));