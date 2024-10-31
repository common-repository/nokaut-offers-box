<?php


namespace ApiKitNokautOffersBox\Repository;

use Nokaut\ApiKit\ClientApi\Rest\Fetch\Fetch;
use Nokaut\ApiKit\ClientApi\Rest\Query\Filter\Single;
use Nokaut\ApiKit\ClientApi\Rest\Query\ProductsQuery;
use Nokaut\ApiKit\Converter\ProductsWithBestOfferConverter;
use Nokaut\ApiKit\Converter\ProductWithBestOfferConverter;
use Nokaut\ApiKit\Entity\Product;

class ProductsAsyncRepository extends \Nokaut\ApiKit\Repository\ProductsAsyncRepository
{
    /**
     * @param $url
     * @param array $fields
     * @param int $limit
     * @return Fetch
     */
    public function fetchProductsForOffersBox($url, array $fields, $limit = 20)
    {
        $query = $this->prepareQueryForOffersBox($url, $fields, $limit);
        $asyncFetch = new Fetch($query, new ProductsWithBestOfferConverter(), $this->cache);
        $this->asyncRepo->addFetch($asyncFetch);
        return $asyncFetch;
    }

    /**
     * @param string $url
     * @param array $fields
     * @return Product
     */
    public function fetchProductForOffersBox($url, array $fields)
    {
        $query = $this->prepareQueryForFetchProductByUrl($url, $fields);
        $asyncFetch = new Fetch($query, new ProductWithBestOfferConverter(), $this->cache);
        $this->asyncRepo->addFetch($asyncFetch);
        return $asyncFetch;
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