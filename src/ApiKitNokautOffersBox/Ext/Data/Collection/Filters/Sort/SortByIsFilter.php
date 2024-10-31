<?php


namespace ApiKitNokautOffersBox\Ext\Data\Collection\Filters\Sort;


use Nokaut\ApiKit\Ext\Data\Collection\Filters\FiltersAbstract;
use Nokaut\ApiKit\Ext\Data\Collection\Filters\Sort\SortInterface;
use Nokaut\ApiKit\Ext\Data\Entity\Filter\FilterAbstract;

class SortByIsFilter implements SortInterface
{
    public static function sort(FiltersAbstract $collection)
    {
        $entities = $collection->getEntities();

        usort($entities, function ($entity1, $entity2) {
            /** @var FilterAbstract $entity1 */
            /** @var FilterAbstract $entity2 */
            if ($entity1->getIsFilter() == $entity2->getIsFilter()) {
                return 0;
            }
            return ($entity1->getIsFilter() < $entity2->getIsFilter()) ? 1 : -1;
        });

        $collection->setEntities($entities);
    }
}