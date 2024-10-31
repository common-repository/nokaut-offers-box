<?php
namespace NokautOffersBox\View;

use Nokaut\ApiKit\ClientApi\Rest\Fetch\ProductsFetch;
use Nokaut\ApiKit\Collection\Offers;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Offer;
use Nokaut\ApiKit\Entity\Product;
use Nokaut\ApiKit\Entity\ProductWithBestOffer;
use Nokaut\ApiKit\Ext\Data;
use Nokaut\ApiKit\Repository\OffersRepository;
use ApiKitNokautOffersBox\Ext\Data as OffersBoxData;
use ApiKitNokautOffersBox\Repository\ProductsRepository;
use NokautOffersBox\Admin\Options;
use NokautOffersBox\ApiKitFactory;
use NokautOffersBox\Template\Renderer;
use NokautOffersBox\UrlHelper;

class OffersBox
{
    const OFFERS_BOX_RENDER_TYPE_INLINE = 'inline';
    const OFFERS_BOX_RENDER_TYPE_AJAX_DATA_HOOK = 'ajax';
    const NOKAUT_URL_PARAM_REGEXP = "/^p:(?P<nokaut_url>.*)/";

    private static $urlMap = array();

    private static $classMap = array(
        'big' => 'Big',
        'row1' => 'NOK-row1',
        'row2' => 'NOK-row2',
        'row3' => 'NOK-row3',
        'row4' => 'NOK-row4',
        'row5' => 'NOK-row5',
    );

    /**
     * @param string $nokautUrl
     * @param int $campaignId
     * @param string $template
     * @param string $renderType
     * @param null|int $limit
     * @param null|int $limitMin
     * @param null|string $classes
     * @param bool $ajaxMode
     * @return string
     * @throws OffersBoxException
     */
    public static function render($nokautUrl, $campaignId = 0, $template, $renderType = self::OFFERS_BOX_RENDER_TYPE_INLINE, $limit = null, $limitMin = null, $classes = null, $ajaxMode = false)
    {
        if (!$nokautUrl) {
            throw new OffersBoxException("Not defined nokaut url");
        }

        if (!$template) {
            $template = Options::getOption(Options::OPTION_DEFAULT_TEMPLATE_NAME);
        }

        if (preg_match('@[^a-zA-Z0-9_/-]@', $template)) {
            throw new OffersBoxException("Template name accept only characters from: [a-zA-Z0-9_/-]");
        }

        switch ($renderType) {
            case self::OFFERS_BOX_RENDER_TYPE_INLINE:
                return self::renderInline($nokautUrl, $campaignId, $template, $limit, $limitMin, $classes, $ajaxMode);
                break;
            case self::OFFERS_BOX_RENDER_TYPE_AJAX_DATA_HOOK:
                return self::renderAjaxDataHook($nokautUrl, $campaignId, $template, $limit, $limitMin, $classes);
                break;
            default:
                throw new OffersBoxException("Unknown render type: " . $renderType);
                break;
        }
    }

    /**
     * @param string $nokautUrl
     * @param int $campaignId
     * @param string $template
     * @param null|int $limit
     * @param null|int $limitMin
     * @param null|string $classes
     * @param null|string $content
     * @return string
     */
    private static function renderAjaxDataHook($nokautUrl, $campaignId = 0, $template, $limit = null, $limitMin = null, $classes = null, $content = null)
    {
        $campaignId = (int)$campaignId;

        $context = array(
            'nokaut_url' => $nokautUrl,
            'campaign_id' => $campaignId ? $campaignId : get_the_ID(),
            'template' => $template,
            'hook_id' => md5($nokautUrl . $campaignId . $template . microtime(true) . rand(1, 1000))
        );

        if ($limit !== null) {
            $context['limit'] = $limit;
        }

        if ($limitMin !== null) {
            $context['limit_min'] = $limitMin;
        }

        if ($classes !== null) {
            $context['classes'] = $classes;
        }

        if ($content !== null) {
            $context['content'] = $content;
        }

        return Renderer::render('ajaxDataHook.twig', $context);
    }

    /**
     * @param string $nokautUrl
     * @param int $campaignId
     * @param string $template
     * @param null|int $limit
     * @param null|int $limitMin
     * @param null|string $classes
     * @param bool $ajaxMode
     * @return string
     */
    private static function renderInline($nokautUrl, $campaignId = 0, $template, $limit = null, $limitMin = null, $classes = null, $ajaxMode = false)
    {
        self::$urlMap = array();

        $limitMin = ($limitMin !== null) ? $limitMin : Options::getOption(Options::OPTION_PRODUCTS_FOUND_MIN);
        $limit = ($limit !== null) ? $limit : Options::getOption(Options::OPTION_PRODUCTS_LIMIT);

        if ($limit < $limitMin) {
            $limitMin = $limit;
        }

        $mode = null;
        if (preg_match('@(?<mode>[a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]+)@', $template, $matches)) {
            $mode = $matches['mode'];
        }

        switch ($mode) {
            case 'mall':
                $modeContext = self::contextMallMode($nokautUrl, $limit, $limitMin);
                break;
            case 'product':
                $modeContext = self::contextProductMode($nokautUrl, $limit);
                break;
            default:
                $modeContext = self::contextDefaultMode($nokautUrl, $limit, $limitMin);
                break;
        }

        $context = array_merge(
            array(
                'nokaut_offers_box_plugin_url' => NOKAUT_OFFERS_BOX_PLUGIN_URL,
                'campaign_id' => $campaignId,
                'classes' => self::mapClasses($classes)
            ),
            $modeContext
        );

        $content = Renderer::render('templates/' . $template . '.twig', $context);

        if ($ajaxMode) {
            return $content;
        }

        return self::renderAjaxDataHook($nokautUrl, $campaignId, $template, $limit, $limitMin, $classes, $content);
    }

    /**
     * @param string $nokautUrl
     * @param null|int $limit
     * @param null|int $limitMin
     * @return array
     * @throws OffersBoxException
     */
    private static function contextMallMode($nokautUrl, $limit = null, $limitMin = null)
    {
        $apiKit = ApiKitFactory::getApiKit();
        /** @var ProductsRepository $productsRepository */
        $productsRepository = $apiKit->getProductsRepository();

        $productsFields = array_merge(ProductsRepository::$fieldsForList,
            array('offer_with_minimum_price,offer_with_minimum_price.click_url', 'offer_with_minimum_price.price',
                'offer_with_minimum_price.id', '_categories.url_in', '_categories.url_out')
        );

        $queryUrl = self::getQueryUrl($nokautUrl);
        $products = $productsRepository->fetchProductsWithBestOfferByUrl($queryUrl, $productsFields);

        if (!count($products)) {
            throw new OffersBoxException("Products not found for: " . $nokautUrl . ($queryUrl != $nokautUrl) ? " (" . $queryUrl . ")" : "");
        }

        self::mapProductsUrls($queryUrl, $products);

        $title = self::contextMallTitle($products);

        $filtersCategoriesConverter = new Data\Converter\Filters\CategoriesConverter();
        $filtersCategories = $filtersCategoriesConverter->convert($products, array(
            new Data\Converter\Filters\Callback\Categories\SortByName(),
            new Data\Converter\Filters\Callback\Categories\SetIsNofollow(),
            new Data\Converter\Filters\Callback\Categories\SetIsActive(),
            new Data\Converter\Filters\Callback\Categories\SetIsExcluded(),
            new OffersBoxData\Converter\Filters\Callback\Categories\SortByIsFilter()
        ));

        $filtersSelectedCategoriesConverter = new Data\Converter\Filters\Selected\CategoriesConverter();
        $filtersSelectedCategories = $filtersSelectedCategoriesConverter->convert($products, array(
            new Data\Converter\Filters\Callback\Categories\SetIsNofollow()
        ));

        $filtersProducersConverter = new Data\Converter\Filters\ProducersConverter();
        $filtersProducers = $filtersProducersConverter->convert($products, array(
            new Data\Converter\Filters\Callback\Producers\SortByName(),
            new Data\Converter\Filters\Callback\Producers\SetIsNofollow(),
            new Data\Converter\Filters\Callback\Producers\SetIsActive(),
            new Data\Converter\Filters\Callback\Producers\SetIsExcluded(),
            new Data\Converter\Filters\Callback\Producers\SetIsPopular(),
            new OffersBoxData\Converter\Filters\Callback\Producers\SortByIsFilter()
        ));
        $filtersSelectedProducersConverter = new Data\Converter\Filters\Selected\ProducersConverter();
        $filtersSelectedProducers = $filtersSelectedProducersConverter->convert($products, array(
            new Data\Converter\Filters\Callback\Producers\SetIsNofollow()
        ));

        $filtersShopsConverter = new Data\Converter\Filters\ShopsConverter();
        $filtersShops = $filtersShopsConverter->convert($products, array(
            new Data\Converter\Filters\Callback\Shops\SortByName(),
            new Data\Converter\Filters\Callback\Shops\SetIsNofollow(),
            new Data\Converter\Filters\Callback\Shops\SetIsActive(),
            new Data\Converter\Filters\Callback\Shops\SetIsExcluded(),
            new Data\Converter\Filters\Callback\Shops\SetIsPopular(),
            new OffersBoxData\Converter\Filters\Callback\Shops\SortByIsFilter()
        ));
        $filtersSelectedShopsConverter = new Data\Converter\Filters\Selected\ShopsConverter();
        $filtersSelectedShops = $filtersSelectedShopsConverter->convert($products, array(
            new Data\Converter\Filters\Callback\Shops\SetIsNofollow()
        ));

        $filtersPriceRangesConverter = new Data\Converter\Filters\PriceRangesConverter();
        $filtersPriceRanges = $filtersPriceRangesConverter->convert($products, array(
            new Data\Converter\Filters\Callback\PriceRanges\SetIsNofollow(),
            new OffersBoxData\Converter\Filters\Callback\PriceRanges\SortByIsFilter()
        ));
        $filtersSelectedPriceRangesConverter = new Data\Converter\Filters\Selected\PriceRangesConverter();
        $filtersSelectedPriceRanges = $filtersSelectedPriceRangesConverter->convert($products, array(
                new Data\Converter\Filters\Callback\PriceRanges\SetIsNofollow()
            )
        );

        $filtersPropertiesConverter = new Data\Converter\Filters\PropertiesConverter();
        $filtersProperties = $filtersPropertiesConverter->convert($products, array(
            new Data\Converter\Filters\Callback\Property\SetIsNofollow(),
            new Data\Converter\Filters\Callback\Property\SetIsActive(),
            new Data\Converter\Filters\Callback\Property\SetIsExcluded(),
            new Data\Converter\Filters\Callback\Property\SortDefault(),
            new OffersBoxData\Converter\Filters\Callback\Property\SortByIsFilter()
        ));
        $filtersSelectedPropertiesConverter = new Data\Converter\Filters\Selected\PropertiesConverter();
        $filtersSelectedProperties = $filtersSelectedPropertiesConverter->convert($products, array(
            new Data\Converter\Filters\Callback\Property\SetIsNofollow(),
        ));

        $modeContext = array(
            'products' => $products,
            'title' => $title,
            'filtersCategories' => $filtersCategories,
            'filtersSelectedCategories' => $filtersSelectedCategories,
            'filtersProducers' => $filtersProducers,
            'filtersSelectedProducers' => $filtersSelectedProducers,
            'filtersShops' => $filtersShops,
            'filtersSelectedShops' => $filtersSelectedShops,
            'filtersPriceRanges' => $filtersPriceRanges,
            'filtersSelectedPriceRanges' => $filtersSelectedPriceRanges,
            'filtersProperties' => $filtersProperties,
            'filtersSelectedProperties' => $filtersSelectedProperties,
        );

        return $modeContext;
    }

    public static function contextMallTitle(Products $products)
    {
        $title = '';

        $categories = array();
        foreach ($products->getCategories() as $category) {
            if ($category->getIsFilter() and $category->getTotal()) {
                $categories[] = $category->getName();
            }
        }
        if ($categories) {
            $title .= implode(", ", $categories);
        }

        $shops = array();
        foreach ($products->getShops() as $shop) {
            if ($shop->getIsFilter() and $shop->getTotal()) {
                $shops[] = $shop->getName();
            }
        }
        if ($shops) {
            if (count($shops) == 1) {
                $title .= ' w sklepie ';
            } else {
                $title .= ' w sklepach ';
            }
            $title .= implode(", ", $shops);
        }

        $producers = array();
        foreach ($products->getProducers() as $producer) {
            if ($producer->getIsFilter() and $producer->getTotal()) {
                $producers[] = $producer->getName();
            }
        }
        if ($producers) {
            $title .= " (" . implode(", ", $producers) . ")";
        }

        return $title;
    }

    /**
     * @param string $nokautUrl
     * @param null|int $offersLimit
     * @return array
     * @throws OffersBoxException
     */
    private static function contextProductMode($nokautUrl, $offersLimit = null)
    {
        $apiKit = ApiKitFactory::getApiKit();
        $productsRepository = $apiKit->getProductsRepository();
        $queryUrl = self::getQueryUrl($nokautUrl, true);
        $product = $productsRepository->fetchProductByUrl($queryUrl, ProductsRepository::$fieldsForProductPage);

        if (!$product->getId()) {
            throw new OffersBoxException('Product not found for: ' . $nokautUrl . ($queryUrl != $nokautUrl) ? " (" . $queryUrl . ")" : "");
        }

        $offersRepository = $apiKit->getOffersRepository();
        // we get all offers regardless of the limit
        $offers = $offersRepository->fetchOffersByProductId($product->getId(), OffersRepository::$fieldsForProductPage);

        if (!count($offers)) {
            throw new OffersBoxException('Offers not found for: ' . $nokautUrl . ($queryUrl != $nokautUrl) ? " (" . $queryUrl . ")" : "");
        }

        self::mapProductUrls($queryUrl, $product);
        self::mapOffersUrls($queryUrl, $offers);

        /** @var Offer $offerRecommended */
        $offerRecommended = current($offers->getEntities());

        $offerWithMinPrice = null;
        if (count($offers) > 1) {
            $offersEntities = $offers->getEntities();
            usort($offersEntities, function (Offer $a, Offer $b) {
                if ($a->getPrice() == $b->getPrice()) {
                    return 0;
                }
                return ($a->getPrice() < $b->getPrice()) ? -1 : 1;
            });

            /** @var Offer $firstOffer */
            $firstOffer = reset($offersEntities);
            if ($offerRecommended->getId() != $firstOffer->getId()) {
                $offerWithMinPrice = $firstOffer;
            }
        }

        // limit offers after getting cheapest offer
        if ($offers > $offersLimit) {
            $offers->setEntities(array_slice($offers->getEntities(), 0, $offersLimit));
        }

        $modeContext = array(
            'product' => $product,
            'offers' => $offers,
            'offerRecommended' => $offerRecommended,
            'offerWithMinPrice' => $offerWithMinPrice,
        );

        return $modeContext;
    }

    /**
     * @param string $nokautUrl
     * @param null|int $limit
     * @param null|int $limitMin
     * @return array
     * @throws OffersBoxException
     */
    private static function contextDefaultMode($nokautUrl, $limit = null, $limitMin = null)
    {
        $fields = array_merge(ProductsRepository::$fieldsWithBestOfferForProductBox, array('description_html', 'properties', 'photo_ids'));

        $apiKit = ApiKitFactory::getApiKit();
        $nokautUrls = explode('|', $nokautUrl);
        if (count($nokautUrls) > 1) {
            $productAsyncRepository = $apiKit->getProductsAsyncRepository();
            $productsFetches = array();
            foreach ($nokautUrls as $url) {
                if (preg_match(self::NOKAUT_URL_PARAM_REGEXP, $url, $matches)) {
                    $queryUrl = self::getQueryUrl($matches['nokaut_url'], true);
                    $productsFetches[$queryUrl] = $productAsyncRepository->fetchProductForOffersBox($queryUrl, $fields);
                } else {
                    $queryUrl = self::getQueryUrl($url);
                    $productsFetches[$queryUrl] = $productAsyncRepository->fetchProductsForOffersBox($queryUrl, $fields, 1);
                }
            }
            $productAsyncRepository->fetchAllAsync();
            $products = new Products(array());
            /** @var ProductsFetch $fetch */
            foreach ($productsFetches as $queryUrl => $fetch) {
                try {
                    /** @var Products $productsFromFetch */
                    $productsFromFetch = $fetch->getResult(true);

                    if ($productsFromFetch) {
                        if (!($productsFromFetch instanceof Products)) {
                            $productsFromFetch = new Products(array($productsFromFetch));
                        }

                        self::mapProductsUrls($queryUrl, $productsFromFetch);
                        $products = new Products(array_merge($products->getEntities(), $productsFromFetch->getEntities()));
                    }
                } catch (\Exception $e) {
                }
            }
        } else {
            $productRepository = $apiKit->getProductsRepository();
            if (preg_match(self::NOKAUT_URL_PARAM_REGEXP, $nokautUrl, $matches)) {
                $queryUrl = self::getQueryUrl($matches['nokaut_url'], true);
                $product = $productRepository->fetchProductForOffersBox($queryUrl, $fields);
                $products = new Products(array($product));
            } else {
                $queryUrl = self::getQueryUrl($nokautUrl);
                $products = $productRepository->fetchProductsForOffersBox($queryUrl, $fields, $limit);
            }
            self::mapProductsUrls($queryUrl, $products);
        }

        $productsEntities = array_filter($products->getEntities(), function ($product) {
            /** @var ProductWithBestOffer $product */
            return ($product->getOfferWithBestPrice() && $product->getOfferWithBestPrice()->getPrice() > 0);
        });
        $products->setEntities($productsEntities);

        if (!count($products)) {
            throw new OffersBoxException("Products not found for: " . $nokautUrl);
        }

        if (count($products) > $limit) {
            $products = new Products(array_slice($products->getEntities(), 0, $limit));
        }

        if (count($products) < $limitMin) {
            throw new OffersBoxException("Products min not achieved for: " . $nokautUrl);
        }

        $modeContext = array(
            'products' => $products,
        );

        return $modeContext;
    }

    /**
     * @param string $queryUrl
     * @param Products $products
     */
    private static function mapProductsUrls($queryUrl, Products $products)
    {
        if (isset(self::$urlMap[$queryUrl])) {
            /** @var Product $product */
            foreach ($products as $product) {
                self::mapProductUrls($queryUrl, $product);
            }
        }
    }

    /**
     * @param $queryUrl
     * @param Product $product
     */
    private static function mapProductUrls($queryUrl, Product $product)
    {
        if (isset(self::$urlMap[$queryUrl])) {
            if ($product instanceof ProductWithBestOffer && $product->getOfferWithBestPrice()) {
                /** @var ProductWithBestOffer $product */
                $product->getOfferWithBestPrice()->setClickUrl(self::$urlMap[$queryUrl]);
            }
            $product->setUrl(self::$urlMap[$queryUrl]);
            $product->setClickUrl(self::$urlMap[$queryUrl]);
        }
    }

    /**
     * @param string $queryUrl
     * @param Offers $offers
     */
    private static function mapOffersUrls($queryUrl, Offers $offers)
    {
        if (isset(self::$urlMap[$queryUrl])) {
            /** @var Offer $offer */
            foreach ($offers as $offer) {
                $offer->setUrl(self::$urlMap[$queryUrl]);
                $offer->setClickUrl(self::$urlMap[$queryUrl]);
            }
        }
    }

    /**
     * @param string $url
     * @param bool $productMode
     * @return string
     */
    private static function getQueryUrl($url, $productMode = false)
    {
        $queryUrl = UrlHelper::filterWhiteLabelNokautUrl($url);
        $isUrlFiltered = ($queryUrl != $url);

        if ($productMode) {
            $queryUrl = self::trimProductUrl($queryUrl);
        }

        if ($isUrlFiltered) {
            $url = preg_replace('/##/', '', $url);
            self::$urlMap[$queryUrl] = $url;
        }

        return $queryUrl;
    }

    /**
     * @param string $url
     * @return string
     */
    private static function trimProductUrl($url)
    {
        $url = trim($url, '/');
        return preg_replace("/.html/", '', $url);
    }

    /**
     * @param null|string $classes
     * @return array
     */
    private static function mapClasses($classes)
    {
        if (!$classes) {
            $classes = array();
        } else {
            $classes = explode('|', $classes);
        }
        foreach ($classes as $key => $value) {
            if (isset(self::$classMap[$value])) {
                $classes[$key] = self::$classMap[$value];
            }
        }

        return $classes;
    }
}
