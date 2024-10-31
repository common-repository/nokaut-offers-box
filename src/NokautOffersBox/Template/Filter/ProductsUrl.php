<?php
namespace NokautOffersBox\Template\Filter;

class ProductsUrl
{
    /**
     * @param string $url
     * @return bool
     */
    public static function isValidProductsAjaxUrl($url)
    {
        $url = trim($url);
        return !(in_array($url, array('', '/')) || preg_match('/^\/--/', $url));
    }

}