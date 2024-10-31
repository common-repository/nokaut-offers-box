<?php
namespace NokautOffersBox\Template\Filter;

class PhotoUrl
{
    const IMG_BASE_URL = '//offers.gallery/';
    const IMG_SIZE50x50 = '50x50';
    const IMG_SIZE100x100 = '100x100';
    const IMG_SIZE130x130 = '130x130';
    const IMG_SIZE150x150 = '150x150';
    const IMG_SIZE170x170 = '170x170';
    const IMG_SIZE200x200 = '200x200';
    const IMG_SIZE250x250 = '250x250';
    const IMG_SIZE300x300 = '300x300';
    const IMG_SIZE350x350 = '350x350';
    const IMG_SIZE400x400 = '400x400';
    const IMG_SIZE450x450 = '450x450';
    const IMG_SIZE500x500 = '500x500';

    /**
     * @param string $shopUrlLogo
     * @return string
     */
    public static function getShopLogoUrl($shopUrlLogo)
    {
        return self::IMG_BASE_URL . ltrim($shopUrlLogo, "/");
    }

    /**
     * @param string $photoId
     * @param string $size
     * @param string $additionalUrlPart
     * @return string
     */
    public static function getPhotoUrl($photoId, $size = self::IMG_SIZE130x130, $additionalUrlPart = '')
    {
        return self::IMG_BASE_URL . ltrim(\Nokaut\ApiKit\Helper\PhotoUrl::prepare($photoId, $size, $additionalUrlPart), "/");
    }
}