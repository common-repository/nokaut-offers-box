<?php


namespace ApiKitNokautOffersBox\Ext\Data\Converter\Filters\Callback\Producers;


use ApiKitNokautOffersBox\Ext\Data\Collection\Filters\Sort\SortByIsFilter as OffersBoxSortByIsFilter;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Ext\Data\Collection\Filters\Producers;
use Nokaut\ApiKit\Ext\Data\Converter\Filters\Callback\Producers\CallbackInterface;

class SortByIsFilter implements CallbackInterface
{
    /**
     * @param Producers $shops
     * @param Products $products
     */
    public function __invoke(Producers $shops, Products $products)
    {
        OffersBoxSortByIsFilter::sort($shops);
    }
}