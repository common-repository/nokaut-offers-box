(function ($) {
    "use strict";
    $(document).ready(function () {
        $("#nokaut_offers_box_option_products_found_min").jRange({
            from: 0,
            to: 10,
            format: '%s',
            width: 300,
            showLabels: true,
            isRange: false,
            snap: true
        });

        $("#nokaut_offers_box_option_products_limit").jRange({
            from: 1,
            to: 20,
            format: '%s',
            width: 300,
            showLabels: true,
            isRange: false,
            snap: true
        });
    });
}(jQuery));