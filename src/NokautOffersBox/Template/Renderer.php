<?php
namespace NokautOffersBox\Template;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

class Renderer
{
    /**
     * @var Environment
     */
    private static $twig;

    /**
     * @var array()
     */
    private static $templateBasePaths = array();

    /**
     * @param Environment $twig
     */
    private static function setTwig(Environment $twig)
    {
        self::$twig = $twig;
    }

    /**
     * @return Environment
     */
    private static function getTwig()
    {
        return self::$twig;
    }

    /**
     * @param array $templateBasePaths
     */
    public static function setTemplateBasePaths($templateBasePaths)
    {
        if (!is_array($templateBasePaths)) {
            $templateBasePaths = array($templateBasePaths);
        }

        self::$templateBasePaths = $templateBasePaths;
    }

    /**
     * @return array
     */
    private static function getTemplateBasePaths()
    {
        return self::$templateBasePaths;
    }

    /**
     * @param string $template
     * @param array $context
     * @return string
     */
    public static function render($template, $context = array())
    {
        if (!self::getTwig()) {
            $loader = new FilesystemLoader(self::getTemplateBasePaths());
            self::setTwig(new Environment($loader));

            self::getTwig()->addFilter(new TwigFilter('photoUrl', array('NokautOffersBox\\Template\\Filter\\PhotoUrl', 'getPhotoUrl')));
            self::getTwig()->addFilter(new TwigFilter('shopLogoUrl', array('NokautOffersBox\\Template\\Filter\\PhotoUrl', 'getShopLogoUrl')));
            self::getTwig()->addFilter(new TwigFilter('validProductsAjaxUrl', array('NokautOffersBox\\Template\\Filter\\ProductsUrl', 'isValidProductsAjaxUrl')));
            self::getTwig()->addFilter(new TwigFilter('clickUrl', array('NokautOffersBox\\Template\\Filter\\ClickUrl', 'clickUrl')));
            self::getTwig()->addFilter(new TwigFilter('isNoFollowUrl', array('NokautOffersBox\\Template\\Filter\\ClickUrl', 'isNoFollowUrl')));
            self::getTwig()->addFilter(new TwigFilter('short', array('NokautOffersBox\\Template\\Filter\\Text', 'short')));
            self::getTwig()->addFilter(new TwigFilter('price', array('NokautOffersBox\\Template\\Filter\\Text', 'price')));
        }

        $template = self::getTwig()->load($template);
        return $template->render($context);
    }
}
