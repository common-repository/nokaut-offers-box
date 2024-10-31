<?php
namespace NokautOffersBox\Admin;

use Nokaut\ApiKit\Collection\Products;
use NokautOffersBox\ApiKitFactory;

class Options
{
    const OPTIONS_GROUP = 'nokaut_offers_box_options';
    const OPTIONS_SECTION_RESET_OPTIONS = 'nokaut_offers_box_options_section_reset_options';
    const OPTIONS_SECTION_API = 'nokaut_offers_box_options_section_api';
    const OPTIONS_SECTION_TEST_API_CONNECTION = 'nokaut_offers_box_options_section_test_api_connection';
    const OPTIONS_SECTION_SETTINGS = 'nokaut_offers_box_options_section_settings';
    const OPTION_API_KEY = 'nokaut_offers_box_api_key';
    const OPTION_API_URL = 'nokaut_offers_box_api_url';
    const OPTION_PRODUCTS_FOUND_MIN = 'nokaut_offers_box_option_products_found_min';
    const OPTION_PRODUCTS_LIMIT = 'nokaut_offers_box_option_products_limit';
    const OPTION_DEFAULT_TEMPLATE_NAME = 'nokaut_offers_box_option_default_template_name';

    private static $optionsDefault = array(
        self::OPTION_API_KEY => '',
        self::OPTION_API_URL => 'http://nokaut.io/api/v2/',
        self::OPTION_PRODUCTS_FOUND_MIN => 1,
        self::OPTION_PRODUCTS_LIMIT => 12,
        self::OPTION_DEFAULT_TEMPLATE_NAME => 'list',
    );

    public static function init()
    {
        if (self::resetOptionsModeEnabled()) {
            self::resetOptions();
            ApiKitFactory::setApiKey(Options::getOption(Options::OPTION_API_KEY));
            ApiKitFactory::setApiUrl(Options::getOption(Options::OPTION_API_URL));
        }

        register_setting(self::OPTIONS_GROUP, self::OPTIONS_GROUP, array(__CLASS__, 'validate'));

        add_settings_section(self::OPTIONS_SECTION_RESET_OPTIONS, 'Resetowanie ustawień wtyczki', array(__CLASS__, 'sectionResetOptionsText'),
            Admin::NOKAUT_OFFERS_BOX_CONFIG_PAGE_UNIQUE_KEY);

        add_settings_section(self::OPTIONS_SECTION_API, 'Dostęp do API', array(__CLASS__, 'sectionApiText'),
            Admin::NOKAUT_OFFERS_BOX_CONFIG_PAGE_UNIQUE_KEY);

        add_settings_field(self::OPTION_API_KEY, 'API KEY', array(__CLASS__, 'apiKeyInputText'),
            Admin::NOKAUT_OFFERS_BOX_CONFIG_PAGE_UNIQUE_KEY, self::OPTIONS_SECTION_API);

        add_settings_field(self::OPTION_API_URL, 'API URL', array(__CLASS__, 'apiUrlInputText'),
            Admin::NOKAUT_OFFERS_BOX_CONFIG_PAGE_UNIQUE_KEY, self::OPTIONS_SECTION_API);

        add_settings_section(self::OPTIONS_SECTION_TEST_API_CONNECTION, 'Test komunikacji z API', array(__CLASS__, 'sectionTestApiConnectionText'),
            Admin::NOKAUT_OFFERS_BOX_CONFIG_PAGE_UNIQUE_KEY);

        add_settings_section(self::OPTIONS_SECTION_SETTINGS, 'Ustawienia', array(__CLASS__, 'sectionSettingsText'),
            Admin::NOKAUT_OFFERS_BOX_CONFIG_PAGE_UNIQUE_KEY);

        add_settings_field(self::OPTION_PRODUCTS_FOUND_MIN, 'Minimalna ilość ofert do wyświetlenia', array(__CLASS__, 'productsFoundMinInputText'),
            Admin::NOKAUT_OFFERS_BOX_CONFIG_PAGE_UNIQUE_KEY, self::OPTIONS_SECTION_SETTINGS);

        add_settings_field(self::OPTION_PRODUCTS_LIMIT, 'Limit ofert do wyświetlenia', array(__CLASS__, 'productsLimitInputText'),
            Admin::NOKAUT_OFFERS_BOX_CONFIG_PAGE_UNIQUE_KEY, self::OPTIONS_SECTION_SETTINGS);

        add_settings_field(self::OPTION_DEFAULT_TEMPLATE_NAME, 'Nazwa domyślnego szablonu', array(__CLASS__, 'defaultTemplateNameInputText'),
            Admin::NOKAUT_OFFERS_BOX_CONFIG_PAGE_UNIQUE_KEY, self::OPTIONS_SECTION_SETTINGS);
    }

    public static function sectionResetOptionsText()
    {
        echo '<p>W razie problemów z wtyczką po zapisaniu nieprawidłowych danych, zresetuj ustawienia wtyczki, aby zacząć od początku.</p>';
        echo '<p><a href="/wp-admin/options-general.php?page=nokaut-offers-box-config&nokaut-offers-box-options-reset=1" class="button button-primary">Zresetuj ustawienia wtyczki</a></p>';
    }

    public static function sectionApiText()
    {
        echo '<p>Wprowadź klucz dostępowy API oraz bazowy adres URL API (kończacy się slash\'em "/", baza do wywołania zasobów: products, offers, categories).</p>';
    }

    public static function apiKeyInputText()
    {
        $value = self::getOption(self::OPTION_API_KEY);
        echo "<input id='" . self::OPTION_API_KEY . "' name='nokaut_offers_box_options[" . self::OPTION_API_KEY . "]' size='95' type='text' value='{$value}' />";
    }

    public static function apiUrlInputText()
    {
        $value = self::getOption(self::OPTION_API_URL);
        echo "<input id='" . self::OPTION_API_URL . "' name='nokaut_offers_box_options[" . self::OPTION_API_URL . "]' size='95' type='text' value='{$value}' />";
    }

    public static function sectionTestApiConnectionText()
    {
        try {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
            $apiKit = ApiKitFactory::getApiKitInDebugMode();
            $productsRepository = $apiKit->getProductsRepository();
            $products = $productsRepository->fetchProductsByCategory(array(127), 1, array('id'));
            if ($products instanceof Products) {
                echo '<p><b>Poprawna komunikacja z API Nokaut.pl</b></p>';
            }
        } catch (\Exception $e) {
            echo '<p><b>Błąd w komunikacji z API Nokaut.pl:</b> ' . $e->getMessage() . '</p>';
        }
    }

    public static function sectionSettingsText()
    {
        echo '<p>Regulacja działania wtyczki.</p>';
    }

    public static function productsFoundMinInputText()
    {
        $value = self::getOption(self::OPTION_PRODUCTS_FOUND_MIN);
        echo "<input type='hidden' id='" . self::OPTION_PRODUCTS_FOUND_MIN . "' name='nokaut_offers_box_options[" . self::OPTION_PRODUCTS_FOUND_MIN . "]' value='{$value}' />";
    }

    public static function productsLimitInputText()
    {
        $value = self::getOption(self::OPTION_PRODUCTS_LIMIT);
        echo "<input type='hidden' id='" . self::OPTION_PRODUCTS_LIMIT . "' name='nokaut_offers_box_options[" . self::OPTION_PRODUCTS_LIMIT . "]' value='{$value}' />";
    }

    public static function defaultTemplateNameInputText()
    {
        $value = self::getOption(self::OPTION_DEFAULT_TEMPLATE_NAME);
        echo "<input id='" . self::OPTION_DEFAULT_TEMPLATE_NAME . "' name='nokaut_offers_box_options[" . self::OPTION_DEFAULT_TEMPLATE_NAME . "]' size='95' type='text' value='{$value}' />";
    }

    public static function validate($input)
    {
        $options = $input;
        return $options;
    }

    public static function form()
    {
        echo '<form method="post" action="options.php"> ';
        settings_fields(self::OPTIONS_GROUP);
        do_settings_sections(Admin::NOKAUT_OFFERS_BOX_CONFIG_PAGE_UNIQUE_KEY);
        submit_button();
        echo '</form>';
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function getOption($key)
    {
        $options = get_option(self::OPTIONS_GROUP);
        if (isset($options[$key])) {
            return $options[$key];
        } elseif (isset(self::$optionsDefault[$key])) {
            return self::$optionsDefault[$key];
        }
    }

    /**
     * @return bool
     */
    public static function resetOptionsModeEnabled()
    {
        return
            is_admin() && isset($_GET['page'])
            && $_GET['page'] == 'nokaut-offers-box-config'
            && isset($_GET['nokaut-offers-box-options-reset'])
            && $_GET['nokaut-offers-box-options-reset'] == '1';
    }

    public static function resetOptions()
    {
        delete_option(self::OPTIONS_GROUP);
    }

    public static function redirectAfterResetOptions()
    {
        if (self::resetOptionsModeEnabled()) {
            wp_redirect('/wp-admin/options-general.php?page=nokaut-offers-box-config');
        }
    }
}