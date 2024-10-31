<?php


namespace ApiKitNokautOffersBox\Ext\Data\Converter\Filters\Callback\PriceRanges;


use ApiKitNokautOffersBox\Ext\Data\Collection\Filters\Sort\SortByIsFilter as OffersBoxSortByIsFilter;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Ext\Data\Collection\Filters\PriceRanges;
use Nokaut\ApiKit\Ext\Data\Converter\Filters\Callback\PriceRanges\CallbackInterface;

class SortByIsFilter implements CallbackInterface
{
    /**
     * @param PriceRanges $priceRanges
     * @param Products $products
     */
    public function __invoke(PriceRanges $priceRanges, Products $products)
    {
        OffersBoxSortByIsFilter::sort($priceRanges);
    }
}