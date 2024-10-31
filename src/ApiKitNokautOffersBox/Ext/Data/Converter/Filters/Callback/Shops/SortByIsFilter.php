<?php


namespace ApiKitNokautOffersBox\Ext\Data\Converter\Filters\Callback\Shops;


use ApiKitNokautOffersBox\Ext\Data\Collection\Filters\Sort\SortByIsFilter as OffersBoxSortByIsFilter;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Ext\Data\Collection\Filters\Shops;
use Nokaut\ApiKit\Ext\Data\Converter\Filters\Callback\Shops\CallbackInterface;

class SortByIsFilter implements CallbackInterface
{
    /**
     * @param Shops $shops
     * @param Products $products
     */
    public function __invoke(Shops $shops, Products $products)
    {
        OffersBoxSortByIsFilter::sort($shops);
    }
}