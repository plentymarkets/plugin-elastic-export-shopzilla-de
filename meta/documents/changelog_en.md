# Release Notes for Elastic Export Shopzilla.de

## v1.1.0 (2017-07-26)

### Fixed
- Shipping costs are now exported correctly.

### Removed
- The column **Versandgewicht** was removed, because it is not contained in official feed specifications.
- The column **Werbetext**, that was only filled with value **2**, was removed.
- The column **Gebot**, that was always exported empty, was removed.

### Changed
- The **delimiter** was changed from space to **tab**.
- The order of columns was changed.
- The column **Zustand** can now be exported as **Gebraucht**.
- The column **Bestand** can now be edited by using the format setting **overwrite item availability**
- The character limite of 256 was removed for the columns **Titel** and **Beschreibung**.
- The column **Hersteller** was renamed to **Marke**.
- The column **Bezeichnung** was renamed to **Titel**.
- The column **SKU** was renamed to **ID**.

### Added
- The column **Zusätzliche Bild-URL** was added.
- The column **ArtikelnummerL** was added.
- The column **Geschlecht** was added.
- The column **Altersgruppe** was added.
- The column **Größe** was added.
- The column **Farbe** was added.
- The column **Material** was added.
- The column **Muster** was added.
- The column **Produktgruppe** was added.
- The column **Empfohlener Preis** was added.
- **Property links** for Shopzilla were added.

## v1.0.3 (2017-07-18)

### Changed
- The plugin Elastic Export is now required to use the plugin format ShopzillaDE.

### Fixed
- An issue was fixed which caused elastic search to ignore the set referrers for the barcodes.
- An issue was fixed which caused the stock filter not to be correctly evaluated.
- An issue was fixed which caused the variations not to be exported in the correct order.
- An issue was fixed which caused the export format to export texts in the wrong language.

## v1.0.2 (2017-05-29)

### Changed
- The plugin Elastic Export is now required to use the plugin format ShopzillaDE.

## v1.0.1 (2017-03-22)

### Fixed
- We now use a different value to get the image URLs for plugins working with elastic search.

## v1.0.0 (2017-03-10)
 
### Added
- Added initial plugin files