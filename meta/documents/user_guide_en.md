
# ElasticExportShopzillaDE plugin user guide

<div class="container-toc"></div>

## 1 Registering with Shopzilla.de

Shopzilla is a price comparison platform.

## 2 Setting up the data format ShopzillaDE-Plugin in plentymarkets

By installing this plugin you will receive the export format **ShopzillaDE-Plugin**. Use this format to exchange data between plentymarkets and Shopzilla. It is required to install the Plugin Elastic export from the plentyMarketplace first before you can use the format **ShopzillaDE-Plugin** in plentymarkets.

Once both plugins are installed, you can create the export format **ShopzillaDE-Plugin**. Refer to the [Exporting data formats for price search engines](https://knowledge.plentymarkets.com/en/basics/data-exchange/export-import/exporting-data#30) page of the manual for further details about the individual format settings.

Creating a new export format:

1. Go to **Data » Elastic export**.
2. Click on **New export**.
3. Carry out the settings as desired. Pay attention to the information given in table 1.
4. **Save** the settings.
→ The export format will be given an ID and it will appear in the overview within the **Exports** tab.

The following table lists details for settings, format settings and recommended item filters for the format **ShopzillaDE-Plugin**.

| **Setting**                                           | **Explanation** | 
| :---                                                  | :--- |
| **Settings**                                          | |
| **Name**                                              | Enter a name. The export format will be listed under this name in the overview within the **Exports** tab. |
| **Type**                                              | Select the type **Item** from the drop-down list. |
| **Format**                                            | Select **ShopzillaDE-Plugin**. |
| **Limit**                                             | Enter a number. If you want to transfer more than 9,999 data records to the price search engine, then the output file will not be generated again for another 24 hours. This is to save resources. If more than 9,999 data records are necessary, the setting **Generate cache file** has to be active. |
| **Generate cache file**                               | Place a check mark if you want to transfer more than 9,999 data records to the price search engine. The output file will not be generated again for another 24 hours. We recommend not to activate this setting for more than 20 export formats. This is to save resources. |
| **Provisioning**                                      | Select **URL**. This option generates a token for authentication in order to allow external access. |
| **Token, URL**                                        | If you have selected the option **URL** under **Provisioning**, then click on **Generate token**. The token will be entered automatically. When the token is generated under **Token**, the URL is entered automatically. |
| **File name**                                         | The file name must have the ending **.csv** or **.txt** for Shopzilla.de to be able to import the file successfully. |
| **Item filters**                                      | |
| **Add item filter**                                   | Select an item filter from the drop-down list and click on **Add**. There are no filters set in default. It is possible to add multiple item filters from the drop-down list one after the other.<br/> **Variations** = Select **Transfer all** or **Only transfer main variations**.<br/> **Markets** = Select one market, several or **ALL**.<br/> The availability for all markets selected here has to be saved for the item. Otherwise, the export will not take place.<br/> **Currency** = Select a currency.<br/> **Category** = Activate to transfer the item with its category link. Only items belonging to this category will be exported.<br/> **Image** = Activate to transfer the item with its image. Only items with images will be transferred.<br/> **Client** = Select client.<br/> **Stock** = Select which stocks you want to export.<br/> **Flag 1 - 2** = Select the flag.<br/> **Manufacturer** = Select one, several or **ALL** manufacturers.<br/> **Active** = Only active variations will be exported. |
| **Format settings**                                   | |
| **Product URL**                                       | Choose wich URL should be transferred to the price comparison portal, the item’s URL or the variation’s URL. Variation SKUs can only be transferred in combination with the Ceres store. |
| **Client**                                            | Select a client. This setting is used for the URL structure. |
| **URL parameter**                                     | Enter a suffix for the product URL if this is required for the export. If you have activated the transfer option for the product URL further up, then this character string will be added to the product URL. |
| **Order referrer**                                    | Choose the order referrer that should be assigned during the order import from the drop-down list. |
| **Marketplace account**                               | Select the marketplace account from the drop-down list. The selected referrer is added to the product URL so that sales can be analysed later. |
| **Language**                                          | Select the language from the drop-down list. |
| **Item name**                                         | Select **Name 1**, **Name 2** or **Name 3**. These names are saved in the **Texts** tab of the item. Enter a number into the **Maximum number of characters (def. Text)** field if desired. This specifies how many characters should be exported for the item name. |
| **Preview text**                                      | This option does not affect this format. |
| **Description**                                       | Select the text that you want to transfer as description.<br/> Enter a number into the **Maximum number of characters (def. text)** field if desired. This specifies how many characters should be exported for the description.<br/> Activate the option **Remove HTML tags** if you want HTML tags to be removed during the export. If you only want to allow specific HTML tags to be exported, then enter these tags into the field **Permitted HTML tags, separated by comma (def. Text)**. Use commas to separate multiple tags. |
| **Target country**                                    | Select the target country from the drop-down list. |
| **Barcode**                                           | Select the ASIN, ISBN or an EAN from the drop-down list. The barcode has to be linked to the order referrer selected above. If the barcode is not linked to the order referrer it will not be exported. |
| **Image**                                             | Select **First image**. |
| **Image position of the energy efficiency label**     | Enter the position. Every image that should be transferred as an energy efficiency label must have this position. |
| **Stockbuffer**                                       | This option does not affect this format. |
| **Stock for varations without stock limitation**      | This option does not affect this format. |
| **Stock for variations with no stock administration** | This option does not affect this format. |
| **Live currency conversion**                          | Activate this option to convert the price into the currency of the selected country of delivery. The price has to be released for the corresponding currency. |
| **Retail price**                                      | Select gross price or net price from the drop-down list. |
| **Offer price**                                       | This option does not affect this format. |
| **RRP**                                               | Activate to transfer the RRP. |
| **Shipping costs**                                    | Activate this option if you want to use the shipping costs that are saved in a configuration. If this option is activated, then you will be able to select the configuration and the payment method from the drop-down lists.<br/> Activate the option **Transfer flat rate shipping charge** if you want to use a fixed shipping charge. If this option is activated, a value has to be entered in the line underneath. |
| **VAT note**                                          | This option does not affect this format. |
| **Overwrite item availability**                       | This setting has to be activated because Shopzilla.de only allows specific values which have to be filled in. You can find further information in the chapter **Available columns of the export file**. |

## 3 Available columns of the export file

| **Column description**   | **Explanation** |
| :---                     | :--- |
| **ID**                   | **Required**<br/> The **SKU** of the variation based on the chosen order referrer in the format settings. |
| **Titel**                | **Required**<br/> **Limitation**: No HTML code allowed<br/> According to the format setting **item name**. |
| **Beschreibung**         | **Required**<br/> **Limitation**: No HTML code allowed<br/> According to the format setting **Description**. |
| **Kategorie**            | **Required**<br/> The **category path of the default cateogory** for the defined client in the format settings. |
| **Artikel-URL**          | **Required**<br/> The **URL path** of the item depending on the chosen **client** in the format settings. |
| **Bild-URL**             | **Required**<br/> **Limitation**: **Minimum size**: 450 x 450 pixel. **Maximum size**: 1000 x 1000 pixel.<br/> **Allowed file types**: jpg, gif, bmp, png<br/> URL of the image according to the format setting **Image**. Variation images are prioritizied over item images. |
| **Zusätzliche Bild-URL** | **Limitation**: **Minimum size**: 450 x 450 pixel. **Maximum size**: 1000 x 1000 pixel<br/> **Allowed file types**: jpg, gif, bmp, png<br/> A list of up to 10 URLs of the image according to the format setting **Image** separated by comma. Variation images are prioritised over item images. | 
| **Zustand**              | **Required**<br/> The value from **Condition for API** of the variation will be translated. **[0]New** will be exported as **Neu**. Every other option will be exported as **Gebraucht**. |
| **Bestand**              | **Required**<br/> **Allowed values**: Auf Lager, Nicht vorrätig, Verfügbar, Auf Vorbestellung<br/> Translation according to the format setting **Overwrite item availabilty**. |
| **Marke**                | The **name of the manufacturer** of the item. The **external name** within **System » Items » Manufacturer** will be preferred if existing. |
| **EAN**                  | According to the format setting **Barcode**. |
| **Artikelnummer**        | The **model** within **Items » Edit item » Open item » Open variation » Settings » Basic settings**. |
| **Versandkosten**        | **Required**<br/> According to the format setting **Shipping costs**. |
| **Geschlecht**           | **Allowed values**: männlich, weiblich, nicht geschlechtspezifisch<br/> The value of a property of the type **Text** or **Selection** that is linked to **Shopzilla.de » Gender**. |
| **Altersgruppe**         | **Allowed values**: Erwachsene, Kinder<br/> The value of a property of the type **Text** or **Selection** that is linked to **Shopzilla.de » Age group**. |
| **Größe**                | The value of an attribute with an attribute link for **Google Shopping** to **Size**. As an alternative the value of a property of the type **Text** or **Selection** that is linked to **Shopzilla.de » Size** can also be used. |
| **Farbe**                | The value of an attribute with an attribute link for **Google Shopping** to **Colour**. As an alternative the value of a property of the type **Text** or **Selection** that is linked to **Shopzilla.de » Colour** can also be used. |
| **Material**             | The value of an attribute with an attribute link for **Google Shopping** to **Material**. As an alternative the value of a property of the type **Text** or **Selection** that is linked to **Shopzilla.de » Material** can also be used. |
| **Muster**               | The value of an attribute with an attribute link for **Google Shopping** to **Pattern**. As an alternative the value of a property of the type **Text** or **Selection** that is linked to **Shopzilla.de » Pattern** can also be used. |
| **Produktgruppe**        | **Required for items with variations**<br/> The item ID. |
| **Grundpreis**           | The **base price information** in the format "price / unit". (Example: 10,00 EUR / kilogram) |
| **Empfohlener Preis**    | The **sales price** of the price type **RRP** of the variation. |
| **Preis**                | **Required**<br/> The **sales price** of the variation. |

## 4 License

This project is licensed under the GNU AFFERO GENERAL PUBLIC LICENSE.- find further information in the [LICENSE.md](https://github.com/plentymarkets/plugin-elastic-export-shopzilla-de/blob/master/LICENSE.md).
