<?php


namespace ApiKitNokautOffersBox\Ext\Data\Converter\Filters\Callback\Categories;


use ApiKitNokautOffersBox\Ext\Data\Collection\Filters\Sort\SortByIsFilter as OffersBoxSortByIsFilter;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Ext\Data\Collection\Filters\Categories;
use Nokaut\ApiKit\Ext\Data\Converter\Filters\Callback\Categories\CallbackInterface;

class SortByIsFilter implements CallbackInterface
{
    /**
     * @param Categories $categories
     * @param Products $products
     */
    public function __invoke(Categories $categories, Products $products)
    {
        OffersBoxSortByIsFilter::sort($categories);
    }
}