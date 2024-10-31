<?php
namespace NokautOffersBox;

class UrlHelper
{
    const REQUEST_URL_SEPARATOR = '##';

    /**
     * @param string $whiteLabelUrl
     * @return string
     */
    public static function filterWhiteLabelNokautUrl($whiteLabelUrl)
    {
        if (preg_match('@^http@', trim($whiteLabelUrl))) {
            if (preg_match('@nokaut.pl@', $whiteLabelUrl)) {
                return $whiteLabelUrl;
            }
            if (preg_match('/' . self::REQUEST_URL_SEPARATOR . '(.*)/', $whiteLabelUrl, $matches)) {
                if (substr_count($matches[1], '/') == 0) {
                    return '/' . ltrim($matches[1], '/') . '/';
                } else {
                    return '/' . ltrim($matches[1], '/');
                }
            }
            $relativeWhiteLabelUrl = preg_replace('@^https*://[^/]+/@', '', trim($whiteLabelUrl));
            $parts = explode('/', $relativeWhiteLabelUrl);
            $parts = array_reverse($parts);
            $parts = array_slice($parts, 0, 2);

            if (isset($parts[1]) && substr_count($parts[1], '-') == 0) {
                unset($parts[1]);
            }

            if (count($parts) == 1) {
                return '/' . substr($parts[0], strpos($parts[0], '-') + 1) . '/';
            } else {
                return '/' . substr($parts[1], strpos($parts[1], '-') + 1) . '/' . $parts[0];
            }
        } else {
            return $whiteLabelUrl;
        }
    }
}