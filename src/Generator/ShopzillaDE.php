<?php

namespace ElasticExportShopzillaDE\Generator;

use ElasticExport\Helper\ElasticExportPriceHelper;
use ElasticExport\Helper\ElasticExportPropertyHelper;
use ElasticExport\Helper\ElasticExportStockHelper;
use ElasticExportShopzillaDE\Helper\AttributeHelper;
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

    const DELIMITER = "\t";

    const SHOPZILLA_DE              = 105.00;

    const CHARACTER_TYPE_GENDER		= 'gender';
    const CHARACTER_TYPE_AGE_GROUP	= 'age_group';

    const CHARACTER_TYPE_COLOR		= 'color';
    const CHARACTER_TYPE_SIZE		= 'size';
    const CHARACTER_TYPE_PATTERN	= 'pattern';
    const CHARACTER_TYPE_MATERIAL	= 'material';

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
     * @var ElasticExportPropertyHelper
     */
    private $elasticExportPropertyHelper;

    /**
     * @var ArrayHelper
     */
    private $arrayHelper;

    /**
     * @var AttributeHelper
     */
    private $attributeHelper;

    /**
     * @var array
     */
    private $shippingCostCache;

    /**
     * ShopzillaDE constructor.
     *
     * @param ArrayHelper $arrayHelper
     * @param AttributeHelper $attributeHelper
     */
    public function __construct(ArrayHelper $arrayHelper, AttributeHelper $attributeHelper)
    {
        $this->arrayHelper = $arrayHelper;
        $this->attributeHelper = $attributeHelper;
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

        $this->elasticExportPropertyHelper = pluginApp(ElasticExportPropertyHelper::class);

        $settings = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');

        $this->attributeHelper->loadLinkedAttributeList($settings);

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
            'ID',
            'Titel',
            'Beschreibung',
            'Kategorie',
            'Artikel-URL',
            'Bild-URL',
            'Zusätzliche Bild-URL',
            'Zustand',
            'Bestand',
            'Marke',
            'EAN',
            'Artikelnummer',
            'Versandkosten',
            'Geschlecht',
            'Altersgruppe',
            'Größe',
            'Farbe',
            'Material',
            'Muster',
            'Produktgruppe',
            'Grundpreis',
            'Empfohlener Preis',
            'Preis',
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

        $imageList = $this->elasticExportHelper->getImageListInOrder($variation, $settings, 11, 'variationImages');

        $variationAttributes = $this->attributeHelper->getVariationAttributes($variation);

        // Only variations with the Retail Price greater than zero will be handled
        if(!is_null($priceList['price']) && (float)$priceList['price'] > 0)
        {
            $data = [
                'ID'                    => (string)$this->elasticExportHelper->generateSku($variation['id'], (float)$settings->get('referrerId'), 0, (string)$variation['data']['skus'][0]['sku']),
                'Titel'                 => $this->elasticExportHelper->getMutatedName($variation, $settings),
                'Beschreibung'          => $this->elasticExportHelper->getMutatedDescription($variation, $settings),
                'Kategorie'             => $this->elasticExportHelper->getCategory((int)$variation['data']['defaultCategories'][0]['id'], $settings->get('lang'), $settings->get('plentyId')),
                'Artikel-URL'           => $this->elasticExportHelper->getMutatedUrl($variation, $settings, true, false),
                'Bild-URL'              => $imageList[0],
                'Zusätzliche Bild-URL'  => $this->getAdditionalImages($imageList),
                'Zustand'               => $this->getCondition((int)$variation['data']['item']['conditionApi']['id']),
                'Bestand'               => $this->elasticExportHelper->getAvailability($variation, $settings, false),
                'Marke'                 => $this->elasticExportHelper->getExternalManufacturerName((int)$variation['data']['item']['manufacturer']['id']),
                'EAN'                   => $this->elasticExportHelper->getBarcodeByType($variation, $settings->get('barcode')),
                'Artikelnummer'         => (string)$variation['data']['variation']['model'],
                'Versandkosten'         => $this->elasticExportHelper->getShippingCost($variation['data']['item']['id'], $settings),
                'Geschlecht'            => $this->elasticExportPropertyHelper->getProperty($variation, self::CHARACTER_TYPE_GENDER, self::SHOPZILLA_DE),
                'Altersgruppe'          => $this->elasticExportPropertyHelper->getProperty($variation, self::CHARACTER_TYPE_AGE_GROUP, self::SHOPZILLA_DE),
                'Größe'                 => $variationAttributes[self::CHARACTER_TYPE_SIZE],
                'Farbe'                 => $variationAttributes[self::CHARACTER_TYPE_COLOR],
                'Material'              => $variationAttributes[self::CHARACTER_TYPE_MATERIAL],
                'Muster'                => $variationAttributes[self::CHARACTER_TYPE_PATTERN],
                'Produktgruppe'         => (int)$variation['data']['item']['id'],
                'Grundpreis'            => $this->elasticExportPriceHelper->getBasePrice($variation, (float)$priceList['price'], $settings->get('lang')),
                'Empfohlener Preis'     => $priceList['recommendedRetailPrice'],
                'Preis'                 => $priceList['price'],
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
     * @param int       $conditionId
     * @return string
     */
    private function getCondition(int $conditionId):string
    {
        switch($conditionId)
        {
            case 0:
                return 'Neu';
            default:
                return 'Gebraucht';
        }
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
            $this->shippingCostCache[$variation['data']['item']['id']] = (float)$shippingCost;
        }
    }

    /**
     * Returns a string of all additional picture-URLs separated by ","
     *
     * @param array $imageList
     * @return string
     */
    private function getAdditionalImages(array $imageList):string
    {
        $imageListString = '';

        unset($imageList[0]);

        if(count($imageList))
        {
            $imageListString = implode(',', $imageList);
        }

        return $imageListString;
    }
}