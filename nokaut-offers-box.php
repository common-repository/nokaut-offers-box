<?php
/*
Plugin Name: Nokaut Offers Box
Plugin URI: http://nokaut.pl/
Description: Nokaut.pl provides offers boxes for your wordpress website.
Version: 1.4.0
Author: Sales Intelligence Sp. z o.o.
Author URI: http://nokaut.pl/
License: MIT
*/

/*
Copyright (c) 2016 Sales Intelligence S.A.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

Nokaut Offers Box uses some extra components:
1. Twig Template Engine:
- Source: https://twig.symfony.com/
- License: new BSD License
- License URI: https://twig.symfony.com/license
2. Nokaut.pl Search API KIT
- Source: https://github.com/nokaut/api-kit
- License: MIT License
- License URI: https://github.com/nokaut/api-kit/blob/master/LICENSE
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('NOKAUT_OFFERS_BOX_VERSION', '1.4.0');
define('NOKAUT_OFFERS_BOX_MINIMUM_WP_VERSION', '5.0');
define('NOKAUT_OFFERS_BOX_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NOKAUT_OFFERS_BOX_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NOKAUT_OFFERS_BOX_THEME_ASSETS_DIR', get_template_directory() . '/nokaut-offers-box/assets/');
define('NOKAUT_OFFERS_BOX_THEME_ASSETS_URL', get_template_directory_uri() . '/nokaut-offers-box/assets/');
define('NOKAUT_OFFERS_BOX_THEME_VIEW_DIR', get_template_directory() . '/nokaut-offers-box/view/');
define('NOKAUT_OFFERS_BOX_PLUGIN_VIEW_DIR', plugin_dir_path(__FILE__) . 'view/');

require_once NOKAUT_OFFERS_BOX_PLUGIN_DIR . "nokaut-offers-box-autoload.php";

register_activation_hook(__FILE__, array('NokautOffersBox\\NokautOffersBox', 'activate'));
register_deactivation_hook(__FILE__, array('NokautOffersBox\\NokautOffersBox', 'deactivate'));

/**
 * Plugin init
 */
\NokautOffersBox\NokautOffersBox::init();

if (is_admin()) {
    \NokautOffersBox\Admin\Admin::init();
}
