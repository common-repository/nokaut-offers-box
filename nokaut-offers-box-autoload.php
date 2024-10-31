<?php
/**
 * Autoloaders for plugin and composer
 */
require_once dirname(__FILE__) . '/vendor/autoload.php';

function nokaut_offers_box_autoloader_namespace($class)
{
    if (preg_match("/^(ApiKitNokautOffersBox|NokautOffersBox)/", $class)) {
        $class = ltrim($class, '\\');
        require_once 'src/' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    }
}

spl_autoload_register('nokaut_offers_box_autoloader_namespace');