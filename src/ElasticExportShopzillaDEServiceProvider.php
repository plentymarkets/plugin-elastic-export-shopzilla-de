<?php

namespace ElasticExportShopzillaDE;

use Plenty\Modules\DataExchange\Services\ExportPresetContainer;
use Plenty\Plugin\DataExchangeServiceProvider;

class ElasticExportShopzillaDEServiceProvider extends DataExchangeServiceProvider
{
    public function register()
    {

    }

    public function exports(ExportPresetContainer $container)
    {
        $container->add(
            'ShopzillaDE-Plugin',
            'ElasticExportShopzillaDE\ResultField\ShopzillaDE',
            'ElasticExportShopzillaDE\Generator\ShopzillaDE',
            '',
            true
        );
    }
}