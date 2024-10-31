<?php
namespace NokautOffersBox;


use NokautOffersBox\Admin\Options;
use NokautOffersBox\Template\Renderer;
use NokautOffersBox\View\OffersBox;
use NokautOffersBox\View\OffersBoxException;

class ShortCode
{
    /**
     * @param array $attributes
     * @return string
     */
    public static function offersBox($attributes = array())
    {
        try {
            $a = shortcode_atts(array(
                'url' => null,
                'cid' => null,
                'template' => Options::getOption(Options::OPTION_DEFAULT_TEMPLATE_NAME),
                'render_type' => OffersBox::OFFERS_BOX_RENDER_TYPE_AJAX_DATA_HOOK,
                'limit' => null,
                'limit_min' => null,
                'classes' => null
            ), $attributes);

            try {
                return OffersBox::render($a['url'], $a['cid'], $a['template'], $a['render_type'], $a['limit'], $a['limit_min'], $a['classes']);
            } catch (OffersBoxException $e) {
                return Renderer::render('error.twig', ['message' => $e->getMessage()]);
            } catch (\Exception $e) {
                error_log($e->getMessage() . ' in file ' . $e->getFile() . ':' . $e->getLine());
                return Renderer::render('error.twig', ['message' => 'Internal error']);
            }
        } catch (\Exception $e) {
            echo '<!-- Nokaut Offers Box Error: Critical -->';
        }
    }
}
