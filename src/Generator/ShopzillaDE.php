<?php

namespace ElasticExportShopzillaDE\Generator;

use ElasticExport\Helper\ElasticExportPriceHelper;
use ElasticExport\Helper\ElasticExportStockHelper;
use Plenty\Modules\DataExchange\Contracts\CSVPluginGenerator;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\DataExchange\Models\FormatSetting;
use ElasticExport\Helper\ElasticExportCoreHelper;
use Plenty\Modules\Helper\Models\KeyValue;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ShopzillaDE
 * @package ElasticExportShopzillaDE\Generator
 */
class ShopzillaDE extends CSVPluginGenerator
{
    use Loggable;

    const DELIMITER = " "; // SPACE

    /**
     * @var ElasticExportCoreHelper
     */
    private $elasticExportHelper;

    /**
     * @var ElasticExportStockHelper
     */
    private $elasticExportStockHelper;

    /**
     * @var ElasticExportPriceHelper
     */
    private $elasticExportPriceHelper;

    /**
     * @var ArrayHelper
     */
    private $arrayHelper;

    /**
     * @var array
     */
    private $shippingCostCache;

    /**
     * ShopzillaDE constructor.
     *
     * @param ArrayHelper $arrayHelper
     */
    public function __construct(ArrayHelper $arrayHelper)
    {
        $this->arrayHelper = $arrayHelper;
    }

    /**
     * Generates and populates the data into the CSV file.
     *
     * @param VariationElasticSearchScrollRepositoryContract $elasticSearch
     * @param array $formatSettings
     * @param array $filter
     */
    protected function generatePluginContent($elasticSearch, array $formatSettings = [], array $filter = [])
    {
        $this->elasticExportHelper = pluginApp(ElasticExportCoreHelper::class);

        $this->elasticExportStockHelper = pluginApp(ElasticExportStockHelper::class);

        $this->elasticExportPriceHelper = pluginApp(ElasticExportPriceHelper::class);

        $settings = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');

        $this->setDelimiter(self::DELIMITER);

        $this->addCSVContent($this->head());

        $startTime = microtime(true);

        if($elasticSearch instanceof VariationElasticSearchScrollRepositoryContract)
        {
            // Initiate the counter for the variations limit
            $limitReached = false;
            $limit = 0;

            do
            {
                // Current number of lines written
                $this->getLogger(__METHOD__)->debug('ElasticExportShopzillaDE::logs.writtenLines', [
                    'Lines written' => $limit,
                ]);

                // Stop writing if limit is reached
                if($limitReached === true)
                {
                    break;
                }

                $esStartTime = microtime(true);

                // Get the data from Elastic Search
                $resultList = $elasticSearch->execute();

                $this->getLogger(__METHOD__)->debug('ElasticExportShopzillaDE::logs.esDuration', [
                    'Elastic Search duration' => microtime(true) - $esStartTime,
                ]);

                if(count($resultList['error']) > 0)
                {
                    $this->getLogger(__METHOD__)->error('ElasticExportShopzillaDE::logs.occurredElasticSearchErrors', [
                        'Error message' => $resultList['error'],
                    ]);

                    break;
                }

                $buildRowStartTime = microtime(true);

                if(is_array($resultList['documents']) && count($resultList['documents']) > 0)
                {
                    $previousItemId = null;

                    foreach($resultList['documents'] as $variation)
                    {
                        // Stop and set the flag if limit is reached
                        if($limit == $filter['limit'])
                        {
                            $limitReached = true;
                            break;
                        }

                        // If filtered by stock is set and stock is negative, then skip the variation
                        if ($this->elasticExportStockHelper->isFilteredByStock($variation, $filter) === true)
                        {
                            $this->getLogger(__METHOD__)->info('ElasticExportShopzillaDE::logs.variationNotPartOfExportStock', [
                                'VariationId' => $variation['id']
                            ]);

                            continue;
                        }

                        try
                        {
                            // Set the caches if we have the first variation or when we have the first variation of an item
                            if($previousItemId === null || $previousItemId != $variation['data']['item']['id'])
                            {
                                $previousItemId = $variation['data']['item']['id'];
                                unset($this->shippingCostCache);

                                // Build the caches arrays
                                $this->buildCaches($variation, $settings);
                            }

                            // Build the new row for printing in the CSV file
                            $this->buildRow($variation, $settings);
                        }
                        catch(\Throwable $throwable)
                        {
                            $this->getLogger(__METHOD__)->error('ElasticExportShopzillaDE::logs.fillRowError', [
                                'Error message ' => $throwable->getMessage(),
                                'Error line'     => $throwable->getLine(),
                                'VariationId'    => $variation['id']
                            ]);
                        }

                        // New line was added
                        $limit++;
                    }

                    $this->getLogger(__METHOD__)->debug('ElasticExportShopzillaDE::logs.buildRowDuration', [
                        'Build rows duration' => microtime(true) - $buildRowStartTime,
                    ]);
                }

            } while ($elasticSearch->hasNext());
        }

        $this->getLogger(__METHOD__)->debug('ElasticExportShopzillaDE::logs.fileGenerationDuration', [
            'Whole file generation duration' => microtime(true) - $startTime,
        ]);
    }

    /**
     * Creates the header of the CSV file.
     *
     * @return array
     */
    private function head():array
    {
        return array(
            'Kategorie',
            'Hersteller',
            'Bezeichnung',
            'Beschreibung',
            'Artikel-URL',
            'Bild-URL',
            'SKU',
            'Bestand',
            'Versandgewicht',
            'Zustand',
            'Versandkosten',
            'Gebot',
            'Werbetext',
            'EAN',
            'Preis',
            'Grundpreis',
        );
    }

    /**
     * Creates the variation row and prints it into the CSV file.
     *
     * @param array $variation
     * @param KeyValue $settings
     */
    private function buildRow($variation, KeyValue $settings)
    {
        // Get the price list
        $priceList = $this->elasticExportPriceHelper->getPriceList($variation, $settings);

        // Only variations with the Retail Price greater than zero will be handled
        if(!is_null($priceList['price']) && (float)$priceList['price'] > 0)
        {
            $data = [
                'Kategorie'         => $this->elasticExportHelper->getCategory((int)$variation['data']['defaultCategories'][0]['id'], $settings->get('lang'), $settings->get('plentyId')),
                'Hersteller'        => $this->elasticExportHelper->getExternalManufacturerName((int)$variation['data']['item']['manufacturer']['id']),
                'Bezeichnung'       => $this->elasticExportHelper->getMutatedName($variation, $settings, 256),
                'Beschreibung'      => $this->elasticExportHelper->getMutatedDescription($variation, $settings, 256),
                'Artikel-URL'       => $this->elasticExportHelper->getMutatedUrl($variation, $settings, true, false),
                'Bild-URL'          => $this->elasticExportHelper->getMainImage($variation, $settings),
                'SKU'               => (string)$this->elasticExportHelper->generateSku($variation['id'], (float)$settings->get('referrerId'), 0, (string)$variation['data']['skus'][0]['sku']),
                'Bestand'           => 'Auf Lager',
                'Versandgewicht'    => $variation['data']['variation']['weightG'],
                'Zustand'           => 'Neu',
                'Versandkosten'     => $this->getShippingCost($variation),
                'Gebot'             => '',
                'Werbetext'         => '2',
                'EAN'               => $this->elasticExportHelper->getBarcodeByType($variation, $settings->get('barcode')),
                'Preis'             => $priceList['price'],
                'Grundpreis'        => $this->elasticExportPriceHelper->getBasePrice($variation, (float)$priceList['price'], $settings->get('lang')),
            ];

            $this->addCSVContent(array_values($data));
        }
        else
        {
            $this->getLogger(__METHOD__)->info('ElasticExportShopzillaDE::logs.variationNotPartOfExportPrice', [
                'VariationId' => $variation['id']
            ]);
        }
    }

    /**
     * Get the shipping cost.
     *
     * @param array $variation
     * @return string
     */
    private function getShippingCost($variation):string
    {
        $shippingCost = null;
        if(isset($this->shippingCostCache) && array_key_exists($variation['data']['item']['id'], $this->shippingCostCache))
        {
            $shippingCost = $this->shippingCostCache[$variation['data']['item']['id']];
        }

        if(!is_null($shippingCost) && $shippingCost != '0.00')
        {
            return $shippingCost;
        }

        return '';
    }

    /**
     * Build the cache array for the item variation.
     *
     * @param array $variation
     * @param KeyValue $settings
     */
    private function buildCaches($variation, $settings)
    {
        if(!is_null($variation) && !is_null($variation['data']['item']['id']))
        {
            $shippingCost = $this->elasticExportHelper->getShippingCost($variation['data']['item']['id'], $settings, 0);
            $this->shippingCostCache[$variation['data']['item']['id']] = number_format((float)$shippingCost, 2, ',', '');
        }
    }
}