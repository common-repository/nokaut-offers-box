<?php
namespace NokautOffersBox;

use ApiKitNokautOffersBox\ApiKitNokautOffersBox;
use Nokaut\ApiKit\Config;
use NokautOffersBox\Logger\PrintHtml;

class ApiKitFactory
{
    /**
     * @var ApiKitNokautOffersBox;
     */
    protected static $apiKit;

    /**
     * @var string
     */
    protected static $apiKey;

    /**
     * @var string
     */
    protected static $apiUrl;

    /**
     * @param string $apiKey
     */
    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    /**
     * @return string
     */
    protected static function getApiKey()
    {
        return self::$apiKey;
    }

    /**
     * @param string $apiUrl
     */
    public static function setApiUrl($apiUrl)
    {
        self::$apiUrl = $apiUrl;
    }

    /**
     * @return string
     */
    protected static function getApiUrl()
    {
        return self::$apiUrl;
    }

    /**
     * @return ApiKitNokautOffersBox
     */
    public static function getApiKit()
    {
        if (!self::$apiKit) {
            $config = new Config();
            $config->setApiAccessToken(self::getApiKey());
            $config->setApiUrl(self::getApiUrl());
            self::$apiKit = new ApiKitNokautOffersBox($config);
        }

        return self::$apiKit;
    }

    /**
     * @return ApiKitNokautOffersBox
     */
    public static function getApiKitInDebugMode()
    {
        $config = new Config();
        $config->setApiAccessToken(self::getApiKey());
        $config->setApiUrl(self::getApiUrl());
        $config->setLogger(new PrintHtml());
        return new ApiKitNokautOffersBox($config);
    }
} 