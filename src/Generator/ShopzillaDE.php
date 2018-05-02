<?php

namespace ElasticExportShopzillaDE\Generator;

use ElasticExport\Helper\ElasticExportPriceHelper;
use ElasticExport\Helper\ElasticExportPropertyHelper;
use ElasticExport\Helper\ElasticExportStockHelper;
use ElasticExport\Services\FiltrationService;
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

    const PROPERTY_TYPE_GENDER		= 'gender';
    const PROPERTY_TYPE_AGE_GROUP	= 'age_group';

    const PROPERTY_TYPE_COLOR		= 'color';
    const PROPERTY_TYPE_SIZE		= 'size';
    const PROPERTY_TYPE_PATTERN		= 'pattern';
    const PROPERTY_TYPE_MATERIAL	= 'material';

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
     * @var FiltrationService
     */
    private $filtrationService;

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
        $this->filtrationService = pluginApp(FiltrationService::class, ['settings' => $settings, 'filterSettings' => $filter]);

        $this->attributeHelper->loadLinkedAttributeList($settings);

        $this->setDelimiter(self::DELIMITER);

        $this->addCSVContent($this->head());

        if($elasticSearch instanceof VariationElasticSearchScrollRepositoryContract)
        {
            // Initiate the counter for the variations limit
            $limitReached = false;
            $limit = 0;

            do
            {
                // Stop writing if limit is reached
                if($limitReached === true)
                {
                    break;
                }

                // Get the data from Elastic Search
                $resultList = $elasticSearch->execute();

                if(count($resultList['error']) > 0)
                {
                    $this->getLogger(__METHOD__)->error('ElasticExportShopzillaDE::logs.occurredElasticSearchErrors', [
                        'Error message' => $resultList['error'],
                    ]);

                    break;
                }

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
                        if ($this->filtrationService->filter($variation))
                        {
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
                }

            } while ($elasticSearch->hasNext());
        }
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

        // Only variations with the Retail Price greater than zero will be handled
        if(!is_null($priceList['price']) && (float)$priceList['price'] > 0)
        {
            // Get shipping cost
            $shippingCost = $this->getShippingCost($variation);

            $imageList = $this->elasticExportHelper->getImageListInOrder($variation, $settings, 11, 'variationImages');

            $variationAttributes = $this->attributeHelper->getVariationAttributes($variation);

            $data = [
                'ID'                    => (string)$this->elasticExportHelper->generateSku($variation['id'], (float)$settings->get('referrerId'), 0, (string)$variation['data']['skus'][0]['sku']),
                'Titel'                 => $this->elasticExportHelper->getMutatedName($variation, $settings),
                'Beschreibung'          => $this->elasticExportHelper->getMutatedDescription($variation, $settings),
                'Kategorie'             => $this->elasticExportHelper->getCategory((int)$variation['data']['defaultCategories'][0]['id'], $settings->get('lang'), $settings->get('plentyId')),
                'Artikel-URL'           => $this->elasticExportHelper->getMutatedUrl($variation, $settings, true, false),
                'Bild-URL'              => isset($imageList[0]) ? $imageList[0] : '',
                'Zusätzliche Bild-URL'  => $this->getAdditionalImages($imageList),
                'Zustand'               => $this->getCondition((int)$variation['data']['item']['conditionApi']['id']),
                'Bestand'               => $this->elasticExportHelper->getAvailability($variation, $settings, false),
                'Marke'                 => $this->elasticExportHelper->getExternalManufacturerName((int)$variation['data']['item']['manufacturer']['id']),
                'EAN'                   => $this->elasticExportHelper->getBarcodeByType($variation, $settings->get('barcode')),
                'Artikelnummer'         => (string)$variation['data']['variation']['model'],
                'Versandkosten'         => $shippingCost,
                'Geschlecht'            => $this->elasticExportPropertyHelper->getProperty($variation, self::PROPERTY_TYPE_GENDER, self::SHOPZILLA_DE),
                'Altersgruppe'          => $this->elasticExportPropertyHelper->getProperty($variation, self::PROPERTY_TYPE_AGE_GROUP, self::SHOPZILLA_DE),
                'Größe'                 => $variationAttributes[self::PROPERTY_TYPE_SIZE],
                'Farbe'                 => $variationAttributes[self::PROPERTY_TYPE_COLOR],
                'Material'              => $variationAttributes[self::PROPERTY_TYPE_MATERIAL],
                'Muster'                => $variationAttributes[self::PROPERTY_TYPE_PATTERN],
                'Produktgruppe'         => (int)$variation['data']['item']['id'],
                'Grundpreis'            => $this->elasticExportPriceHelper->getBasePrice($variation, (float)$priceList['price'], $settings->get('lang')),
                'Empfohlener Preis'     => $priceList['recommendedRetailPrice'] > $priceList['price'] ? $priceList['recommendedRetailPrice'] : '',
                'Preis'                 => $priceList['price'],
            ];

            $this->addCSVContent(array_values($data));
        }
    }

    /**
     * Get the condition of a variation.
     *
     * @param int $conditionId
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
     * Get the shipping cost.
     *
     * @param $variation
     * @return string
     */
    private function getShippingCost($variation):string
    {
        $shippingCost = null;
        if(isset($this->shippingCostCache) && array_key_exists($variation['data']['item']['id'], $this->shippingCostCache))
        {
            $shippingCost = $this->shippingCostCache[$variation['data']['item']['id']];
        }

        if(!is_null($shippingCost) && $shippingCost > 0)
        {
            return number_format((float)$shippingCost, 2, ',', '');
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