<?php
namespace NokautOffersBox\Template\Filter;

class ClickUrl
{
    const CLICK_BASE_URL = 'http://nokaut.click/';

    /**
     * @param $clickUrl
     * @param int|null $campaignId
     * @return string
     */
    public static function clickUrl($clickUrl, $campaignId = null)
    {
        if (self::isAbsoluteUrl($clickUrl)) {
            return $clickUrl;
        }

        $url = self::CLICK_BASE_URL . ltrim($clickUrl, "/");

        $campaignId = (int)$campaignId;
        if ($campaignId) {
            $url .= '&cid=' . $campaignId;
        }

        return $url;
    }

    /**
     * @param $url
     * @return bool
     */
    public static function isNoFollowUrl($url)
    {
        if (!$url) {
            return false;
        }

        $isClickUrl = (bool)preg_match('/' . preg_quote(self::CLICK_BASE_URL, '/') . '/', $url);
        $isNokautUrl = (bool)preg_match('/nokaut.pl/', $url);
        return $isClickUrl || $isNokautUrl;
    }

    /**
     * @param string $url
     * @return bool
     */
    private static function isAbsoluteUrl($url)
    {
        return (bool)preg_match('/^http/', trim($url));
    }
}