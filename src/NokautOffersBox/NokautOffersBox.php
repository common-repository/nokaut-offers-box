<?php
namespace NokautOffersBox;

use NokautOffersBox\Admin\Options;
use NokautOffersBox\Template\Renderer;
use NokautOffersBox\View\OffersBox;
use NokautOffersBox\View\OffersBoxException;

class NokautOffersBox
{
    private static $initiated = false;

    public static function init()
    {
        if (!self::$initiated) {
            self::init_hooks();
        }
    }

    /**
     * Initializes WordPress hooks
     */
    private static function init_hooks()
    {
        self::$initiated = true;
        self::setTemplateDirs();

        ApiKitFactory::setApiKey(Options::getOption(Options::OPTION_API_KEY));
        ApiKitFactory::setApiUrl(Options::getOption(Options::OPTION_API_URL));

        add_action('wp_enqueue_scripts', array(__CLASS__, 'initNokautOffersBoxJs'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'initNokautOffersBoxCss'));

        self::initNokautOffersBoxAjax();
        self::registerShortCodes();
    }

    public static function activate()
    {
        // nothing to do
    }

    public static function deactivate()
    {
        // tidy up
    }

    private static function setTemplateDirs()
    {
        $templateDirs = array();
        if (file_exists(NOKAUT_OFFERS_BOX_THEME_VIEW_DIR)) {
            $templateDirs[] = NOKAUT_OFFERS_BOX_THEME_VIEW_DIR;
        }
        $templateDirs[] = NOKAUT_OFFERS_BOX_PLUGIN_VIEW_DIR;

        Renderer::setTemplateBasePaths($templateDirs);
    }

    public static function initNokautOffersBoxJs()
    {
        if (file_exists(NOKAUT_OFFERS_BOX_THEME_ASSETS_DIR . 'js/nokaut-offers-box.js')) {
            wp_register_script('nokaut-offers-box.js', NOKAUT_OFFERS_BOX_THEME_ASSETS_URL . 'js/nokaut-offers-box.js', array('jquery'), NOKAUT_OFFERS_BOX_VERSION);
        } else {
            wp_register_script('nokaut-offers-box.js', NOKAUT_OFFERS_BOX_PLUGIN_URL . 'assets/js/nokaut-offers-box.js', array('jquery'), NOKAUT_OFFERS_BOX_VERSION);
        }

        wp_localize_script('nokaut-offers-box.js', 'ajax_nokaut_offers_box_object', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
        wp_enqueue_script('nokaut-offers-box.js');
    }

    public static function initNokautOffersBoxCss()
    {
        if (file_exists(NOKAUT_OFFERS_BOX_THEME_ASSETS_DIR . 'css/nokaut-offers-box.css')) {
            wp_register_style('nokaut-offers-box.css', NOKAUT_OFFERS_BOX_THEME_ASSETS_URL . 'css/nokaut-offers-box.css', array(), NOKAUT_OFFERS_BOX_VERSION);
        } else {
            wp_register_style('nokaut-offers-box.css', NOKAUT_OFFERS_BOX_PLUGIN_URL . 'assets/css/nokaut-offers-box.css', array(), NOKAUT_OFFERS_BOX_VERSION);
        }
        wp_enqueue_style('nokaut-offers-box.css');
    }

    public static function initNokautOffersBoxAjax()
    {
        add_action('wp_ajax_get_nokaut_offers_box', array(__CLASS__, 'ajaxNokautOffersBoxCallback'));
        add_action('wp_ajax_nopriv_get_nokaut_offers_box', array(__CLASS__, 'ajaxNokautOffersBoxCallback'));
    }

    public static function ajaxNokautOffersBoxCallback()
    {
        $url = (isset($_POST['url']) and $_POST['url']) ? $_POST['url'] : null;
        $campaignId = (isset($_POST['cid']) and $_POST['cid']) ? $_POST['cid'] : null;
        $template = (isset($_POST['template']) and $_POST['template']) ? $_POST['template'] : Options::getOption(Options::OPTION_DEFAULT_TEMPLATE_NAME);
        $limit = isset($_POST['limit']) ? $_POST['limit'] : null;
        $limitMin = isset($_POST['limit_min']) ? $_POST['limit_min'] : null;
        $classes = isset($_POST['classes']) ? $_POST['classes'] : null;

        $data = array();
        $data['url'] = $url;
        $data['cid'] = $campaignId;
        $data['template'] = $template;
        $data['limit'] = $limit;
        $data['limit_min'] = $limitMin;
        $data['classes'] = $classes;
        $data['offers_box'] = '';
        $data['error'] = '';

        try {
            $offersBox = new OffersBox();
            $data['offers_box'] = $offersBox->render($url, $campaignId, $template, OffersBox::OFFERS_BOX_RENDER_TYPE_INLINE, $limit, $limitMin, $classes, true);
        } catch (OffersBoxException $e) {
            $data['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['error'] = 'Internal error';
            error_log($e->getMessage() . ' in file ' . $e->getFile() . ':' . $e->getLine());
        }

        header('Content-Type: application/json');
        echo json_encode($data);
        die;
    }

    public static function registerShortCodes()
    {
        add_shortcode('nokaut-offers-box', array('\\NokautOffersBox\\ShortCode', 'offersBox'));
    }
}