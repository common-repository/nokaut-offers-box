<?php


namespace ApiKitNokautOffersBox\Repository;

use Nokaut\ApiKit\ClientApi\Rest\Fetch\Fetch;
use Nokaut\ApiKit\ClientApi\Rest\Query\Filter\Single;
use Nokaut\ApiKit\ClientApi\Rest\Query\ProductsQuery;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Converter\ProductsWithBestOfferConverter;
use Nokaut\ApiKit\Converter\ProductWithBestOfferConverter;
use Nokaut\ApiKit\Entity\Product;

class ProductsRepository extends \Nokaut\ApiKit\Repository\ProductsRepository
{
    /**
     * @param $url
     * @param array $fields
     * @param int $limit
     * @return Products - return collection of ProductWithBestOffer
     */
    public function fetchProductsForOffersBox($url, array $fields, $limit = 20)
    {
        $query = $this->prepareQueryForOffersBox($url, $fields, $limit);
        $fetch = new Fetch($query, new ProductsWithBestOfferConverter(), $this->cache);
        $this->clientApi->send($fetch);
        return $fetch->getResult();
    }

    /**
     * @param string $url
     * @param array $fields
     * @return Product
     */
    public function fetchProductForOffersBox($url, array $fields)
    {
        $query = $this->prepareQueryForFetchProductByUrl($url, $fields);
        $fetch = new Fetch($query, new ProductWithBestOfferConverter(), $this->cache);
        $this->clientApi->send($fetch);
        return $fetch->getResult();
    }

    /**
     * @param $url
     * @param array $fields
     * @param $limit
     * @return ProductsQuery
     */
    protected function prepareQueryForOffersBox($url, array $fields, $limit)
    {
        $query = new ProductsQuery($this->apiBaseUrl);
        $query->setFields($fields);
        $query->addFilter(new Single('url', $url));
        $query->addFacet('query', false);
        $query->setLimit($limit);
        return $query;
    }
}