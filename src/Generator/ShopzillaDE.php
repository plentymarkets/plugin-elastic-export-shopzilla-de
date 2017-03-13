<?php

namespace ElasticExportShopzillaDE\Generator;

use Plenty\Modules\DataExchange\Contracts\CSVPluginGenerator;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\DataExchange\Models\FormatSetting;
use ElasticExport\Helper\ElasticExportCoreHelper;
use Plenty\Modules\Helper\Models\KeyValue;
use Plenty\Modules\Item\Property\Contracts\PropertySelectionRepositoryContract;
use Plenty\Modules\Item\Property\Models\PropertySelection;
use Plenty\Modules\Helper\Contracts\UrlBuilderRepositoryContract;


class ShopzillaDE extends CSVPluginGenerator
{
    /**
     * @var ElasticExportCoreHelper $elasticExportHelper
     */
    private $elasticExportHelper;

    /**
     * @var ArrayHelper
     */
    private $arrayHelper;

    /**
     * @var array $idlVariations
     */
    private $idlVariations;

    /**
     * Shopzilla constructor.
     * @param ArrayHelper $arrayHelper
     */
    public function __construct(ArrayHelper $arrayHelper)
    {
        $this->arrayHelper = $arrayHelper;
    }

    /**
     * @param mixed $resultData
     * @param array $formatSettings
     * @param array $filter
     */
    protected function generatePluginContent($resultData, array $formatSettings = [], array $filter = [])
    {
        $this->elasticExportHelper = pluginApp(ElasticExportCoreHelper::class);
        if(is_array($resultData['documents']) && count($resultData['documents']) > 0)
        {
            $settings = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');

            $this->setDelimiter(" ");

            $this->addCSVContent([
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
            ]);

            //Create a List of all VariationIds
            $variationIdList = array();
            foreach($resultData['documents'] as $variation)
            {
                $variationIdList[] = $variation['id'];
            }

            //Get the missing fields in ES from IDL
            if(is_array($variationIdList) && count($variationIdList) > 0)
            {
                /**
                 * @var \ElasticExportShopzillaDE\IDL_ResultList\ShopzillaDE $idlResultList
                 */
                $idlResultList = pluginApp(\ElasticExportShopzillaDE\IDL_ResultList\ShopzillaDE::class);
                $idlResultList = $idlResultList->getResultList($variationIdList, $settings);
            }

            //Creates an array with the variationId as key to surpass the sorting problem
            if(isset($idlResultList) && $idlResultList instanceof RecordList)
            {
                $this->createIdlArray($idlResultList);
            }

            foreach($resultData['documents'] as $item)
            {
                $deliveryCost = $this->elasticExportHelper->getShippingCost($item['data']['item']['id'], $settings);
                if(!is_null($deliveryCost))
                {
                    $deliveryCost = number_format((float)$deliveryCost, 2, ',', '');
                }
                else
                {
                    $deliveryCost = '';
                }

                $data = [
                    'Kategorie' 		=> $this->elasticExportHelper->getCategory((int)$item['data']['defaultCategories'][0]['id'], $settings->get('lang'), $settings->get('plentyId')),
                    'Hersteller' 		=> $this->elasticExportHelper->getExternalManufacturerName((int)$item['data']['item']['manufacturer']['id']),
                    'Bezeichnung' 		=> $this->elasticExportHelper->getName($item, $settings, 256),
                    'Beschreibung' 		=> $this->elasticExportHelper->getDescription($item, $settings, 256),
                    'Artikel-URL' 		=> $this->elasticExportHelper->getUrl($item, $settings, true, false),
                    'Bild-URL' 			=> $this->elasticExportHelper->getMainImage($item, $settings),
                    'SKU' 				=> $item['data']['item']['id'],
                    'Bestand' 			=> 'Auf Lager',
                    'Versandgewicht' 	=> $item['data']['variation']['weightG'],
                    'Zustand' 			=> 'Neu',
                    'Versandkosten' 	=> $deliveryCost,
                    'Gebot' 			=> '',
                    'Werbetext' 		=> '2',
                    'EAN' 				=> $this->elasticExportHelper->getBarcodeByType($item, $settings->get('barcode')),
                    'Preis' 			=> number_format((float)$this->idlVariations[$item['id']]['variationRetailPrice.price'], 2, '.', ''),
                    'Grundpreis' 		=> $this->elasticExportHelper->getBasePrice($item, $this->idlVariations[$item['id']]),
                ];

                $this->addCSVContent(array_values($data));
            }
        }
    }

    /**
     * @param RecordList $idlResultList
     */
    private function createIdlArray($idlResultList)
    {
        if($idlResultList instanceof RecordList)
        {
            foreach($idlResultList as $idlVariation)
            {
                if($idlVariation instanceof Record)
                {
                    $this->idlVariations[$idlVariation->variationBase->id] = [
                        'itemBase.id' => $idlVariation->itemBase->id,
                        'variationBase.id' => $idlVariation->variationBase->id,
                        'variationRetailPrice.price' => $idlVariation->variationRetailPrice->price,
                    ];
                }
            }
        }
    }
}