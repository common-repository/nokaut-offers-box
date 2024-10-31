<?php

namespace ApiKitNokautOffersBox;

use ApiKitNokautOffersBox\Repository\ProductsAsyncRepository;
use ApiKitNokautOffersBox\Repository\ProductsRepository;
use Nokaut\ApiKit\ApiKit;
use Nokaut\ApiKit\Config;

class ApiKitNokautOffersBox extends ApiKit
{
    /**
     * @param Config $config
     * @return ProductsRepository
     */
    public function getProductsRepository(Config $config = null)
    {
        if (!$config) {
            $config = $this->config;
        }
        $this->validate($config);

        $restClientApi = $this->getClientApi($config);

        return new ProductsRepository($config, $restClientApi);
    }

    /**
     * @param Config $config
     * @return ProductsAsyncRepository
     */
    public function getProductsAsyncRepository(Config $config = null)
    {
        if (!$config) {
            $config = $this->config;
        }
        $this->validate($config);

        $restClientApi = $this->getClientApi($config);

        return new ProductsAsyncRepository($config, $restClientApi);
    }
}