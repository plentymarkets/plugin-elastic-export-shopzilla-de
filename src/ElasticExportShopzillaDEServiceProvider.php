<?php

namespace ElasticExportShopzillaDE;

use Plenty\Modules\DataExchange\Services\ExportPresetContainer;
use Plenty\Plugin\DataExchangeServiceProvider;

/**
 * Class ElasticExportShopzillaDEServiceProvider
 * @package ElasticExportShopzillaDE
 */
class ElasticExportShopzillaDEServiceProvider extends DataExchangeServiceProvider
{
    /**
     * Abstract function definition for registering the service provider.
     */
    public function register()
    {

    }

    /**
     * Adds the export format to the export container.
     *
     * @param ExportPresetContainer $container
     */
    public function exports(ExportPresetContainer $container)
    {
        $container->add(
            'ShopzillaDE-Plugin',
            'ElasticExportShopzillaDE\ResultField\ShopzillaDE',
            'ElasticExportShopzillaDE\Generator\ShopzillaDE',
            '',
            true,
            true
        );
    }
}