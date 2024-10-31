<?php


namespace ApiKitNokautOffersBox\Ext\Data\Converter\Filters\Callback\Property;


use ApiKitNokautOffersBox\Ext\Data\Collection\Filters\Sort\SortByIsFilter as OffersBoxSortByIsFilter;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Ext\Data\Collection\Filters\PropertyAbstract;
use Nokaut\ApiKit\Ext\Data\Converter\Filters\Callback\Property\CallbackInterface;

class SortByIsFilter implements CallbackInterface
{
    /**
     * @param PropertyAbstract $property
     * @param Products $products
     */
    public function __invoke(PropertyAbstract $property, Products $products)
    {
        OffersBoxSortByIsFilter::sort($property);
    }
}